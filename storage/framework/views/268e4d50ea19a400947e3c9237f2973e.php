<?php $__env->startSection('title', 'Form Pengambilan Antrian - SANTIKA BPS'); ?>

<?php $__env->startSection('content'); ?>

<!-- Memuat CDN SweetAlert2 untuk mengatasi error Swal is not defined -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.26.25/dist/sweetalert2.all.min.js" integrity="sha384-nLoOnA/BDh8A/jxqtckg4DumuCGOBYUnNJLZdQz/zfYNp3wcjGSoWTAzgko06G/2" crossorigin="anonymous"></script>
<script>
    // Selalu ambil token CSRF FRESH dari server saat halaman dibuka,
    // supaya kebal terhadap HTML yang mungkin di-cache oleh server
    let tokenCsrfSegar = null;
    fetch("<?php echo e(route('csrf.refresh')); ?>", { cache: 'no-store' })
        .then(response => response.json())
        .then(data => {
            tokenCsrfSegar = data.token;
            document.querySelectorAll('input[name="_token"]').forEach(function (input) {
                input.value = data.token;
            });
        })
        .catch(() => {
            // Kalau gagal, nanti fallback pakai token bawaan halaman saat submit
        });
</script>

<!-- CSS Kustom: Perbaikan Rantai Flex di Mode HP dan Absolute Image Positioning -->
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

    .uiverse-form .input-custom.is-invalid {
        border-color: #dc3545;
        box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.12);
    }

    .field-validation-message {
        display: block;
        margin-top: 0.25rem;
        color: #b42318;
        font-size: 0.75rem;
    }

    .email-duplicate-status {
        display: block;
        min-height: 1rem;
        margin-top: 0.25rem;
        font-size: 0.75rem;
    }

    .email-duplicate-status.is-success { color: #157347; }
    .email-duplicate-status.is-warning { color: #997404; }

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

    /* Layout Utama: Native Grid dengan align-items: stretch */
    .registration-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr);
        gap: 1.5rem;
        align-items: stretch;
    }

    .registration-layout-left {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .registration-layout-right {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    /* Kartu Berita Kiri */
    .news-card-container {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
    }

    .news-card-body {
        position: relative;
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        padding: 0 !important;
    }

    .news-ticker-item {
        display: none;
        opacity: 0;
        transform: translateY(12px);
        transition: opacity 0.6s ease-in-out, transform 0.6s ease-in-out;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        justify-content: space-between;
        text-decoration: none;
    }

    .news-ticker-item.is-active {
        display: flex;
        opacity: 1;
        transform: translateY(0);
    }

    /* Pembungkus Gambar Berita: Menggunakan position: relative sebagai acuan absolute anak */
    .news-image-wrapper {
        position: relative;
        flex: 1;
        min-height: 0;
        background-color: #ffffff;
        width: 100%;
        overflow: hidden;
    }

    /* Gambar Berita: Absolute dan keluar dari layout flow alami agar tidak merusak grid */
    .news-ticker-image {
        position: absolute;
        inset: 10px;
        width: calc(100% - 20px);
        height: calc(100% - 20px);
        object-fit: contain;
        display: block;
    }

    .news-ticker-control {
        position: absolute;
        top: 45%;
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

    .visit-date-picker {
        position: relative;
    }

    .visit-date-input {
        display: flex;
        gap: 0.5rem;
    }

    .visit-date-input .input-custom {
        min-width: 0;
        cursor: pointer;
    }

    .visit-date-toggle {
        flex: 0 0 44px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        color: #0284c7;
        background: #fff;
    }

    .visit-date-calendar {
        position: absolute;
        top: calc(100% + 6px);
        left: 0;
        z-index: 20;
        width: min(100%, 360px);
        padding: 14px;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
    }

    .visit-date-calendar[hidden] {
        display: none;
    }

    .visit-date-calendar-header,
    .visit-date-calendar-grid {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 4px;
    }

    .visit-date-calendar-header {
        align-items: center;
        margin-bottom: 6px;
    }

    .visit-date-calendar-header button {
        border: 0;
        border-radius: 6px;
        padding: 6px 2px;
        color: #334155;
        background: transparent;
        font-weight: 700;
    }

    .visit-date-calendar-header button:hover:not(:disabled) {
        background: #e0f2fe;
    }

    .visit-date-calendar-header button:disabled {
        opacity: 0.4;
        cursor: not-allowed;
    }

    .visit-date-calendar-weekdays,
    .visit-date-calendar-day {
        display: flex;
        min-height: 38px;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 0.84rem;
    }

    .visit-date-calendar-weekdays {
        color: #64748b;
        font-weight: 700;
    }

    .visit-date-calendar-day {
        border: 1px solid transparent;
        color: #1e293b;
        background: transparent;
    }

    .visit-date-calendar-day:hover:not(:disabled) {
        border-color: #0284c7;
        background: #e0f2fe;
    }

    .visit-date-calendar-day.is-selected {
        color: #fff;
        background: #0284c7;
    }

    .visit-date-calendar-day:disabled {
        color: #94a3b8;
        background: #f1f5f9;
        cursor: not-allowed;
        opacity: 0.55;
    }

    .visit-date-calendar-day.is-holiday:disabled {
        color: #b91c1c;
        background: #fee2e2;
        text-decoration: line-through;
    }

    /* Perbaikan Mode HP (< 992px): Putus rantai flex:1 dan gunakan height tetap */
    @media (max-width: 991px) {
        .news-card-container,
        .news-card-body,
        .news-ticker-item {
            flex: initial;
            min-height: auto;
        }
        .news-image-wrapper {
            flex: none;
            height: 220px;
        }
        .registration-layout-right {
            order: 1;
        }
        .registration-layout-left {
            order: 2;
        }
    }

    @media (min-width: 992px) {
        .registration-layout {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .registration-layout-left { grid-column: span 1; }
        .registration-layout-right { grid-column: span 2; }
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

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden news-card-container">
            <div class="card-header bg-white py-2 border-bottom border-2 border-warning">
                <h6 class="fw-bold m-0 text-dark"><i class="fa fa-newspaper text-primary me-2"></i> Berita Terbaru BPS Kabupaten Magelang</h6>
            </div>
            <div class="card-body news-card-body" id="newsTicker">
                <button type="button" id="newsPrevious" class="news-ticker-control news-ticker-control-left left-3 z-10" aria-label="Berita sebelumnya"><i class="fa fa-chevron-left"></i></button>
                <button type="button" id="newsNext" class="news-ticker-control news-ticker-control-right right-3 z-10" aria-label="Berita berikutnya"><i class="fa fa-chevron-right"></i></button>
                <?php $__empty_1 = true; $__currentLoopData = $berita; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index =>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <a href="<?php echo e($item->url); ?>" target="_blank" rel="noopener noreferrer" class="news-ticker-item gap-2" data-news-index="<?php echo e($index); ?>">
                        <div class="news-image-wrapper">
                            <img src="<?php echo e($item->image_url ?: 'https://placehold.co/144x104/e2e8f0/475569?text=BPS'); ?>" alt="Thumbnail berita" class="news-ticker-image" loading="lazy" onerror="this.src='https://placehold.co/144x104/e2e8f0/475569?text=BPS';">
                        </div>
                        <span class="fw-semibold text-dark px-3 pb-3"><?php echo e($item->title); ?></span>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="small text-muted mb-0 p-3">Berita terbaru akan tampil setelah admin menyimpan tautan berita.</p>
                <?php endif; ?>
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
            
            <form action="<?php echo e(route('pendaftaran.store')); ?>" method="POST" class="uiverse-form" id="formPendaftaran" novalidate>
                <?php echo csrf_field(); ?>

                <div class="d-flex justify-content-between align-items-center mb-0">
                    <p class="uiverse-title m-0">Form Antrian</p>
                    <?php
                        $waktuSekarang = now('Asia/Jakarta');$hariKerja = $waktuSekarang->isWeekday();$jamBuka = $waktuSekarang->copy()->setTime(8, 0);$jamTutup = $waktuSekarang->copy()->setTime($waktuSekarang->dayOfWeekIso === 5 ? 16 : 15, $waktuSekarang->dayOfWeekIso === 5 ? 0 : 30);$layananBuka = $hariKerja &&$waktuSekarang->betweenIncluded($jamBuka,$jamTutup);
                    ?>
                    <?php if($layananBuka): ?>
                        <span class="badge bg-success px-2 py-1" style="font-size: 0.7rem;">🟢 Layanan Buka</span>
                    <?php else: ?>
                        <span class="badge bg-secondary px-2 py-1" style="font-size: 0.7rem;">🔴 Layanan Tutup</span>
                    <?php endif; ?>
                </div>
                <p class="text-muted small mb-1" style="font-size: 0.85rem;">Lengkapi data diri Anda untuk mengambil nomor urut pelayanan hari ini.</p>

                <div class="mb-1">
                    <label class="uiverse-label" for="inputNama">Nama Lengkap (Sesuai KTP)</label>
                    <input type="text" name="nama" id="inputNama" value="<?php echo e(old('nama')); ?>" placeholder="Masukkan nama lengkap Anda" class="input-custom" maxlength="255" aria-describedby="errorNama" aria-invalid="false" data-validate-field="nama" required />
                    <small class="field-validation-message" id="errorNama" data-validation-error="nama" role="alert" hidden></small>
                </div>

                <!-- DROPDOWN: Pilihan Layanan / Keperluan -->
                <div class="mb-1">
                    <label class="uiverse-label" for="inputJenisLayanan">Pilih Jenis Layanan / Keperluan</label>
                    <select name="jenis_layanan" id="inputJenisLayanan" class="input-custom" aria-describedby="errorJenisLayanan" aria-invalid="false" data-validate-field="jenis_layanan" required>
                        <option value="" disabled selected>-- Pilih Jenis Layanan / Keperluan --</option>
                        <option value="Konsultasi Statistik">Konsultasi Statistik</option>
                        <option value="Konsultasi DTSEN">Konsultasi DTSEN</option>
                        <option value="Permintaan Data">Permintaan Data</option>
                        <option value="Rekomendasi Kegiatan Statistik">Rekomendasi Kegiatan Statistik</option>
                        <option value="Pengaduan">Pengaduan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>

                    <div id="containerJenisLayananLainnya" class="mt-2" hidden>
                        <input type="text" name="keterangan_lainnya" id="inputJenisLayananLainnya" class="input-custom" placeholder="Sebutkan jenis layanan / keperluan Anda..." maxlength="50" aria-describedby="errorKeteranganLainnya" aria-invalid="false" data-validate-field="keterangan_lainnya" />
                        <small class="text-muted d-block mt-1" style="font-size: 0.7rem;">*Maksimal 50 karakter termasuk spasi.</small>
                        <small class="field-validation-message" id="errorKeteranganLainnya" data-validation-error="keterangan_lainnya" role="alert" hidden></small>
                    </div>
                    <small class="field-validation-message" id="errorJenisLayanan" data-validation-error="jenis_layanan" role="alert" hidden></small>
                </div>

                <div class="mb-1">
                    <label class="uiverse-label" for="inputTanggalKunjungan">Tanggal Kunjungan</label>
                    <div class="visit-date-picker">
                        <div class="visit-date-input">
                            <input type="text" id="inputTanggalKunjungan" class="input-custom" placeholder="Pilih tanggal kunjungan" aria-haspopup="dialog" aria-expanded="false" aria-controls="calendarTanggalKunjungan" aria-describedby="tanggalKunjunganError" aria-invalid="false" data-validate-field="tanggal_kunjungan" autocomplete="off" readonly required />
                            <input type="hidden" name="tanggal_kunjungan" id="tanggalKunjunganValue" value="<?php echo e(old('tanggal_kunjungan')); ?>" />
                            <button type="button" class="visit-date-toggle" id="toggleCalendarTanggalKunjungan" aria-label="Buka kalender tanggal kunjungan" aria-controls="calendarTanggalKunjungan" aria-expanded="false">
                                <i class="fa fa-calendar-days" aria-hidden="true"></i>
                            </button>
                        </div>
                        <div class="visit-date-calendar" id="calendarTanggalKunjungan" role="dialog" aria-label="Pilih tanggal kunjungan" hidden>
                            <div class="visit-date-calendar-header">
                                <button type="button" id="previousMonthTanggalKunjungan" aria-label="Bulan sebelumnya"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                                <strong class="text-center" id="monthTanggalKunjungan" style="grid-column: span 5;" aria-live="polite"></strong>
                                <button type="button" id="nextMonthTanggalKunjungan" aria-label="Bulan berikutnya"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
                            </div>
                            <div class="visit-date-calendar-grid mb-1" aria-hidden="true">
                                <span class="visit-date-calendar-weekdays">Sen</span>
                                <span class="visit-date-calendar-weekdays">Sel</span>
                                <span class="visit-date-calendar-weekdays">Rab</span>
                                <span class="visit-date-calendar-weekdays">Kam</span>
                                <span class="visit-date-calendar-weekdays">Jum</span>
                                <span class="visit-date-calendar-weekdays">Sab</span>
                                <span class="visit-date-calendar-weekdays">Min</span>
                            </div>
                            <div class="visit-date-calendar-grid" id="daysTanggalKunjungan" role="grid" aria-label="Tanggal dalam bulan"></div>
                        </div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem; margin-top: 4px; display: block;">Pilih hari Senin–Jumat. Akhir pekan dan hari libur nasional berwarna redup dan tidak dapat dipilih.</small>
                    <small class="field-validation-message" id="tanggalKunjunganError" data-validation-error="tanggal_kunjungan" role="alert" hidden></small>
                    <?php if (! ($holidayCalendarAvailable)): ?>
                        <small class="text-danger d-block mt-1" style="font-size: 0.7rem;">Kalender hari libur nasional tidak dapat dimuat saat ini. Akhir pekan tetap tidak tersedia.</small>
                    <?php endif; ?>
                </div>

                <div class="mb-1">
                    <label class="uiverse-label" for="inputEmail">Alamat Email Aktif</label>
                    <input type="email" name="email" id="inputEmail" value="<?php echo e(old('email')); ?>" placeholder="Contoh: nama@domain.com" class="input-custom" aria-describedby="hintEmail errorEmail" aria-invalid="false" data-validate-field="email" required />
                    <small class="text-muted d-block mt-1" id="hintEmail">Satu alamat email hanya dapat digunakan satu kali untuk tanggal kunjungan yang sama.</small>
                    <small class="field-validation-message" id="errorEmail" data-validation-error="email" role="alert" hidden></small>
                    <small class="email-duplicate-status" id="emailDuplicateStatus" aria-live="polite" hidden></small>
                </div>

                <div class="mb-1">
                    <label class="uiverse-label" for="inputNoWa">Nomor WhatsApp Aktif</label>
                    <input type="text" name="no_wa" id="inputNoWa" value="<?php echo e(old('no_wa')); ?>" placeholder="Contoh: 08123456789 atau 628123456789" class="input-custom" oninput="this.value = this.value.replace(/[^0-9]/g, '')" maxlength="15" aria-describedby="hintNoWa errorNoWa" aria-invalid="false" data-validate-field="no_wa" required />
                    <small class="text-muted" id="hintNoWa" style="font-size: 0.7rem; margin-top: 2px; display: block;">*Hanya angka, diawali 08 atau 628 (min. 10 digit).</small>
                    <small class="field-validation-message" id="errorNoWa" data-validation-error="no_wa" role="alert" hidden></small>
                </div>

                <div class="mb-1">
                    <label class="uiverse-label" for="inputAlamat">Alamat Lengkap (Domisili)</label>
                    <textarea name="alamat" id="inputAlamat" placeholder="Contoh: Jl. Merdeka No. 1, Kota Magelang" class="input-custom" rows="2" aria-describedby="errorAlamat" aria-invalid="false" data-validate-field="alamat" required><?php echo e(old('alamat')); ?></textarea>
                    <small class="field-validation-message" id="errorAlamat" data-validation-error="alamat" role="alert" hidden></small>
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

<!-- Skrip JavaScript -->
<script>
    const publicHolidays = <?php echo \Illuminate\Support\Js::from($publicHolidays)->toHtml() ?>;
    const minimumDate = <?php echo \Illuminate\Support\Js::from(now('Asia/Jakarta')->format('Y-m-d'))->toHtml() ?>;
    const duplicateEmailCheckUrl = <?php echo \Illuminate\Support\Js::from(route('pendaftaran.check-email'))->toHtml() ?>;
    let duplicateCheckTimer = null;
    let duplicateCheckController = null;
    let duplicateCheckResult = null;

    document.addEventListener("DOMContentLoaded", function () {
        const textarea = document.getElementById('inputAlamat');
        const jenisLayanan = document.getElementById('inputJenisLayanan');
        const jenisLayananLainnya = document.getElementById('inputJenisLayananLainnya');
        const containerJenisLayananLainnya = document.getElementById('containerJenisLayananLainnya');
        const tanggalKunjungan = document.getElementById('inputTanggalKunjungan');
        const tanggalKunjunganValue = document.getElementById('tanggalKunjunganValue');
        const calendar = document.getElementById('calendarTanggalKunjungan');
        const calendarToggle = document.getElementById('toggleCalendarTanggalKunjungan');
        const tanggalKunjunganError = document.getElementById('tanggalKunjunganError');
        const calendarMonth = document.getElementById('monthTanggalKunjungan');
        const calendarDays = document.getElementById('daysTanggalKunjungan');
        const previousMonth = document.getElementById('previousMonthTanggalKunjungan');
        const nextMonth = document.getElementById('nextMonthTanggalKunjungan');
        const selectedDate = tanggalKunjunganValue.value;
        const initialDate = selectedDate ? new Date(`${selectedDate}T00:00:00`) : new Date(`${minimumDate}T00:00:00`);
        let visibleMonth = new Date(initialDate.getFullYear(), initialDate.getMonth(), 1);
        const monthFormatter = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
        const dateFormatter = new Intl.DateTimeFormat('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });

        function toDateValue(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');

            return `${year}-${month}-${day}`;
        }

        function renderCalendar() {
            const firstDay = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);
            const firstGridDate = new Date(firstDay);
            const mondayOffset = (firstDay.getDay() + 6) % 7;
            firstGridDate.setDate(firstGridDate.getDate() - mondayOffset);

            calendarMonth.textContent = monthFormatter.format(visibleMonth);
            previousMonth.disabled = visibleMonth.getFullYear() === Number(minimumDate.slice(0, 4))
                && visibleMonth.getMonth() === Number(minimumDate.slice(5, 7)) - 1;
            calendarDays.replaceChildren();

            for (let index = 0; index < 42; index++) {
                const date = new Date(firstGridDate);
                date.setDate(firstGridDate.getDate() + index);

                const dateValue = toDateValue(date);
                const holidayName = publicHolidays[dateValue];
                const isWeekend = date.getDay() === 0 || date.getDay() === 6;
                const isOutsideMonth = date.getMonth() !== visibleMonth.getMonth();
                const isPast = dateValue < minimumDate;
                const isUnavailable = isWeekend || Boolean(holidayName) || isPast || isOutsideMonth;
                const dayButton = document.createElement('button');

                dayButton.type = 'button';
                dayButton.className = 'visit-date-calendar-day';
                dayButton.textContent = String(date.getDate());
                dayButton.dataset.date = dateValue;
                dayButton.setAttribute('role', 'gridcell');
                dayButton.setAttribute('aria-label', dateFormatter.format(date));
                dayButton.disabled = isUnavailable;

                if (dateValue === tanggalKunjunganValue.value) {
                    dayButton.classList.add('is-selected');
                    dayButton.setAttribute('aria-pressed', 'true');
                } else {
                    dayButton.setAttribute('aria-pressed', 'false');
                }

                if (holidayName) {
                    dayButton.classList.add('is-holiday');
                    dayButton.title = `${holidayName} - hari libur nasional`;
                } else if (isWeekend) {
                    dayButton.title = 'Akhir pekan - tanggal tidak tersedia';
                } else if (isPast) {
                    dayButton.title = 'Tanggal yang sudah lewat tidak tersedia';
                }

                calendarDays.appendChild(dayButton);
            }
        }

        function closeCalendar() {
            calendar.hidden = true;
            tanggalKunjungan.setAttribute('aria-expanded', 'false');
            calendarToggle.setAttribute('aria-expanded', 'false');
        }

        function openCalendar() {
            calendar.hidden = false;
            tanggalKunjungan.setAttribute('aria-expanded', 'true');
            calendarToggle.setAttribute('aria-expanded', 'true');
            renderCalendar();
        }

        if (tanggalKunjunganValue.value) {
            const selected = new Date(`${tanggalKunjunganValue.value}T00:00:00`);

            if (!Number.isNaN(selected.getTime())) {
                tanggalKunjungan.value = dateFormatter.format(selected);
            }
        }

        renderCalendar();
        tanggalKunjungan.addEventListener('click', openCalendar);
        calendarToggle.addEventListener('click', function () {
            if (calendar.hidden) {
                openCalendar();
            } else {
                closeCalendar();
            }
        });
        previousMonth.addEventListener('click', function () {
            visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() - 1, 1);
            renderCalendar();
        });
        nextMonth.addEventListener('click', function () {
            visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + 1, 1);
            renderCalendar();
        });
        calendarDays.addEventListener('click', function (event) {
            const dayButton = event.target.closest('button[data-date]');

            if (!dayButton || dayButton.disabled) {
                return;
            }

            tanggalKunjunganValue.value = dayButton.dataset.date;
            tanggalKunjungan.value = dateFormatter.format(new Date(`${dayButton.dataset.date}T00:00:00`));
            setRegistrationFieldError('tanggal_kunjungan', '');
            scheduleDuplicateEmailCheck();
            renderCalendar();
            closeCalendar();
        });
        document.addEventListener('click', function (event) {
            if (!calendar.hidden && !event.target.closest('.visit-date-picker')) {
                closeCalendar();
            }
        });

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
            toggleJenisLayananLainnya();
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

        bindRegistrationValidation();
    });

    const registrationFieldNames = [
        'nama',
        'jenis_layanan',
        'keterangan_lainnya',
        'tanggal_kunjungan',
        'email',
        'no_wa',
        'alamat',
    ];

    function registrationFieldControl(field) {
        if (field === 'tanggal_kunjungan') {
            return document.getElementById('inputTanggalKunjungan');
        }

        return document.querySelector(`[name="${field}"]`);
    }

    function setRegistrationFieldError(field, message) {
        const control = registrationFieldControl(field);
        const error = document.querySelector(`[data-validation-error="${field}"]`);

        if (!control || !error) {
            return;
        }

        error.textContent = message;
        error.hidden = !message;
        control.classList.toggle('is-invalid', Boolean(message));
        control.setAttribute('aria-invalid', message ? 'true' : 'false');
    }

    function getRegistrationFieldError(field) {
        const control = registrationFieldControl(field);
        const value = control?.value ?? '';
        const containsMarkup = /<\/?[a-z][^>]*>/i.test(value);

        if (containsMarkup) {
            return 'Tag HTML tidak diperbolehkan.';
        }

        if (field === 'nama') {
            if (!value.trim()) return 'Nama wajib diisi.';
            if (value.length > 255) return 'Nama maksimal 255 karakter.';
            if (!/^[\p{L}\p{M}\s.,']+$/u.test(value)) return 'Nama hanya boleh berisi huruf, spasi, titik, koma, dan apostrof.';
        }

        if (field === 'jenis_layanan' && !value) {
            return 'Silakan pilih jenis layanan.';
        }

        if (field === 'keterangan_lainnya') {
            if (document.getElementById('inputJenisLayanan')?.value !== 'Lainnya') return '';
            if (!value.trim()) return 'Keterangan layanan wajib diisi.';
            if (value.length > 50) return 'Keterangan maksimal 50 karakter.';
            if (!/^[\p{L}\p{M}\d\s.,'\/():-]*$/u.test(value)) return 'Keterangan mengandung karakter yang tidak diizinkan.';
        }

        if (field === 'tanggal_kunjungan') {
            const dateValue = document.getElementById('tanggalKunjunganValue')?.value ?? '';

            if (!dateValue) return 'Silakan pilih tanggal kunjungan.';
            if (!/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) return 'Format tanggal kunjungan tidak valid.';

            const [year, month, day] = dateValue.split('-').map(Number);
            const selectedDate = new Date(year, month - 1, day);

            if (selectedDate.getFullYear() !== year || selectedDate.getMonth() !== month - 1 || selectedDate.getDate() !== day) {
                return 'Tanggal kunjungan tidak valid.';
            }
            if (dateValue < minimumDate) return 'Tanggal kunjungan tidak boleh kurang dari hari ini.';
            if (selectedDate.getDay() === 0 || selectedDate.getDay() === 6) return 'Tanggal kunjungan hanya tersedia pada hari Senin sampai Jumat.';
            if (publicHolidays[dateValue]) return `${publicHolidays[dateValue]} bukan hari layanan.`;
        }

        if (field === 'email') {
            if (!value.trim()) return 'Alamat email wajib diisi.';
            if (control.validity.typeMismatch) return 'Format alamat email belum benar.';

            const currentPair = `${value.trim()}\n${document.getElementById('tanggalKunjunganValue')?.value ?? ''}`;

            if (duplicateCheckResult?.key === currentPair && duplicateCheckResult.alreadyRegistered) {
                return 'Email ini sudah digunakan untuk tanggal kunjungan yang dipilih.';
            }
        }

        if (field === 'no_wa') {
            if (!value.trim()) return 'Nomor WhatsApp wajib diisi.';
            if (!/^(08|628)[0-9]{7,13}$/.test(value)) return 'Nomor harus diawali 08 atau 628 dan mengikuti format yang diminta.';
        }

        if (field === 'alamat') {
            if (!value.trim()) return 'Alamat wajib diisi.';
            if (!/^[\p{L}\p{M}\d\s.,'\/#()-]+$/u.test(value)) return 'Alamat mengandung karakter yang tidak diizinkan.';
        }

        return '';
    }

    function validateRegistrationField(field) {
        const error = getRegistrationFieldError(field);
        setRegistrationFieldError(field, error);

        return !error;
    }

    function focusRegistrationField(field) {
        const control = registrationFieldControl(field);

        if (!control) {
            return;
        }

        control.scrollIntoView({ behavior: 'smooth', block: 'center' });
        control.focus({ preventScroll: true });
    }

    function validateRegistrationForm() {
        let firstInvalidField = null;

        registrationFieldNames.forEach(field => {
            if (!validateRegistrationField(field) && firstInvalidField === null) {
                firstInvalidField = field;
            }
        });

        if (firstInvalidField) {
            focusRegistrationField(firstInvalidField);
            return false;
        }

        return true;
    }

    function applyServerValidationErrors(errors) {
        let firstInvalidField = null;

        Object.entries(errors).forEach(([field, messages]) => {
            if (!registrationFieldNames.includes(field)) return;

            const message = Array.isArray(messages) ? messages[0] : String(messages);
            setRegistrationFieldError(field, message);

            if (field === 'email' && message.toLowerCase().includes('sudah digunakan')) {
                duplicateCheckResult = {
                    key: `${document.getElementById('inputEmail').value.trim()}\n${document.getElementById('tanggalKunjunganValue').value}`,
                    alreadyRegistered: true,
                };
            }

            firstInvalidField ??= field;
        });

        if (firstInvalidField) {
            focusRegistrationField(firstInvalidField);
        }
    }

    function bindRegistrationValidation() {
        const form = document.getElementById('formPendaftaran');

        form?.addEventListener('submit', event => {
            event.preventDefault();
            bukaModalKonfirmasi();
        });

        form?.querySelectorAll('[data-validate-field]').forEach(control => {
            const field = control.dataset.validateField;

            control.addEventListener(control.tagName === 'SELECT' ? 'change' : 'input', () => {
                setRegistrationFieldError(field, '');

                if (field === 'email') scheduleDuplicateEmailCheck();
            });
        });
    }

    function setDuplicateEmailStatus(message, type = '') {
        const status = document.getElementById('emailDuplicateStatus');

        status.textContent = message;
        status.className = `email-duplicate-status${type ? ` is-${type}` : ''}`;
        status.hidden = !message;
    }

    function scheduleDuplicateEmailCheck() {
        window.clearTimeout(duplicateCheckTimer);
        duplicateCheckController?.abort();
        duplicateCheckController = null;
        if (duplicateCheckResult?.alreadyRegistered) {
            setRegistrationFieldError('email', '');
        }
        duplicateCheckResult = null;
        setDuplicateEmailStatus('');

        if (getRegistrationFieldError('email') || getRegistrationFieldError('tanggal_kunjungan')) {
            return;
        }

        duplicateCheckTimer = window.setTimeout(() => checkDuplicateEmail(), 400);
    }

    async function checkDuplicateEmail() {
        window.clearTimeout(duplicateCheckTimer);

        const email = document.getElementById('inputEmail').value.trim();
        const date = document.getElementById('tanggalKunjunganValue').value;

        if (getRegistrationFieldError('email') || getRegistrationFieldError('tanggal_kunjungan')) {
            return null;
        }

        duplicateCheckController?.abort();
        const controller = new AbortController();
        duplicateCheckController = controller;
        const requestKey = `${email}\n${date}`;

        setDuplicateEmailStatus('Memeriksa penggunaan email untuk tanggal ini...');

        try {
            const response = await fetch(duplicateEmailCheckUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': tokenCsrfSegar || document.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ email, tanggal_kunjungan: date }),
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error('Duplicate check request failed.');
            }

            const result = await response.json();
            const currentKey = `${document.getElementById('inputEmail').value.trim()}\n${document.getElementById('tanggalKunjunganValue').value}`;

            if (controller.signal.aborted || requestKey !== currentKey) {
                return null;
            }

            duplicateCheckResult = { key: requestKey, alreadyRegistered: result.already_registered };

            if (result.already_registered) {
                setRegistrationFieldError('email', 'Email ini sudah digunakan untuk tanggal kunjungan yang dipilih.');
                setDuplicateEmailStatus('');

                return true;
            }

            setDuplicateEmailStatus('Email belum terdaftar untuk tanggal kunjungan ini.', 'success');

            return false;
        } catch (error) {
            if (error.name === 'AbortError') {
                return null;
            }

            setDuplicateEmailStatus('Pemeriksaan awal tidak tersedia; server tetap memeriksa saat pendaftaran dikirim.', 'warning');

            return null;
        } finally {
            if (duplicateCheckController === controller) {
                duplicateCheckController = null;
            }
        }
    }

    function bukaModalKonfirmasi() {
        const jenisLayanan = document.getElementById('inputJenisLayanan');
        const jenisLayananLainnya = document.getElementById('inputJenisLayananLainnya');

        if (!validateRegistrationForm()) {
            return;
        }

        const btnUtama = document.getElementById('btnUtamaSubmit');
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

        // Ambil token CSRF fresh atau fallback ke input form
        const tokenTerkini = tokenCsrfSegar || document.querySelector('input[name="_token"]').value;
        formData.set('_token', tokenTerkini);

        let btnUtama = document.getElementById('btnUtamaSubmit');
        let btnKirim = document.getElementById('btnKirimFinal');

        btnUtama.disabled = true;
        btnUtama.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Sedang Memproses...';

        btnKirim.disabled = true;
        btnKirim.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Memproses...';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': tokenTerkini,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(res => {
            if (res.status === 200 && res.body.success) {
                // Munculkan Pop-up SweetAlert2 Sukses dengan Rating Bintang & Kritik Saran
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil!',
                    html: `
                        <p style="font-size: 14px; margin-bottom: 15px;">
                            ${res.body.message}
                        </p>
                        <hr style="margin: 15px 0;">
                        <div style="text-align: left;">
                            <label style="font-weight: bold; font-size: 13px; display: block; margin-bottom: 5px;">Beri Penilaian Pelayanan Kami:</label>
                            <div id="star-rating" style="font-size: 28px; color: #ffc107; cursor: pointer; margin-bottom: 12px; text-align: center;">
                                <span class="star" data-value="1" style="margin: 0 3px;">★</span>
                                <span class="star" data-value="2" style="margin: 0 3px;">★</span>
                                <span class="star" data-value="3" style="margin: 0 3px;">★</span>
                                <span class="star" data-value="4" style="margin: 0 3px;">★</span>
                                <span class="star" data-value="5" style="margin: 0 3px;">★</span>
                            </div>
                            <input type="hidden" id="selected-rating" value="5">
                            
                            <label style="font-weight: bold; font-size: 13px; display: block; margin-bottom: 5px;">Kritik & Saran:</label>
                            <textarea id="kritik-saran" placeholder="Tulis kritik atau saran untuk peningkatan layanan..." style="width: 100%; height: 75px; font-size: 13px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 5px; resize: none;"></textarea>
                        </div>
                    `,
                    confirmButtonText: 'Kirim Ulasan & Selesai',
                    confirmButtonColor: '#28a745',
                    allowOutsideClick: false,
                    didOpen: () => {
                        document.querySelectorAll('.star').forEach(star => {
                            star.addEventListener('click', function() {
                                const val = this.getAttribute('data-value');
                                document.getElementById('selected-rating').value = val;
                                document.querySelectorAll('.star').forEach(s => {
                                    s.style.color = s.getAttribute('data-value') <= val ? '#ffc107' : '#e4e5e9';
                                });
                            });
                        });
                    },
                    preConfirm: () => {
                        const rating = document.getElementById('selected-rating').value;
                        const kritikSaran = document.getElementById('kritik-saran').value;
                        
                        return fetch("<?php echo e(route('review.store')); ?>", {
                            method: "POST",
                            headers: {
                                "Content-Type": "application/json",
                                "X-CSRF-TOKEN": tokenCsrfSegar || document.querySelector('input[name="_token"]').value
                            },
                            body: JSON.stringify({
                                rating: rating,
                                kritik_saran: kritikSaran,
                                registration_id: res.body.registration_id ?? null
                            })
                        })
                        .then(resp => resp.json())
                        .catch(error => {
                            Swal.showValidationMessage(`Gagal mengirim ulasan: ${error}`);
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terima Kasih!',
                            text: 'Ulasan dan saran Anda berhasil dikirim.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                });

            } else {
                btnUtama.disabled = false;
                btnUtama.innerHTML = '<i class="fa fa-paper-plane me-2"></i> Proses Ambil Nomor Antrian';

                btnKirim.disabled = false;
                btnKirim.innerHTML = '<i class="fa fa-check me-1"></i> Ya, Data Sudah Benar & Kirim';

                if (res.body.errors) {
                    applyServerValidationErrors(res.body.errors);
                    return;
                }

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

    // --- Logika Dinamis Render Berita ---
    document.addEventListener('DOMContentLoaded', function () {
        const ticker = document.getElementById('newsTicker');
        const previousButton = document.getElementById('newsPrevious');
        const nextButton = document.getElementById('newsNext');
        let tickerItems = [];
        let activeIndex = 0;
        let newsSignature = '';
        let autoSlideTimer = null;
        const slideDuration = 5000;

        function showNews(index) {
            if (tickerItems.length === 0) return;

            tickerItems[activeIndex]?.classList.remove('is-active');
            activeIndex = (index + tickerItems.length) % tickerItems.length;
            tickerItems[activeIndex].classList.add('is-active');
        }

        function nextNews() {
            showNews(activeIndex + 1);
        }

        function prevNews() {
            showNews(activeIndex - 1);
        }

        function startAutoRotate() {
            stopAutoRotate();
            autoSlideTimer = setInterval(() => {
                if (tickerItems.length > 1) {
                    nextNews();
                }
            }, slideDuration);
        }

        function stopAutoRotate() {
            if (autoSlideTimer) {
                clearInterval(autoSlideTimer);
                autoSlideTimer = null;
            }
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

                const imageWrapper = document.createElement('div');
                imageWrapper.className = 'news-image-wrapper';

                const image = document.createElement('img');
                image.src = item.image_url || 'https://placehold.co/144x104/e2e8f0/475569?text=BPS';
                image.alt = 'Thumbnail berita';
                image.className = 'news-ticker-image';
                image.loading = 'lazy';
                image.onerror = () => { image.src = 'https://placehold.co/144x104/e2e8f0/475569?text=BPS'; };

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
            fetch("<?php echo e(route('api.berita')); ?>")
                .then(response => response.json())
                .then(items => renderNews(items))
                .catch(error => console.error('Gagal memperbarui berita:', error));
        }

        previousButton?.addEventListener('click', function () {
            prevNews();
            startAutoRotate(); 
        });

        nextButton?.addEventListener('click', function () {
            nextNews();
            startAutoRotate(); 
        });

        if (ticker) {
            ticker.addEventListener('mouseenter', stopAutoRotate);
            ticker.addEventListener('mouseleave', startAutoRotate);
        }

        renderNews(Array.from(document.querySelectorAll('.news-ticker-item')).map(item => ({
            url: item.href,
            title: item.querySelector('span')?.textContent || '',
            image_url: item.querySelector('img')?.src || '',
        })));

        startAutoRotate();
        setInterval(refreshNews, 10000);
    });
</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Kuliah\Magang\SANTIKA\resources\views/pages/pendaftaran.blade.php ENDPATH**/ ?>