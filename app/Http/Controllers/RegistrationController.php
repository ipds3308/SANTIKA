<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\AntrianMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use App\Models\NewsItem;

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
        if (!$jenisLayanan) {
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

    public function create()
    {
        // Memaksa browser untuk selalu memuat ulang halaman (membersihkan cache token lama)
        return response()->view('pages.pendaftaran', [
            'berita' => NewsItem::latest('created_at')->latest('id')->get(),
        ])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function store(Request $request)
    {
        // Validasi input form (mendukung no. WA awalan 08 atau 628)
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email',
            'no_wa' => ['required', 'string', 'regex:/^(08|628)[0-9]{7,13}$/'],
            'alamat' => 'required|string',
            'jenis_layanan' => ['required', 'string', Rule::in(array_keys(self::SERVICE_PREFIXES))],
            'keterangan_lainnya' => ['nullable', 'string', 'max:50', 'required_if:jenis_layanan,Lainnya'],
            'tanggal_kunjungan' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    try {
                        $tanggalKunjungan = Carbon::createFromFormat('Y-m-d', $value);
                    } catch (\Throwable) {
                        return;
                    }

                    if ($tanggalKunjungan->isWeekend()) {
                        $fail('Tanggal kunjungan hanya tersedia pada hari Senin sampai Jumat.');
                    }
                },
            ],
        ], [
            'no_wa.regex' => 'Nomor WhatsApp harus diawali dengan 08 atau 628, dan panjang 10 s.d. 15 digit.',
            'jenis_layanan.required' => 'Silakan pilih jenis layanan atau keperluan terlebih dahulu.',
            'keterangan_lainnya.required_if' => 'Silakan isi keterangan untuk layanan Lainnya.',
            'tanggal_kunjungan.after_or_equal' => 'Tanggal kunjungan tidak boleh kurang dari hari ini.',
        ]);

        // Cegah satu email mendaftar lebih dari sekali pada tanggal kunjungan yang sama.
        $sudahDaftar = Registration::where('email', $request->email)
            ->where('tanggal_kunjungan', $request->tanggal_kunjungan)
            ->exists();

        if ($sudahDaftar) {
            $pesanError = 'Mohon maaf, email tersebut sudah digunakan untuk mengambil nomor antrian pada tanggal kunjungan yang dipilih.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $pesanError], 422);
            }
            return redirect()->back()->with('error', $pesanError);
        }

        // Gunakan DB Transaction agar aman dan atomik
        return DB::transaction(function () use ($request) {
            $tanggalHariIni = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $tanggalKunjungan = $request->tanggal_kunjungan;
            $formatTanggal = Carbon::parse($tanggalKunjungan)->format('dmy');

            $jenisLayananPrefix = $request->jenis_layanan;
            $jenisLayanan = $request->jenis_layanan === 'Lainnya'
                ? 'Lainnya: ' . trim($request->keterangan_lainnya)
                : $request->jenis_layanan;
            $urutan_angka = $this->nextQueueNumber($tanggalKunjungan, $jenisLayanan);
            $prefix = self::SERVICE_PREFIXES[$jenisLayananPrefix];
            $nomor_antrian = $prefix . '-' . $formatTanggal . '-' . str_pad($urutan_angka, 3, '0', STR_PAD_LEFT);

            $pendaftaran = new Registration();
            $pendaftaran->nomor_antrian = $nomor_antrian;
            $pendaftaran->nomor_urut = $urutan_angka;
            $pendaftaran->tanggal = $tanggalHariIni;
            $pendaftaran->nama = $request->nama;
            $pendaftaran->email = $request->email;
            $pendaftaran->no_wa = $request->no_wa;
            $pendaftaran->alamat = $request->alamat;
            $pendaftaran->jenis_layanan = $jenisLayanan;
            $pendaftaran->tanggal_kunjungan = $request->tanggal_kunjungan;
            $pendaftaran->status = 'Menunggu';
            $pendaftaran->save();

            // Kirim email tiket
            try {
                Mail::to($request->email)->send(new AntrianMail($pendaftaran));
            } catch (\Exception $e) {
                // Tangani error email jika ada gangguan koneksi mail server
            }

            $pesanSukses = 'Berhasil mengambil nomor antrian: ' . $nomor_antrian . '. Silakan cek kotak masuk atau folder spam email Anda untuk melihat rincian e-tiket antrian.';

            // Jika permintaan dari AJAX, kirim format JSON
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'nomor_antrian' => $nomor_antrian,
                    'message' => $pesanSukses
                ]);
            }

            return redirect()->back()->with('success', $pesanSukses);
        });
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
        $sort = $request->input('sort', 'created_at');

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
            // Default tampilkan hari ini jika tidak ada filter yang dipilih
            $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $query->where('tanggal_kunjungan', $tanggal);
        }

        // Logika Filter Berdasarkan Jenis Layanan
        $this->applyServiceFilter($query, $jenisLayanan);

        switch ($sort) {
            case 'nomor_antrian':
                $query->orderByRaw('SUBSTR(nomor_antrian, 1, 1) ASC')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');
                break;

            case 'nama':
                $query->orderBy('nama', 'asc')
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');
                break;

            case 'created_at':
            default:
                $query->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc');
                break;
        }

        $pendaftar = $query->get();

        // Menggunakan response view dengan header anti-cache agar aman setelah logout
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
        return redirect()->back()->with('success', 'Status antrian berhasil diubah menjadi: ' . $status);
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
                $data['nomor_antrian'] = $prefix . '-' . Carbon::parse($data['tanggal_kunjungan'])->format('dmy') . '-' . str_pad($nomorUrut, 3, '0', STR_PAD_LEFT);
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
            'message' => 'Antrian ' . $pendaftaran->nomor_antrian . ' berhasil dipanggil.',
            'data' => $pendaftaran
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
        $labelPeriode = "";

        if ($tanggal) {
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = "Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $query->whereYear('tanggal_kunjungan', $pecahBulan[0])->whereMonth('tanggal_kunjungan', $pecahBulan[1]);
                $labelPeriode = "Bulan: " . Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
            }
        } else {
            $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = "Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y');
        }

        if ($jenisLayanan) {
            $this->applyServiceFilter($query, $jenisLayanan);
            $labelPeriode .= " | Layanan: " . $jenisLayanan;
        } else {
            $labelPeriode .= " | Semua Layanan";
        }

        $pendaftar = $query->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('nomor_urut', 'asc')
            ->get();
        $filename = "Laporan_Rekap_BPS_" . ($bulan ?? $tanggal) . ".xls";
        $nama_petugas = Auth::user()->name;

        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="utf-8"></head>';
        echo '<body style="font-family: Arial, sans-serif;">';
        echo '<table border="0" style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
                <tr>
                    <td style="width: 15%; text-align: right; vertical-align: middle; padding-right: 15px;"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png" style="height: 50px; width: auto;" alt="Logo BPS Kiri"></td>
                    <td style="width: 70%; text-align: center; vertical-align: middle;">
                        <div style="font-size: 18pt; font-weight: bold; line-height: 1.2;">SANTIKA</div>
                        <div style="font-size: 11pt; font-weight: bold; line-height: 1.2;">BADAN PUSAT STATISTIK KABUPATEN MAGELANG</div>
                        <div style="font-size: 8.5pt; font-weight: normal; line-height: 1.2;">Jl. Soekarno-Hatta No. 4 Kota Mungkid, Kabupaten Magelang</div>
                    </td>
                    <td style="width: 15%; text-align: left; vertical-align: middle; padding-left: 15px;"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan Pusat Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png" style="height: 50px; width: auto;" alt="Logo BPS Kanan"></td>
                </tr>
                <tr><td colspan="3" style="text-align: center; font-size: 11pt;">' . $labelPeriode . ' | Diekspor Oleh: ' . $nama_petugas . '</td></tr>
                <tr><td colspan="8"></td></tr>
              </table>';

        echo '<table border="1" style="width: 100%; border-collapse: collapse;">
                <tr style="background-color: #002060; color: #ffffff;">
                    <th style="padding: 8px;">No</th>
                    <th style="padding: 8px;">Tanggal Kunjungan</th>
                    <th style="padding: 8px;">No. Antrian</th>
                    <th style="padding: 8px;">Nama Pemohon</th>
                    <th style="padding: 8px;">Jenis Layanan</th>
                    <th style="padding: 8px;">Email</th>
                    <th style="padding: 8px;">No. WhatsApp</th>
                    <th style="padding: 8px;">Alamat</th>
                </tr>';

        $no = 1;
        foreach ($pendaftar as $row) {
            echo '<tr>
                <td style="text-align: center; padding: 5px;">' . $no++ . '</td>
                <td style="text-align: center; padding: 5px;">' . Carbon::parse($row->tanggal_kunjungan)->format('d-m-Y') . '</td>
                <td style="text-align: center; font-weight: bold; padding: 5px;">' . $row->nomor_antrian . '</td>
                <td style="padding: 5px;">' . $row->nama . '</td>
                <td style="padding: 5px;">' . $row->jenis_layanan . '</td>
                <td style="padding: 5px;">' . $row->email . '</td>
                <td style="text-align: center; padding: 5px; mso-number-format:\'\@\';">' . $row->no_wa . '</td>
                <td style="padding: 5px;">' . $row->alamat . '</td>
            </tr>';
        }
        echo '</table>
            <table style="width: 100%; margin-top: 30px; border-collapse: collapse;">
                <tr>
                    <td colspan="6"></td>
                    <td colspan="2" style="text-align: center;">
                        Magelang, ' . Carbon::now()->translatedFormat('d F Y') . '<br>
                        Mengetahui,<br>
                        <b>Petugas Pelayanan SANTIKA</b><br><br><br>
                        <b><u>( ' . strtoupper($nama_petugas) . ' )</u></b>
                    </td>
                </tr>
            </table>
            </body></html>';
        exit;
    }

    public function exportWord(Request $request)
    {
        $tanggal = $request->input('tanggal');
        $bulan = $request->input('bulan');
        $jenisLayanan = $request->input('jenis_layanan');

        $query = Registration::query();
        $labelPeriode = "";

        if ($tanggal) {
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = "Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $query->whereYear('tanggal_kunjungan', $pecahBulan[0])->whereMonth('tanggal_kunjungan', $pecahBulan[1]);
                $labelPeriode = "Bulan: " . Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
            }
        } else {
            $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = "Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y');
        }

        if ($jenisLayanan) {
            $this->applyServiceFilter($query, $jenisLayanan);
            $labelPeriode .= " | Layanan: " . $jenisLayanan;
        }

        $pendaftar = $query->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('nomor_urut', 'asc')
            ->get();
        $filename = "Laporan_Rekap_BPS_" . ($bulan ?? $tanggal) . ".doc";
        $nama_petugas = Auth::user()->name;

        header("Content-type: application/vnd.ms-word");
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
                    <td style="width: 15%; text-align: right; vertical-align: middle; padding-right: 15px;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png" style="height: 50px; width: auto;" alt="Logo BPS Kiri">
                    </td>
                    <td style="width: 70%; text-align: center; vertical-align: middle;">
                        <div style="font-size: 18pt; font-weight: bold; line-height: 1.2;">SANTIKA</div>
                        <div style="font-size: 11pt; font-weight: bold; line-height: 1.2;">BADAN PUSAT STATISTIK KABUPATEN MAGELANG</div>
                        <div style="font-size: 8.5pt; font-weight: normal; line-height: 1.2;">Jl. Soekarno-Hatta No. 4 Kota Mungkid, Kabupaten Magelang</div>
                    </td>
                    <td style="width: 15%; text-align: left; vertical-align: middle; padding-left: 15px;">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png" style="height: 50px; width: auto;" alt="Logo BPS Kanan">
                    </td>
                </tr>
            </table>
            <div class="garis-ganda"></div>
            <div style="text-align: center; font-weight: bold; margin-bottom: 10px; text-decoration: underline;">LAPORAN REKAPITULASI ANTRIAN LAYANAN</div>
            <p style="margin-bottom: 10px;">Periode: ' . $labelPeriode . '<br>Dicetak Oleh: ' . $nama_petugas . '</p>

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
                <td class="text-center">' . $no++ . '</td>
                <td class="text-center">' . Carbon::parse($row->tanggal_kunjungan)->format('d-m-Y') . '</td>
                <td class="text-center"><b>' . $row->nomor_antrian . '</b></td>
                <td>' . $row->nama . '</td>
                <td>' . $row->jenis_layanan . '</td>
                <td>' . $row->email . '</td>
                <td class="text-center">' . $row->no_wa . '</td>
                <td>' . $row->alamat . '</td>
            </tr>';
        }

        echo '</tbody></table>
            <table class="ttd-box">
                <tr>
                    <td width="65%"></td>
                    <td width="35%" class="text-center">
                        <p>Magelang, ' . Carbon::now()->translatedFormat('d F Y') . '<br>Mengetahui,<br><b>Petugas Pelayanan SANTIKA</b></p>
                        <br><br><br><p><b><u>( ' . strtoupper($nama_petugas) . ' )</u></b></p>
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
        $labelPeriode = "";

        if ($tanggal) {
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = "Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y');
        } elseif ($bulan) {
            $pecahBulan = explode('-', $bulan);
            if (count($pecahBulan) == 2) {
                $query->whereYear('tanggal_kunjungan', $pecahBulan[0])->whereMonth('tanggal_kunjungan', $pecahBulan[1]);
                $labelPeriode = "Bulan: " . Carbon::createFromFormat('Y-m', $bulan)->translatedFormat('F Y');
            }
        } else {
            $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');
            $query->where('tanggal_kunjungan', $tanggal);
            $labelPeriode = "Tanggal: " . Carbon::parse($tanggal)->translatedFormat('d F Y');
        }

        if ($jenisLayanan) {
            $this->applyServiceFilter($query, $jenisLayanan);
            $labelPeriode .= " | Layanan: " . $jenisLayanan;
        }

        $pendaftar = $query->orderBy('tanggal_kunjungan', 'asc')
            ->orderBy('nomor_urut', 'asc')
            ->get();
        $tanggal_format = $labelPeriode;

        return view('pages.laporan_pdf', compact('pendaftar', 'tanggal_format'));
    }
}