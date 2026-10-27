<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRegistrationRequest;
use App\Mail\AntrianMail;
use App\Models\NewsItem;
use App\Models\Registration;
use App\Models\Review;
use App\Services\IndonesianPublicHolidayCalendar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class RegistrationController extends Controller
{
    private const SERVICE_PREFIXES = [
        'Konsultasi Statistik' => 'A',
        'Konsultasi DTSEN' => 'B',
        'Permintaan Data' => 'C',
        'Rekomendasi Kegiatan Statistik' => 'D',
        'Pengaduan' => 'E',
        'Lainnya' => 'F',
    ];

    private const LEGACY_DTSEN = 'Konsultasi Data Tunggal Sosial dan Ekonomi Nasional (DTSEN)';

    private function serviceValues(string $jenisLayanan): array
    {
        if ($jenisLayanan === 'Konsultasi DTSEN') {
            return [$jenisLayanan, self::LEGACY_DTSEN];
        }

        if ($jenisLayanan === 'Lainnya') {
            return ['Lainnya'];
        }

        return [$jenisLayanan];
    }

    private function normalizeService(string $jenisLayanan): string
    {
        return array_key_exists($jenisLayanan, self::SERVICE_PREFIXES) || str_starts_with($jenisLayanan, 'Lainnya:')
            ? $jenisLayanan
            : 'Lainnya';
    }

    private function isOtherService(string $jenisLayanan): bool
    {
        return $jenisLayanan === 'Lainnya' || str_starts_with($jenisLayanan, 'Lainnya:');
    }

    private function nextQueueNumber(string $tanggalKunjungan, string $jenisLayanan, ?int $exceptId = null): int
    {
        $query = Registration::where('tanggal_kunjungan', $tanggalKunjungan);

        if ($this->isOtherService($jenisLayanan)) {
            $query->where(function (Builder $query): void {
                $query->where('jenis_layanan', 'Lainnya')
                    ->orWhere('jenis_layanan', 'like', 'Lainnya:%');
            });
        } else {
            $query->whereIn('jenis_layanan', $this->serviceValues($jenisLayanan));
        }

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        $pendaftaranTerakhir = $query
            ->orderByDesc('nomor_urut')
            ->lockForUpdate()
            ->first();

        return $pendaftaranTerakhir ? $pendaftaranTerakhir->nomor_urut + 1 : 1;
    }

    /**
     * Apply the shared service filter used by monitoring and report endpoints.
     */
    private function applyServiceFilter(Builder $query, ?string $jenisLayanan): void
    {
        if (! $jenisLayanan) {
            return;
        }

        $layananStandar = array_keys(self::SERVICE_PREFIXES);

        if ($jenisLayanan === 'Lainnya') {
            $query->where(function (Builder $query) use ($layananStandar): void {
                $query->where('jenis_layanan', 'Lainnya')
                    ->orWhereNotIn('jenis_layanan', $layananStandar);
            });

            return;
        }

        $query->whereIn('jenis_layanan', $this->serviceValues($jenisLayanan));
    }

    public function create(IndonesianPublicHolidayCalendar $holidayCalendar)
    {
        // Memaksa browser untuk selalu memuat ulang halaman (membersihkan cache token lama)
        $calendar = $holidayCalendar->calendar();

        return response()->view('pages.pendaftaran', [
            'berita' => NewsItem::latest('created_at')->latest('id')->get(),
            'publicHolidays' => $calendar['holidays'],
            'holidayCalendarAvailable' => $calendar['available'],
        ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function checkDuplicateEmail(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'tanggal_kunjungan' => ['required', 'date_format:Y-m-d'],
        ]);

        $alreadyRegistered = Registration::where('email', $validated['email'])
            ->where('tanggal_kunjungan', $validated['tanggal_kunjungan'])
            ->exists();

        return response()->json(
            ['already_registered' => $alreadyRegistered],
            200,
            ['Cache-Control' => 'no-store, private'],
        );
    }

    public function store(StoreRegistrationRequest $request)
    {
        $validated = $request->validated();

        // Cegah satu email mendaftar lebih dari sekali pada tanggal kunjungan yang sama.
        $sudahDaftar = Registration::where('email', $validated['email'])
            ->where('tanggal_kunjungan', $validated['tanggal_kunjungan'])
            ->exists();

        if ($sudahDaftar) {
            $pesanError = 'Mohon maaf, email tersebut sudah digunakan untuk mengambil nomor antrian pada tanggal kunjungan yang dipilih.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $pesanError,
                    'errors' => ['email' => [$pesanError]],
                ], 422);
            }

            return redirect()->back()->with('error', $pesanError);
        }

        // Gunakan DB Transaction agar aman, atomik, dan mengembalikan data dengan benar
        try {
            $result = DB::transaction(function () use ($validated) {
                $tanggalHariIni = Carbon::now('Asia/Jakarta')->format('Y-m-d');
                $tanggalKunjungan = $validated['tanggal_kunjungan'];
                $formatTanggal = Carbon::parse($tanggalKunjungan)->format('dmy');

                $jenisLayananPrefix = $validated['jenis_layanan'];
                $jenisLayanan = $validated['jenis_layanan'] === 'Lainnya'
                    ? 'Lainnya: '.trim($validated['keterangan_lainnya'])
                    : $validated['jenis_layanan'];

                $urutan_angka = $this->nextQueueNumber($tanggalKunjungan, $jenisLayanan);
                $prefix = self::SERVICE_PREFIXES[$jenisLayananPrefix];
                $nomor_antrian = $prefix.'-'.$formatTanggal.'-'.str_pad($urutan_angka, 3, '0', STR_PAD_LEFT);

                $pendaftaran = new Registration;
                $pendaftaran->nomor_antrian = $nomor_antrian;
                $pendaftaran->nomor_urut = $urutan_angka;
                $pendaftaran->tanggal = $tanggalHariIni;
                $pendaftaran->nama = $validated['nama'];
                $pendaftaran->email = $validated['email'];
                $pendaftaran->no_wa = $validated['no_wa'];
                $pendaftaran->alamat = $validated['alamat'];
                $pendaftaran->jenis_layanan = $jenisLayanan;
                $pendaftaran->tanggal_kunjungan = $validated['tanggal_kunjungan'];
                $pendaftaran->status = 'Menunggu';
                $pendaftaran->save();

                // Kirim email tiket
                try {
                    Mail::to($validated['email'])->send(new AntrianMail($pendaftaran));
                } catch (\Exception $e) {
                    // Tangani error email jika ada gangguan koneksi mail server
                }

                return [
                    'pendaftaran' => $pendaftaran,
                    'nomor_antrian' => $nomor_antrian,
                ];
            });

            $pesanSukses = 'Berhasil mengambil nomor antrian: '.$result['nomor_antrian'].'. Silakan cek kotak masuk atau folder spam email Anda untuk melihat rincian e-tiket antrian.';

            // Jika permintaan dari AJAX, kirim format JSON lengkap dengan registration_id
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'registration_id' => $result['pendaftaran']->id,
                    'nomor_antrian' => $result['nomor_antrian'],
                    'message' => $pesanSukses,
                ]);
            }

            return redirect()->back()->with('success', $pesanSukses);

        } catch (\Throwable $e) {
            $pesanGagal = 'Terjadi kesalahan pada sistem saat menyimpan data: '.$e->getMessage();
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $pesanGagal], 500);
            }

            return redirect()->back()->with('error', $pesanGagal);
        }
    }

    public function cetak_pdf($id)
    {
        $data = Registration::findOrFail($id);

        return view('pages.cetak', compact('data'));
    }

    public function index(Request $request)
    {
        $request->validate([
            'tanggal' => 'nullable|date',
            'bulan' => 'nullable|date_format:Y-m',
            'jenis_layanan' => 'nullable|string',
            'sort' => 'nullable|in:created_at,nomor_antrian,nama',
        ]);

        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $jenisLayanan = $request->input('jenis_layanan');

        // DEFAULT DIUBAH KE 'nomor_antrian'
        $sort = $request->input('sort', 'nomor_antrian');

        $query = Registration::query();

        // Logika Filter Tanggal atau Bulan
        if ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $query->whereYear('tanggal_kunjungan', $pecahBulan[0])
                    ->whereMonth('tanggal_kunjungan', $pecahBulan[1]);
            }
        } elseif ($tanggal) {
            $query->where('tanggal_kunjungan', $tanggal);
        } else {
            $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $query->where('tanggal_kunjungan', $tanggal);
        }

        // Logika Filter Berdasarkan Jenis Layanan
        $this->applyServiceFilter($query, $jenisLayanan);

        switch ($sort) {
            case 'nomor_antrian':
                $query->orderByRaw("CASE WHEN status IN ('Selesai', 'Tidak Hadir') THEN 1 ELSE 0 END ASC")
                    ->orderByRaw('SUBSTR(nomor_antrian, 1, 1) ASC')
                    ->orderBy('created_at', 'asc')
                    ->orderBy('id', 'asc');
                break;

            case 'nama':
                $query->orderByRaw("CASE WHEN status IN ('Selesai', 'Tidak Hadir') THEN 1 ELSE 0 END ASC")
                    ->orderBy('nama', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');
                break;

            case 'created_at':
            default:
                $query->orderByRaw("CASE WHEN status IN ('Selesai', 'Tidak Hadir') THEN 1 ELSE 0 END ASC")
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');
                break;
        }

        $pendaftar = $query->get();

        return response()->view('pages.dashboard', [
            'pendaftar' => $pendaftar,
            'tanggal' => $tanggal,
            'bulan' => $bulan,
            'jenisLayanan' => $jenisLayanan,
            'sort' => $sort,
            'berita' => NewsItem::orderBy('position')->get(),
        ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function updateStatus($id, $status)
    {
        $pendaftaran = Registration::findOrFail($id);

        if ($pendaftaran->status === 'Selesai') {
            return redirect()->back()->with('error', 'Antrian yang sudah selesai tidak dapat diubah statusnya.');
        }

        $pendaftaran->status = $status;
        $pendaftaran->save();

        return redirect()->back()->with('success', 'Status antrian berhasil diubah menjadi: '.$status);
    }

    public function update(Request $request, int $id)
    {
        abort_unless(in_array(strtolower((string) Auth::user()->role), ['admin', 'cs'], true), 403);

        $data = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'no_wa' => ['required', 'string', 'regex:/^(08|628)[0-9]{7,13}$/'],
            'alamat' => 'required|string',
            'jenis_layanan' => ['required', 'string', Rule::in(array_keys(self::SERVICE_PREFIXES))],
            'tanggal_kunjungan' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (Carbon::createFromFormat('Y-m-d', $value)->isWeekend()) {
                        $fail('Tanggal kunjungan hanya tersedia pada hari Senin sampai Jumat.');
                    }
                },
            ],
        ]);

        $pendaftaran = Registration::findOrFail($id);
        $data['jenis_layanan'] = $this->normalizeService($data['jenis_layanan']);
        $duplikat = Registration::where('email', $data['email'])
            ->where('tanggal_kunjungan', $data['tanggal_kunjungan'])
            ->where('id', '!=', $pendaftaran->id)
            ->exists();

        if ($duplikat) {
            return response()->json([
                'success' => false,
                'message' => 'Email tersebut sudah digunakan pada tanggal kunjungan yang dipilih.',
            ], 422);
        }

        DB::transaction(function () use ($data, $pendaftaran): void {
            if (
                $pendaftaran->tanggal_kunjungan !== $data['tanggal_kunjungan']
                || $pendaftaran->jenis_layanan !== $data['jenis_layanan']
            ) {
                $nomorUrut = $this->nextQueueNumber(
                    $data['tanggal_kunjungan'],
                    $data['jenis_layanan'],
                    $pendaftaran->id,
                );
                $prefix = self::SERVICE_PREFIXES[$data['jenis_layanan']];
                $data['nomor_urut'] = $nomorUrut;
                $data['nomor_antrian'] = $prefix.'-'.Carbon::parse($data['tanggal_kunjungan'])->format('dmy').'-'.str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
            }

            $pendaftaran->update($data);
        });

        try {
            Mail::to($pendaftaran->email)->send(new AntrianMail($pendaftaran->fresh(), true));
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'message' => 'Data tiket berhasil diperbarui, tetapi email perubahan gagal dikirim. Silakan coba lagi.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data tiket berhasil diperbarui dan email perubahan telah dikirim ke pemohon.',
        ]);
    }

    // -----------------------------------------------------------------
    // FITUR RUANG TUNGGU & PANGGIL OTOMATIS
    // -----------------------------------------------------------------

    public function ruangTunggu()
    {
        return view('pages.ruang_tunggu');
    }

    public function apiRuangTunggu()
    {
        $today = Carbon::now('Asia/Jakarta')->format('Y-m-d');

        $sedangDipanggil = Registration::where('tanggal_kunjungan', $today)
            ->where('status', 'Dipanggil')
            ->latest('updated_at')
            ->first();

        $antrianMenunggu = Registration::where('tanggal_kunjungan', $today)
            ->where('status', 'Menunggu')
            ->orderBy('nomor_urut', 'asc')
            ->take(5)
            ->get();

        $selesai = collect(self::SERVICE_PREFIXES)->mapWithKeys(function (string $prefix, string $jenisLayanan) use ($today): array {
            $query = Registration::where('tanggal_kunjungan', $today)
                ->where('status', 'Selesai')
                ->when($jenisLayanan === 'Lainnya', function (Builder $query): void {
                    $query->where(function (Builder $query): void {
                        $query->where('jenis_layanan', 'Lainnya')
                            ->orWhereNotIn('jenis_layanan', array_keys(self::SERVICE_PREFIXES));
                    });
                }, function (Builder $query) use ($jenisLayanan): void {
                    $query->whereIn('jenis_layanan', $this->serviceValues($jenisLayanan));
                });

            $terakhir = $query->orderByDesc('nomor_urut')
                ->orderByDesc('updated_at')
                ->first();

            return [$prefix => $terakhir?->nomor_antrian];
        });

        // Antrian dengan status Tidak Hadir tidak ditampilkan dalam monitor tunggu.

        return response()->json([
            'dipanggil' => $sedangDipanggil,
            'menunggu' => $antrianMenunggu,
            'selesai' => $selesai,
        ]);
    }

    public function panggilAntrian($id)
    {
        $pendaftaran = Registration::findOrFail($id);

        if ($pendaftaran->status === 'Selesai') {
            return response()->json([
                'success' => false,
                'message' => 'Antrian yang sudah selesai tidak dapat diubah statusnya.',
            ], 422);
        }

        $pendaftaran->status = 'Dipanggil';
        $pendaftaran->save();

        return response()->json([
            'success' => true,
            'message' => 'Antrian '.$pendaftaran->nomor_antrian.' berhasil dipanggil.',
            'data' => $pendaftaran,
        ]);
    }

    // -----------------------------------------------------------------
    // PROTEKSI HAPUS: HANYA ADMIN YANG BISA HAPUS
    // -----------------------------------------------------------------

    public function destroy($id)
    {
        abort_unless(strtolower((string) Auth::user()->role) === 'admin', 403);

        $pendaftaran = Registration::findOrFail($id);
        $pendaftaran->delete();

        return redirect()->back()->with('success', 'Data antrian berhasil dihapus.');
    }

    // -----------------------------------------------------------------
    // EXPORT LAPORAN (EXCEL, WORD, PDF)
    // -----------------------------------------------------------------

    public function export(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $jenisLayanan = $request->input('jenis_layanan');

        $query = Registration::query();
        $labelPeriode = '';

        if ($tanggal) {
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = 'Tanggal: '.Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $query->whereYear('tanggal_kunjungan', $pecahBulan[0])->whereMonth('tanggal_kunjungan', $pecahBulan[1]);
                $labelPeriode = 'Bulan: '.Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
            }
        } else {
            $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = 'Tanggal: '.Carbon::parse($tanggal)->translatedFormat('d F Y');
        }

        if ($jenisLayanan) {
            $this->applyServiceFilter($query, $jenisLayanan);
            $labelPeriode .= ' | Layanan: '.$jenisLayanan;
        } else {
            $labelPeriode .= ' | Semua Layanan';
        }

        $pendaftar = $query->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('nomor_urut', 'asc')
            ->get();
        $filename = 'Laporan_Rekap_BPS_'.($bulan ?? $tanggal).'.xlsx';

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Rekap');

        // Lebar kolom A-H
        $sheet->getColumnDimension('A')->setWidth(5);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(16);
        $sheet->getColumnDimension('D')->setWidth(20);
        $sheet->getColumnDimension('E')->setWidth(24);
        $sheet->getColumnDimension('F')->setWidth(24);
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(30);

        // Baris 1-4: ruang kop surat, tinggi disatukan agar logo tak terpotong
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getRowDimension(2)->setRowHeight(20);
        $sheet->getRowDimension(3)->setRowHeight(18);
        $sheet->getRowDimension(4)->setRowHeight(16);

        // ===== KOP SURAT =====
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'SANTIKA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'BADAN PUSAT STATISTIK KABUPATEN MAGELANG');
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:H3');
        $sheet->setCellValue('A3', 'Jl. Soekarno-Hatta No. 4 Kota Mungkid, Kabupaten Magelang');
        $sheet->getStyle('A3')->getFont()->setSize(8.5);
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Garis ganda pemisah kop
        $sheet->mergeCells('A4:H4');
        $sheet->getStyle('A4')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        // ===== LOGO KIRI (BPS) =====
        $logoBpsPath = public_path('images/logo-bps.png');
        if (file_exists($logoBpsPath)) {
            $drawingBps = new Drawing;
            $drawingBps->setName('Logo BPS');
            $drawingBps->setPath($logoBpsPath);
            $drawingBps->setHeight(60);
            $drawingBps->setCoordinates('A1');
            $drawingBps->setOffsetX(8);
            $drawingBps->setOffsetY(2);
            $drawingBps->setWorksheet($sheet);
        }

        // ===== LOGO KANAN (SANTIKA) =====
        $logoSantikaPath = public_path('images/LOGO SANTIKA HITAM.png');
        if (file_exists($logoSantikaPath)) {
            $drawingSantika = new Drawing;
            $drawingSantika->setName('Logo SANTIKA');
            $drawingSantika->setPath($logoSantikaPath);
            $drawingSantika->setHeight(60);
            $drawingSantika->setCoordinates('H1');
            $drawingSantika->setOffsetX(140);
            $drawingSantika->setOffsetY(2);
            $drawingSantika->setWorksheet($sheet);
        }

        // ===== JUDUL LAPORAN =====
        $sheet->mergeCells('A5:H5');
        $sheet->setCellValue('A5', 'LAPORAN REKAPITULASI ANTRIAN LAYANAN');
        $sheet->getStyle('A5')->getFont()->setBold(true)->setSize(12)->setUnderline(true);
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension(5)->setRowHeight(22);

        $sheet->mergeCells('A6:H6');
        $sheet->setCellValue('A6', 'Periode: '.$labelPeriode);
        $sheet->getStyle('A6')->getFont()->setSize(11);
        $sheet->getRowDimension(6)->setRowHeight(20);

        // ===== HEADER TABEL =====
        $headerRow = 7;
        $headers = ['No', 'Tanggal Kunjungan', 'No. Antrian', 'Nama Pemohon', 'Jenis Layanan', 'Email', 'No. WhatsApp', 'Alamat'];
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.$headerRow, $header);
            $col++;
        }
        $sheet->getStyle('A'.$headerRow.':H'.$headerRow)->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A'.$headerRow.':H'.$headerRow)->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('002060');
        $sheet->getStyle('A'.$headerRow.':H'.$headerRow)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        // ===== ISI DATA =====
        $row = $headerRow + 1;
        $no = 1;
        foreach ($pendaftar as $data) {
            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValue('B'.$row, Carbon::parse($data->tanggal_kunjungan)->format('d-m-Y'));
            $sheet->setCellValue('C'.$row, $data->nomor_antrian);
            $sheet->setCellValue('D'.$row, $data->nama);
            $sheet->setCellValue('E'.$row, $data->jenis_layanan);
            $sheet->setCellValue('F'.$row, $data->email);
            $sheet->setCellValueExplicit('G'.$row, $data->no_wa, DataType::TYPE_STRING);
            $sheet->setCellValue('H'.$row, $data->alamat);

            $sheet->getStyle('A'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$row)->getFont()->setBold(true);
            $sheet->getStyle('C'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G'.$row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A'.$row.':H'.$row)->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

            $row++;
        }
        $lastDataRow = $row - 1;

        // Border tabel
        $sheet->getStyle('A'.$headerRow.':H'.$lastDataRow)->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // ===== TANDA TANGAN =====
        $ttdRow = $lastDataRow + 3;
        $sheet->mergeCells('G'.$ttdRow.':H'.$ttdRow);
        $sheet->setCellValue('G'.$ttdRow, 'Magelang, '.Carbon::now()->translatedFormat('d F Y'));
        $sheet->getStyle('G'.$ttdRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('G'.($ttdRow + 1).':H'.($ttdRow + 1));
        $sheet->setCellValue('G'.($ttdRow + 1), 'Mengetahui,');
        $sheet->getStyle('G'.($ttdRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('G'.($ttdRow + 2).':H'.($ttdRow + 2));
        $sheet->setCellValue('G'.($ttdRow + 2), 'Petugas Pelayanan SANTIKA');
        $sheet->getStyle('G'.($ttdRow + 2))->getFont()->setBold(true);
        $sheet->getStyle('G'.($ttdRow + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('G'.($ttdRow + 6).':H'.($ttdRow + 6));
        $sheet->setCellValue('G'.($ttdRow + 6), '(....................................)');
        $sheet->getStyle('G'.($ttdRow + 6))->getFont()->setBold(true)->setUnderline(true);
        $sheet->getStyle('G'.($ttdRow + 6))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ===== OUTPUT FILE =====
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    public function exportWord(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $jenisLayanan = $request->input('jenis_layanan');

        $query = Registration::query();
        $labelPeriode = '';

        if ($tanggal) {
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = 'Tanggal: '.Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $query->whereYear('tanggal_kunjungan', $pecahBulan[0])->whereMonth('tanggal_kunjungan', $pecahBulan[1]);
                $labelPeriode = 'Bulan: '.Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
            }
        } else {
            $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = 'Tanggal: '.Carbon::parse($tanggal)->translatedFormat('d F Y');
        }

        if ($jenisLayanan) {
            $this->applyServiceFilter($query, $jenisLayanan);
            $labelPeriode .= ' | Layanan: '.$jenisLayanan;
        }

        $pendaftar = $query->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('nomor_urut', 'asc')
            ->get();
        $filename = 'Laporan_Rekap_BPS_'.($bulan ?? $tanggal).'.doc';
        $logoBpsUrl = 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png';
        $logoSantikaUrl = route('assets.santika-logo-hitam');

        header('Content-type: application/vnd.ms-word');
        header("Content-Disposition: attachment;Filename=\"$filename\"");

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word" xmlns="http://www.w3.org/TR/REC-html40">
        <head>
            <meta charset="utf-8">
            <style>
                body { font-family: "Times New Roman", Times, serif; font-size: 11pt; }
                .kop-surat { margin: 0 auto 5px; border-collapse: collapse; }
                .kop-surat td { vertical-align: middle; }
                .garis-ganda { border-top: 3px solid black; border-bottom: 1px solid black; height: 2px; margin-bottom: 15px; margin-top: 5px; }
                .tabel-data { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 10pt; }
                .tabel-data th, .tabel-data td { border: 1px solid black; padding: 5px; vertical-align: top; }
                .tabel-data th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
                .text-center { text-align: center; }
                .ttd-box { width: 100%; margin-top: 20px; }
            </style>
        </head>
        <body>
            <table class="kop-surat" style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                <tr>
                    <td style="width: 15%; text-align: right; vertical-align: middle; padding-right: 22px;">
                        <img src="'.$logoBpsUrl.'" width="42" height="42" style="width: 42px; height: 42px;" alt="Logo BPS Kiri">
                    </td>
                    <td style="width: 70%; text-align: center; vertical-align: middle;">
                        <div style="font-size: 18pt; font-weight: bold; line-height: 1.2;">SANTIKA</div>
                        <div style="font-size: 11pt; font-weight: bold; line-height: 1.2;">BADAN PUSAT STATISTIK KABUPATEN MAGELANG</div>
                        <div style="font-size: 8.5pt; font-weight: normal; line-height: 1.2;">Jl. Soekarno-Hatta No. 4 Kota Mungkid, Kabupaten Magelang</div>
                    </td>
                    <td style="width: 15%; text-align: left; vertical-align: middle; padding-left: 15px;">
                        <img src="'.$logoSantikaUrl.'" width="42" height="42" style="width: 42px; height: 42px;" alt="Logo SANTIKA Kanan">
                    </td>
                </tr>
            </table>
            <div class="garis-ganda"></div>
            <div style="text-align: center; font-weight: bold; margin-bottom: 10px; text-decoration: underline;">LAPORAN REKAPITULASI ANTRIAN LAYANAN</div>
            <p style="margin-bottom: 10px;">Periode: '.$labelPeriode.'</p>

            <table class="tabel-data">
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 12%;">Tanggal Kunjungan</th>
                        <th style="width: 10%;">Antrian</th>
                        <th style="width: 15%;">Nama</th>
                        <th style="width: 22%;">Jenis Layanan</th>
                        <th style="width: 15%;">Email</th>
                        <th style="width: 11%;">No. WA</th>
                        <th style="width: 15%;">Alamat</th>
                    </tr>
                </thead>
                <tbody>';

        $no = 1;
        foreach ($pendaftar as $row) {
            echo '<tr>
                <td class="text-center">'.$no++.'</td>
                <td class="text-center">'.Carbon::parse($row->tanggal_kunjungan)->format('d-m-Y').'</td>
                <td class="text-center"><b>'.$row->nomor_antrian.'</b></td>
                <td>'.$row->nama.'</td>
                <td>'.$row->jenis_layanan.'</td>
                <td>'.$row->email.'</td>
                <td class="text-center">'.$row->no_wa.'</td>
                <td>'.$row->alamat.'</td>
            </tr>';
        }

        echo '</tbody></table>
            <table class="ttd-box">
                <tr>
                    <td width="65%"></td>
                    <td width="35%" class="text-center">
                        <p>Magelang, '.Carbon::now()->translatedFormat('d F Y').'<br>Mengetahui,<br><b>Petugas Pelayanan SANTIKA</b></p>
                        <br><br><br><p><b><u>(....................................)</u></b></p>
                    </td>
                </tr>
            </table>
        </body>
        </html>';
        exit;
    }

    public function exportPdf(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $jenisLayanan = $request->input('jenis_layanan');

        $query = Registration::query();
        $labelPeriode = '';

        if ($tanggal) {
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = 'Tanggal: '.Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $query->whereYear('tanggal_kunjungan', $pecahBulan[0])->whereMonth('tanggal_kunjungan', $pecahBulan[1]);
                $labelPeriode = 'Bulan: '.Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
            }
        } else {
            $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = 'Tanggal: '.Carbon::parse($tanggal)->translatedFormat('d F Y');
        }

        if ($jenisLayanan) {
            $this->applyServiceFilter($query, $jenisLayanan);
            $labelPeriode .= ' | Layanan: '.$jenisLayanan;
        }

        $pendaftar = $query->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('nomor_urut', 'asc')
            ->get();
        $tanggal_format = $labelPeriode;

        return view('pages.laporan_pdf', compact('pendaftar', 'tanggal_format'));
    }

    public function storeReview(Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'kritik_saran' => 'nullable|string|max:500',
            'registration_id' => 'nullable|exists:registrations,id',
        ]);

        Review::create([
            'registration_id' => $request->registration_id,
            'rating' => $request->rating,
            'kritik_saran' => $request->kritik_saran,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas ulasan, kritik, dan saran Anda!',
        ]);
    }

    public function listReviews(Request $request)
    {
        abort_unless(in_array(strtolower((string) Auth::user()->role), ['admin', 'cs'], true), 403);

        $request->validate([
            'bulan' => 'nullable|date_format:Y-m',
        ]);

        $bulan = $request->input('bulan');

        $query = Review::with('registration')->latest();

        if ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $query->whereYear('created_at', $pecahBulan[0])
                    ->whereMonth('created_at', $pecahBulan[1]);
            }
        }

        $reviews = $query->paginate(15)->withQueryString();

        $ratingQuery = Review::query();
        if ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $ratingQuery->whereYear('created_at', $pecahBulan[0])
                    ->whereMonth('created_at', $pecahBulan[1]);
            }
        }

        $averageRating = (clone $ratingQuery)->avg('rating');
        $totalReview = (clone $ratingQuery)->count();

        return response()->view('pages.admin_reviews', compact('reviews', 'averageRating', 'totalReview', 'bulan'))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function exportReviewsPdf(Request $request)
    {
        abort_unless(in_array(strtolower((string) Auth::user()->role), ['admin', 'cs'], true), 403);

        $request->validate([
            'bulan' => 'nullable|date_format:Y-m',
        ]);

        $bulan = $request->input('bulan');
        $labelPeriode = 'Semua Periode';

        $query = Review::with('registration')->latest();

        if ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $query->whereYear('created_at', $pecahBulan[0])
                    ->whereMonth('created_at', $pecahBulan[1]);
                $labelPeriode = 'Bulan: '.Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
            }
        }

        $reviews = $query->get();

        $ratingQuery = Review::query();
        if ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $ratingQuery->whereYear('created_at', $pecahBulan[0])
                    ->whereMonth('created_at', $pecahBulan[1]);
            }
        }

        $averageRating = (clone $ratingQuery)->avg('rating');
        $totalReview = (clone $ratingQuery)->count();

        return view('pages.laporan_ulasan_pdf', compact('reviews', 'averageRating', 'totalReview', 'labelPeriode'));
    }
}
