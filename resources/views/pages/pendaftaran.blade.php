@extends('layouts.main')

@section('title', 'Form Pengambilan Antrian - SANTIKA BPS')

@section('content')

<!-- CSS Kustom: Ukuran Kompak & Desain Peringatan -->
<style>
    .uiverse-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        max-width: 100%;
        padding: 20px 22px;
        border-radius: 14px;
        position: relative;
        background-color: whitesmoke;
        color: #212121;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        border: 1px solid #cbd5e1;
    }

    .uiverse-title {
        font-size: 21px;
        font-weight: 700;
        letter-spacing: -0.5px;
        position: relative;
        display: flex;
        align-items: center;
        padding-left: 24px;
        color: #0284c7;
    }

    .uiverse-title::before,
    .uiverse-title::after {
        position: absolute;
        content: "";
        height: 12px;
        width: 12px;
        border-radius: 50%;
        left: 0px;
        background-color: #0284c7;
    }

    .uiverse-title::after {
        animation: pulse-bps 1.2s linear infinite;
    }

    .uiverse-label {
        font-size: 0.8rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 3px;
        display: block;
    }

    .uiverse-form .input-custom {
        background-color: #ffffff;
        color: #1e293b;
        width: 100%;
        padding: 9px 12px;
        outline: 0;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .uiverse-form .input-custom:focus {
        border-color: #0284c7;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }

    .uiverse-form textarea.input-custom {
        min-height: 45px;
        resize: none;
        overflow: hidden;
    }

    .uiverse-submit {
        border: none;
        outline: none;
        padding: 10px 16px;
        border-radius: 8px;
        color: #0284c7;
        font-size: 14.5px;
        font-weight: bold;
        transition: all 0.3s ease;
        background-color: whitesmoke;
        border: 2px solid #0284c7;
        cursor: pointer;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        margin-top: 4px;
        width: 100%;
    }

    .uiverse-submit:hover:not(:disabled) {
        background-color: #0284c7;
        color: whitesmoke;
    }

    .uiverse-submit:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }

    @keyframes pulse-bps {
        from { transform: scale(0.9); opacity: 1; }
        to { transform: scale(1.8); opacity: 0; }
    }

    @keyframes custom-ping {
        75%, 100% { transform: scale(2); opacity: 0; }
    }

    .news-ticker-item {
        display: none;
        flex-direction: column;
        align-items: stretch;
        min-height: 0;
        animation: news-fade-in 2s cubic-bezier(0.22, 1, 0.36, 1);
    }

    .news-ticker-item.is-active {
        display: flex;
    }

    .news-ticker-image {
        width: 100%;
        height: auto;
        max-height: 300px;
        flex: 0 0 auto;
        object-fit: cover;
        display: block;
    }

    .news-ticker-body {
        position: relative;
        height: auto;
        overflow: hidden;
        padding: 0 !important;
    }

    .news-ticker-control {
        position: absolute;
        top: 50%;
        z-index: 10;
        width: 42px;
        height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transform: translateY(-50%);
        border: 0;
        border-radius: 50%;
        color: #1e293b;
        background: rgba(255, 255, 255, 0.9);
        box-shadow: 0 0.25rem 0.75rem rgba(15, 23, 42, 0.2);
        transition: background 0.2s ease, transform 0.2s ease;
    }

    .news-ticker-control:hover {
        background: #fff;
        transform: translateY(-50%) scale(1.05);
    }

    .news-ticker-control-left { left: 0.75rem; }
    .news-ticker-control-right { right: 0.75rem; }

    @media (min-width: 576px) {
        .news-ticker-image {
            max-height: 400px;
        }
    }

    @media (min-width: 992px) {
        .news-ticker-image {
            max-height: none;
            object-fit: contain;
        }
    }

    .registration-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.5rem;
    }

    .registration-layout-left,
    .registration-layout-right {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    @media (min-width: 992px) {
        .registration-layout {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .registration-layout-left { grid-column: span 1; }
        .registration-layout-right { grid-column: span 2; }
    }

    @keyframes news-fade-in {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    @media (prefers-reduced-motion: reduce) {
        .news-ticker-item {
            animation: none;
        }
    }
</style>

<!-- Kotak Pengumuman / Berita Singkat Instansi -->
<div class="alert alert-primary border-0 shadow-sm mb-4 d-flex align-items-center rounded-4" role="alert" style="border-left: 5px solid #002060 !important;">
    <i class="fa fa-bullhorn fa-2x me-3 text-primary"></i>
    <div>
        <h5 class="alert-heading fw-bold mb-1" style="font-size: 1rem; color: #002060;">Pemberitahuan Layanan Tatap Muka</h5>
        <p class="mb-0 small text-muted">Silakan ambil nomor antrian melalui form di bawah ini. Pastikan data kontak Anda aktif agar e-tiket digital dapat langsung dikirimkan ke email Anda.</p>
    </div>
</div>

<div class="registration-layout grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Kolom Kiri: Bantuan dan Berita -->
    <div class="registration-layout-left space-y-6 lg:col-span-1 flex flex-col">
        <div class="card border-0 shadow-sm bg-light rounded-4 mb-2">
            <div class="card-body text-center p-2">
                <i class="fa fa-headset fa-lg text-primary mb-1"></i>
                <h6 class="fw-bold text-dark small mb-1">Butuh Bantuan Layanan?</h6>
                <p class="text-muted mb-1" style="font-size: 0.7rem;">Petugas siap membantu kendala teknis Anda.</p>
                
                <!-- TOMBOL LAYANAN AKTIF DIBUAT KLIKABLE KE WHATSAPP -->
                <a href="https://wa.me/628999331500" target="_blank" rel="noopener noreferrer" class="btn btn-primary btn-sm px-2 py-1 fw-bold" style="border-radius: 8px; font-size: 0.7rem;">
                    <i class="fa-brands fa-whatsapp me-1"></i> Layanan Aktif
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 h-auto overflow-hidden">
            <div class="card-header bg-white py-2 border-bottom border-2 border-warning">
                <h6 class="fw-bold m-0 text-dark"><i class="fa fa-newspaper text-primary me-2"></i> Berita Terbaru BPS Kabupaten Magelang</h6>
            </div>
            <div class="card-body news-ticker-body" id="newsTicker">
                <button type="button" id="newsPrevious" class="news-ticker-control news-ticker-control-left left-3 z-10" aria-label="Berita sebelumnya"><i class="fa fa-chevron-left"></i></button>
                <button type="button" id="newsNext" class="news-ticker-control news-ticker-control-right right-3 z-10" aria-label="Berita berikutnya"><i class="fa fa-chevron-right"></i></button>
                @forelse($berita as $index => $item)
                    <a href="{{ $item->url }}" target="_blank" rel="noopener noreferrer" class="news-ticker-item gap-2 text-decoration-none" data-news-index="{{ $index }}">
                        <div class="p-3 bg-white">
                            <img src="{{ $item->image_url ?: 'https://placehold.co/144x104/e2e8f0/475569?text=BPS' }}" alt="Thumbnail berita" class="news-ticker-image rounded-xl" loading="lazy" onerror="this.src='https://placehold.co/144x104/e2e8f0/475569?text=BPS';">
                        </div>
                        <span class="fw-semibold text-dark px-3 pb-3">{{ $item->title }}</span>
                    </a>
                @empty
                    <p class="small text-muted mb-0">Berita terbaru akan tampil setelah admin menyimpan tautan berita.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Alur dan Form Utama Pendaftaran -->
    <div class="registration-layout-right space-y-6 lg:col-span-2">
        <div class="card border-0 shadow-sm rounded-4 w-full">
            <div class="card-header bg-white py-3 border-bottom border-2 border-warning rounded-top-4">
                <h6 class="fw-bold m-0 text-dark"><i class="fa fa-info-circle text-primary me-2"></i> Alur Pelayanan Antrian</h6>
            </div>
            <div class="card-body">
                <ol class="ps-3 small text-muted mb-0" style="line-height: 1.8;">
                    <li>Mengisi formulir antrian dengan identitas sah.</li>
                    <li>Sistem otomatis mencatat nomor urut harian.</li>
                    <li>E-tiket digital dikirim otomatis ke email pemohon.</li>
                    <li>Tunjukkan e-tiket kepada petugas loket saat nomor dipanggil.</li>
                </ol>
            </div>
        </div>

        <div class="card border-0 shadow-sm bg-transparent p-0">
            
            <form action="{{ route('pendaftaran.store') }}" method="POST" class="uiverse-form" id="formPendaftaran">
                @csrf

                <div class="d-flex justify-content-between align-items-center mb-0">
                    <p class="uiverse-title m-0">Form Antrian</p>
                    @php
                        $waktuSekarang = now('Asia/Jakarta');
                        $hariKerja = $waktuSekarang->isWeekday();
                        $jamBuka = $waktuSekarang->copy()->setTime(8, 0);
                        $jamTutup = $waktuSekarang->copy()->setTime($waktuSekarang->dayOfWeekIso === 5 ? 16 : 15, $waktuSekarang->dayOfWeekIso === 5 ? 0 : 30);
                        $layananBuka = $hariKerja && $waktuSekarang->betweenIncluded($jamBuka, $jamTutup);
                    @endphp
                    @if($layananBuka)
                        <span class="badge bg-success px-2 py-1" style="font-size: 0.7rem;">🟢 Layanan Buka</span>
                    @else
                        <span class="badge bg-secondary px-2 py-1" style="font-size: 0.7rem;">🔴 Layanan Tutup</span>
                    @endif
                </div>
                <p class="text-muted small mb-1" style="font-size: 0.85rem;">Lengkapi data diri Anda untuk mengambil nomor urut pelayanan hari ini.</p>

                <div class="mb-1">
                    <label class="uiverse-label">Nama Lengkap (Sesuai KTP)</label>
                    <input type="text" name="nama" id="inputNama" value="{{ old('nama') }}" placeholder="Masukkan nama lengkap Anda" class="input-custom" required />
                </div>

                <!-- DROPDOWN: Pilihan Layanan / Keperluan -->
                <div class="mb-1">
                    <label class="uiverse-label">Pilih Jenis Layanan / Keperluan</label>
                    <select name="jenis_layanan" id="inputJenisLayanan" class="input-custom" required>
                        <option value="" disabled selected>-- Pilih Jenis Layanan / Keperluan --</option>
                        <option value="Konsultasi Statistik">Konsultasi Statistik</option>
                        <option value="Konsultasi DTSEN">Konsultasi DTSEN</option>
                        <option value="Permintaan Data">Permintaan Data</option>
                        <option value="Rekomendasi Kegiatan Statistik">Rekomendasi Kegiatan Statistik</option>
                        <option value="Pengaduan">Pengaduan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>

                    <div id="containerJenisLayananLainnya" class="mt-2" hidden>
                        <input type="text" name="keterangan_lainnya" id="inputJenisLayananLainnya" class="input-custom" placeholder="Sebutkan jenis layanan / keperluan Anda..." maxlength="50" />
                        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">*Maksimal 50 karakter termasuk spasi.</small>
                    </div>
                </div>

                <div class="mb-1">
                        <label class="uiverse-label" for="inputTanggalKunjungan">Tanggal Kunjungan</label>
                        <input type="date" name="tanggal_kunjungan" id="inputTanggalKunjungan" value="{{ old('tanggal_kunjungan') }}" min="{{ now('Asia/Jakarta')->format('Y-m-d') }}" class="input-custom" required />
                        <small class="text-muted" style="font-size: 0.7rem; margin-top: 2px; display: block;">*Senin - Jumat</small>
                </div>

                <div class="mb-1">
                    <label class="uiverse-label">Alamat Email Aktif</label>
                    <input type="email" name="email" id="inputEmail" value="{{ old('email') }}" placeholder="Contoh: nama@domain.com" class="input-custom" required />
                </div>

                <div class="mb-1">
                    <label class="uiverse-label">Nomor WhatsApp Aktif</label>
                    <input type="text" name="no_wa" id="inputNoWa" value="{{ old('no_wa') }}" placeholder="Contoh: 08123456789 atau 628123456789" class="input-custom" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="15" required />
                    <small class="text-muted" style="font-size: 0.7rem; margin-top: 2px; display: block;">*Hanya angka, diawali 08 atau 628 (min. 10 digit).</small>
                </div>

                <div class="mb-1">
                    <label class="uiverse-label">Alamat Lengkap (Domisili)</label>
                    <textarea name="alamat" id="inputAlamat" placeholder="Contoh: Jl. Merdeka No. 1, Kota Magelang" class="input-custom" rows="2" required>{{ old('alamat') }}</textarea>
                </div>

                <button type="button" class="uiverse-submit" id="btnUtamaSubmit" onclick="bukaModalKonfirmasi()">
                    <i class="fa fa-paper-plane me-2"></i> Proses Ambil Nomor Antrian
                </button>
            </form>

        </div>
    </div>
</div>

<!-- MODAL KONFIRMASI CEK DATA -->
<div class="modal fade" id="modalKonfirmasiData" tabindex="-1" aria-labelledby="modalKonfirmasiLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; background-color: #f8fafc; border: 1px solid #cbd5e1 !important;">
            
            <div class="modal-header border-0 pb-0 pt-4 px-4 bg-transparent">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 42px; height: 42px; background-color: rgba(2, 132, 199, 0.15); color: #0284c7;">
                        <i class="fa fa-user-check fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-0 text-dark" style="font-size: 1.1rem;" id="modalKonfirmasiLabel">Periksa Kembali Data Anda</h5>
                    </div>
                </div>
            </div>

            <div class="modal-body px-4 py-3">
                <p class="small text-muted mb-3" style="line-height: 1.5;">
                    Pastikan data diri di bawah ini sudah benar sebelum e-tiket dan nomor antrian diproses:
                </p>
                <div class="bg-white p-3 rounded-3 border small shadow-sm">
                    <div class="mb-2"><strong>Nama Lengkap:</strong> <span id="prevNama" class="text-secondary"></span></div>
                    <div class="mb-2"><strong>Jenis Layanan:</strong> <span id="prevJenisLayanan" class="text-secondary"></span></div>
                    <div class="mb-2"><strong>Tanggal Kunjungan:</strong> <span id="prevTanggalKunjungan" class="text-secondary"></span></div>
                    <div class="mb-2"><strong>Email Aktif:</strong> <span id="prevEmail" class="text-secondary"></span></div>
                    <div class="mb-2"><strong>Nomor WhatsApp:</strong> <span id="prevNoWa" class="text-secondary"></span></div>
                    <div><strong>Alamat Domisili:</strong> <span id="prevAlamat" class="text-secondary"></span></div>
                </div>
            </div>

            <div class="modal-footer border-0 bg-transparent justify-content-between pb-4 px-4 pt-2">
                <button type="button" class="btn fw-bold px-4 py-2 text-secondary bg-white border" data-bs-dismiss="modal" onclick="batalKonfirmasi()" style="border-radius: 8px; font-size: 0.9rem;">
                    Periksa Lagi
                </button>
                <button type="button" class="btn fw-bold px-4 py-2 text-white" onclick="kirimFormFinal()" id="btnKirimFinal" style="background-color: #0284c7; border-radius: 8px; font-size: 0.9rem;">
                    <i class="fa fa-check me-1"></i> Ya, Data Sudah Benar & Kirim
                </button>
            </div>

        </div>
    </div>
</div>

<!-- MODAL POP-UP ERROR -->
<div class="modal fade" id="errorPopupModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; background-color: #fffbeb; border: 1px solid #fde68a !important;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 bg-transparent">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 42px; height: 42px; background-color: rgba(245, 158, 11, 0.15); color: #d97706;">
                        <i class="fa fa-clock fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-0" style="color: #92400e; font-size: 1rem;" id="errorModalTitle">Pemberitahuan Sistem</h5>
                    </div>
                </div>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="small mb-0 text-dark" id="errorModalMessage" style="line-height: 1.6; font-size: 0.95rem;"></p>
            </div>
            <div class="modal-footer border-0 bg-transparent justify-content-end pb-4 px-4 pt-2">
                <button type="button" class="btn fw-bold px-4 py-2 text-white" data-bs-dismiss="modal" style="background-color: #d97706; border-radius: 8px; font-size: 0.9rem;">
                    <i class="fa fa-check me-1"></i> Oke, Saya Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL POP-UP BERHASIL -->
<div class="modal fade" id="successPopupModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 14px; overflow: hidden; background-color: #f0fdf4; border: 1px solid #bbf7d0 !important;">
            <div class="modal-header border-0 pb-0 pt-4 px-4 bg-transparent">
                <div class="d-flex align-items-center gap-3 w-100">
                    <div class="d-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 42px; height: 42px; background-color: rgba(34, 197, 94, 0.15); color: #15803d;">
                        <i class="fa fa-check-circle fa-lg"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-0" style="color: #166534; font-size: 1.1rem;">Pendaftaran Berhasil!</h5>
                    </div>
                </div>
            </div>
            <div class="modal-body px-4 py-3">
                <p class="small mb-0" id="successMessageText" style="color: #15803d !important; line-height: 1.6; font-size: 0.95rem;"></p>
            </div>
            <div class="modal-footer border-0 bg-transparent justify-content-end pb-4 px-4 pt-2">
                <button type="button" class="btn fw-bold px-5 py-2 text-white" data-bs-dismiss="modal" onclick="location.reload();" style="background-color: #16a34a; border-radius: 8px; font-size: 0.9rem;">
                    <i class="fa fa-thumbs-up me-1"></i> Oke, Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Skrip JavaScript -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const textarea = document.getElementById('inputAlamat');
        const jenisLayanan = document.getElementById('inputJenisLayanan');
        const jenisLayananLainnya = document.getElementById('inputJenisLayananLainnya');
        const containerJenisLayananLainnya = document.getElementById('containerJenisLayananLainnya');
        const tanggalKunjungan = document.getElementById('inputTanggalKunjungan');

        function toggleJenisLayananLainnya() {
            const isLainnya = jenisLayanan.value === 'Lainnya';

            if (containerJenisLayananLainnya) {
                containerJenisLayananLainnya.hidden = !isLainnya;
            }

            if (jenisLayananLainnya) {
                jenisLayananLainnya.required = isLainnya;
                jenisLayananLainnya.setCustomValidity('');
            }

            if (!isLainnya && jenisLayananLainnya) {
                jenisLayananLainnya.value = '';
            }
        }

        if (jenisLayanan && jenisLayananLainnya) {
            jenisLayanan.addEventListener('change', toggleJenisLayananLainnya);
            jenisLayananLainnya.addEventListener('input', function () {
                jenisLayananLainnya.setCustomValidity('');
            });
            toggleJenisLayananLainnya();
        }

        if (tanggalKunjungan) {
            tanggalKunjungan.addEventListener('change', function () {
                const tanggal = new Date(`${tanggalKunjungan.value}T00:00:00`);
                const hari = tanggal.getDay();

                tanggalKunjungan.setCustomValidity(hari === 0 || hari === 6 ? 'Tanggal kunjungan hanya tersedia pada hari Senin sampai Jumat.' : '');
            });
        }

        if (textarea) {
            textarea.style.resize = 'none';
            textarea.style.overflow = 'hidden';

            function adjustHeight() {
                textarea.style.height = 'auto';
                textarea.style.height = (textarea.scrollHeight) + 'px';
            }

            adjustHeight();
            textarea.addEventListener('input', adjustHeight);
        }
    });

    function bukaModalKonfirmasi() {
        let form = document.getElementById('formPendaftaran');
        const jenisLayanan = document.getElementById('inputJenisLayanan');
        const jenisLayananLainnya = document.getElementById('inputJenisLayananLainnya');

        if (jenisLayanan.value === 'Lainnya' && !jenisLayananLainnya.value.trim()) {
            jenisLayananLainnya.setCustomValidity('Silakan isi jenis layanan/keperluan Anda.');
        } else {
            jenisLayananLainnya.setCustomValidity('');
        }

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        let btnUtama = document.getElementById('btnUtamaSubmit');
        btnUtama.disabled = true;
        btnUtama.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Menunggu Konfirmasi...';

        document.getElementById('prevNama').innerText = document.getElementById('inputNama').value;
        const jenisLayananValue = jenisLayanan.value === 'Lainnya'
            ? `Lainnya: ${jenisLayananLainnya.value.trim()}`
            : jenisLayanan.value;

        document.getElementById('prevJenisLayanan').innerText = jenisLayananValue;
        document.getElementById('prevTanggalKunjungan').innerText = document.getElementById('inputTanggalKunjungan').value;
        document.getElementById('prevEmail').innerText = document.getElementById('inputEmail').value;
        document.getElementById('prevNoWa').innerText = document.getElementById('inputNoWa').value;
        document.getElementById('prevAlamat').innerText = document.getElementById('inputAlamat').value;

        var confirmModal = new bootstrap.Modal(document.getElementById('modalKonfirmasiData'));
        confirmModal.show();
    }

    function batalKonfirmasi() {
        let btnUtama = document.getElementById('btnUtamaSubmit');
        btnUtama.disabled = false;
        btnUtama.innerHTML = '<i class="fa fa-paper-plane me-2"></i> Proses Ambil Nomor Antrian';
    }

    function kirimFormFinal() {
        var confirmModalEl = document.getElementById('modalKonfirmasiData');
        var modalInstance = bootstrap.Modal.getInstance(confirmModalEl);
        if (modalInstance) {
            modalInstance.hide();
        }

        let form = document.getElementById('formPendaftaran');
        let jenisLayanan = document.getElementById('inputJenisLayanan');
        let jenisLayananLainnya = document.getElementById('inputJenisLayananLainnya');
        let formData = new FormData(form);

        formData.set('jenis_layanan', jenisLayanan.value);
        formData.set('keterangan_lainnya', jenisLayanan.value === 'Lainnya' ? jenisLayananLainnya.value.trim() : '');

        let btnUtama = document.getElementById('btnUtamaSubmit');
        let btnKirim = document.getElementById('btnKirimFinal');

        btnUtama.disabled = true;
        btnUtama.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Sedang Memproses...';

        btnKirim.disabled = true;
        btnKirim.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Memproses...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            if (res.status === 200 && res.body.success) {
                document.getElementById('successMessageText').innerText = res.body.message;
                var successModal = new bootstrap.Modal(document.getElementById('successPopupModal'));
                successModal.show();
            } else {
                btnUtama.disabled = false;
                btnUtama.innerHTML = '<i class="fa fa-paper-plane me-2"></i> Proses Ambil Nomor Antrian';

                btnKirim.disabled = false;
                btnKirim.innerHTML = '<i class="fa fa-check me-1"></i> Ya, Data Sudah Benar & Kirim';

                document.getElementById('errorModalMessage').innerText = res.body.message || 'Terjadi kesalahan pada sistem.';
                var errorModal = new bootstrap.Modal(document.getElementById('errorPopupModal'));
                errorModal.show();
            }
        })
        .catch(error => {
            btnUtama.disabled = false;
            btnUtama.innerHTML = '<i class="fa fa-paper-plane me-2"></i> Proses Ambil Nomor Antrian';

            btnKirim.disabled = false;
            btnKirim.innerHTML = '<i class="fa fa-check me-1"></i> Ya, Data Sudah Benar & Kirim';

            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan atau server.');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const ticker = document.getElementById('newsTicker');
        const previousButton = document.getElementById('newsPrevious');
        const nextButton = document.getElementById('newsNext');
        let tickerItems = [];
        let activeIndex = 0;
        let newsSignature = '';

        function showNews(index) {
            if (tickerItems.length === 0) return;

            tickerItems[activeIndex]?.classList.remove('is-active');
            activeIndex = (index + tickerItems.length) % tickerItems.length;
            tickerItems[activeIndex].classList.add('is-active');
        }

        function startAutoRotate() {
            setInterval(() => {
                if (tickerItems.length > 1) {
                    showNews(activeIndex + 1);
                }
            }, 5000);
        }

        function renderNews(items) {
            const nextSignature = JSON.stringify(items.map(item => ({
                url: item.url,
                title: item.title,
                image_url: item.image_url || '',
            })));

            if (nextSignature === newsSignature) {
                return;
            }

            newsSignature = nextSignature;
            tickerItems.forEach(item => item.remove());
            tickerItems = items.map(item => {
                const link = document.createElement('a');
                link.href = item.url;
                link.target = '_blank';
                link.rel = 'noopener noreferrer';
                link.className = 'news-ticker-item gap-2 text-decoration-none';

                const image = document.createElement('img');
                image.src = item.image_url || 'https://placehold.co/144x104/e2e8f0/475569?text=BPS';
                image.alt = 'Thumbnail berita';
                image.className = 'news-ticker-image';
                image.loading = 'lazy';
                image.onerror = () => { image.src = 'https://placehold.co/144x104/e2e8f0/475569?text=BPS'; };

                const imageWrapper = document.createElement('div');
                imageWrapper.className = 'p-3 bg-white';
                image.classList.add('rounded-xl');
                imageWrapper.appendChild(image);

                const title = document.createElement('span');
                title.className = 'fw-semibold text-dark px-3 pb-3';
                title.textContent = item.title;
                link.append(imageWrapper, title);
                ticker.appendChild(link);
                return link;
            });

            activeIndex = Math.min(activeIndex, Math.max(tickerItems.length - 1, 0));
            if (tickerItems.length > 0) {
                tickerItems[activeIndex].classList.add('is-active');
            }
            previousButton?.classList.toggle('d-none', tickerItems.length < 2);
            nextButton?.classList.toggle('d-none', tickerItems.length < 2);
        }

        function refreshNews() {
            fetch("{{ route('api.berita') }}")
                .then(response => response.json())
                .then(items => renderNews(items))
                .catch(error => console.error('Gagal memperbarui berita:', error));
        }

        previousButton?.addEventListener('click', function () {
            showNews(activeIndex - 1);
        });
        nextButton?.addEventListener('click', function () {
            showNews(activeIndex + 1);
        });

        renderNews(Array.from(document.querySelectorAll('.news-ticker-item')).map(item => ({
            url: item.href,
            title: item.querySelector('span')?.textContent || '',
            image_url: item.querySelector('img')?.src || '',
        })));
        startAutoRotate();
        setInterval(refreshNews, 10000);
    });

</script>

@endsection