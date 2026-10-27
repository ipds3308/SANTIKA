  
  
<?php $__env->startSection('title', 'Ruang Tunggu - SANTIKA BPS'); ?>  
  
<?php $__env->startSection('content'); ?>  
  
<!-- CSS Kustom untuk Perbaikan Line Break Nomor Antrian & Responsivitas Mobile -->  
<style>  
 /* Styling Switch Uiverse */  
 .toggleSwitch {  
 width: 45px;  
 height: 45px;  
 position: relative;  
 display: flex;  
 align-items: center;  
 justify-content: center;  
 background-color: rgb(39, 39, 39);  
 border-radius: 50%;  
 cursor: pointer;  
 transition-duration: 0.3s;  
 box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.13);  
 overflow: hidden;  
 }  
  
 #checkboxInput {  
 display: none;  
 }  
  
 .speaker, .mute-speaker {  
 position: absolute;  
 width: 100%;  
 height: 100%;  
 display: flex;  
 align-items: center;  
 justify-content: center;  
 transition-duration: 0.3s;  
 }  
  
 .speaker {  
 z-index: 2;  
 }  
  
 .mute-speaker {  
 opacity: 0;  
 z-index: 3;  
 }  
  
 .speaker svg, .mute-speaker svg {  
 width: 18px;  
 }  
  
 #checkboxInput:checked + .toggleSwitch .speaker {  
 opacity: 0;  
 transition-duration: 0.3s;  
 }  
  
 #checkboxInput:checked + .toggleSwitch .mute-speaker {  
 opacity: 1;  
 transition-duration: 0.3s;  
 background-color: red;  
 }  
  
 #checkboxInput:active + .toggleSwitch {  
 transform: scale(0.7);  
 }  
  
 #checkboxInput:hover + .toggleSwitch {  
 background-color: rgb(61, 61, 61);  
 }  
  
 #card-panggilan.fullscreen-active {  
 width: 100vw !important;  
 height: 100vh !important;  
 min-height: 0 !important;  
 padding: 2rem !important;  
 display: flex !important;  
 flex-direction: row !important;  
 align-items: stretch !important;  
 gap: 1.5rem;  
 overflow: hidden;  
 border-radius: 0 !important;  
 background: #002b6a !important;  
 }  
  
 #card-panggilan.fullscreen-active .layout-normal {  
 display: none;  
 }  
  
 #card-panggilan:not(.fullscreen-active) .layout-fullscreen {  
 display: none;  
 }  
  
 #card-panggilan.fullscreen-active .layout-fullscreen {  
 display: flex;  
 width: 100%;  
 height: 100%;  
 gap: 1.5rem;  
 }  
  
 #card-panggilan.fullscreen-active .fullscreen-main {  
 width: 66.666667%;  
 display: flex;  
 flex-direction: column;  
 justify-content: center;  
 align-items: center;  
 }  

 #card-panggilan.fullscreen-active .fullscreen-queue {  
 width: 33.333333%;  
 min-width: 0;  
 color: #1e293b;  
 background: #fff;  
 border-radius: 0.75rem;  
 padding: 1.5rem;  
 display: flex;  
 flex-direction: column;  
 height: 100%;  
 box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);  
 }  
  
 /* Perbaikan Nomor Aktif */  
 #nomor-aktif {  
 font-size: clamp(3rem, 6.5vw, 5.2rem);  
 font-weight: 800;  
 letter-spacing: 1px;  
 line-height: 1.15;  
 word-break: normal;  
 overflow-wrap: break-word;  
 hyphens: auto;  
 display: inline-block;  
 max-width: 100%;  
 }  

 #card-panggilan.fullscreen-active #nomor-aktif-fullscreen {  
 font-size: clamp(3.5rem, 8vw, 7.5rem);  
 font-weight: 900;  
 line-height: 1.1;  
 letter-spacing: 2px;  
 text-align: center;  
 width: 100%;  
 display: block;  
 word-break: normal;  
 overflow-wrap: break-word;  
 }  
  
 #card-panggilan.fullscreen-active .fullscreen-queue .table-responsive {  
 overflow: auto;  
 }  
  
 #card-panggilan.fullscreen-active .fullscreen-selesai {  
 width: 33.333333%;  
 min-width: 0;  
 height: 100%;  
 padding: 1rem;  
 border-radius: 0.75rem;  
 background: #f8fafc;  
 color: #1e293b;  
 box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.2);  
 display: flex;  
 flex-direction: column;  
 }  
  
 #card-panggilan.fullscreen-active .fullscreen-selesai-grid {  
 display: grid;  
 grid-template-columns: repeat(2, minmax(0, 1fr));  
 gap: 0.75rem;  
 flex: 1;  
 }  
  
 /* Fix Bug #1: Menggunakan Container Query (cqw) untuk card fullscreen agar proporsional terhadap lebar card itu sendiri */  
 #card-panggilan.fullscreen-active .fullscreen-selesai-card {  
 container-type: inline-size;  
 min-width: 0;  
 padding: 0.75rem 0.5rem;  
 border: 1px solid #e2e8f0;  
 border-top: 4px solid var(--accent-color);  
 border-radius: 0.6rem;  
 background: #fff;  
 color: #1e293b;  
 display: flex;  
 flex-direction: column;  
 justify-content: space-between;  
 text-align: center;  
 }  
  
 #card-panggilan.fullscreen-active .fullscreen-selesai-title {  
 min-height: 3.5rem;  
 display: flex;  
 flex-direction: column;  
 align-items: center;  
 justify-content: center;  
 gap: 0.35rem;  
 font-size: clamp(0.95rem, 1.2vw, 1.35rem);  
 font-weight: 700;  
 color: #0f172a;  
 line-height: 1.25;  
 text-align: center;  
 }  
  
 #card-panggilan.fullscreen-active .fullscreen-selesai-title > span:first-child {  
 width: 100%;  
 text-align: center;  
 }  
  
 #card-panggilan.fullscreen-active .fullscreen-selesai-title .badge {  
 font-size: 0.85rem;  
 padding: 0.25rem 0.5rem;  
 }  
  
 #card-panggilan.fullscreen-active .fullscreen-selesai-number {  
 margin: auto 0;  
 padding: 0.4rem 0.25rem;  
 font-size: clamp(1rem, 12cqw, 1.9rem);  
 font-weight: 800;  
 line-height: 1.1;  
 word-break: normal;  
 text-align: center;  
 }  
  
 #card-panggilan.fullscreen-active .fullscreen-selesai-status {  
 color: #020617;  
 font-size: 0.95rem;  
 font-weight: 700;  
 line-height: 1.2;  
 padding-top: 0.35rem;  
 }  
  
 #selesai-section.fullscreen-hidden {  
 display: none;  
 }  
  
 .selesai-grid {  
 display: grid;  
 grid-template-columns: repeat(2, minmax(0, 1fr));  
 gap: 1rem;  
 margin-top: 1.5rem;  
 margin-bottom: 1.5rem;  
 align-items: stretch;  
 }  
  
 /* Fix Bug #1 (Versi Non-Fullscreen): Container query untuk rekap selesai */  
 .selesai-card {  
 container-type: inline-size;  
 padding: 1rem;  
 border-radius: 0.75rem;  
 border: 1px solid #e2e8f0;  
 border-top-width: 4px;  
 box-shadow: 0 0.25rem 0.75rem rgba(15, 23, 42, 0.08);  
 min-height: 118px;  
 height: 100%;  
 display: flex;  
 flex-direction: column;  
 justify-content: space-between;  
 }  
  
 .selesai-title {  
 min-height: 48px;  
 display: flex;  
 flex-direction: column;  
 align-items: center;  
 justify-content: center;  
 gap: 0.25rem;  
 line-height: 1.25;  
 text-align: center;  
 font-weight: 700;  
 color: #0f172a;  
 }  
  
 .selesai-title > span:first-child {  
 width: 100%;  
 text-align: center;  
 }  
  
 .selesai-number {  
 margin: auto 0;  
 padding: 0.5rem 0.25rem;  
 color: #1e293b;  
 font-size: clamp(0.8rem, 11cqw, 1.3rem);  
 font-weight: 800;  
 line-height: 1.2;  
 word-break: normal;  
 text-align: center;  
 }  
  
 .selesai-status {  
 color: #64748b;  
 font-size: 0.8rem;  
 font-weight: 600;  
 text-align: center;  
 }  
  
 .selesai-card.border-primary { border-top-color: #0d6efd; }  
 .selesai-card.border-success { border-top-color: #198754; }  
 .selesai-card.border-info { border-top-color: #0dcaf0; }  
 .selesai-card.border-warning { border-top-color: #ffc107; }  
 .selesai-card.border-danger { border-top-color: #dc3545; }  
 .selesai-card.border-secondary { border-top-color: #6c757d; }  
  
 @media (min-width: 768px) {  
 .selesai-grid {  
 grid-template-columns: repeat(3, minmax(0, 1fr));  
 }  
 }  
  
 @media (min-width: 992px) {  
 .selesai-grid {  
 grid-template-columns: repeat(6, minmax(0, 1fr));  
 }  
 }  

 /* Fix Bug #2 & Overflow Layout di HP / layar kecil (max-width: 768px) */  
 @media (max-width: 768px) {  
     #card-panggilan.fullscreen-active {  
         flex-direction: column !important;  
         padding: 1rem !important;  
         gap: 1rem;  
         overflow-y: auto !important;  
     }  
     #card-panggilan.fullscreen-active .layout-fullscreen {  
         flex-direction: column;  
         height: auto;  
     }  
     #card-panggilan.fullscreen-active .fullscreen-main {  
         width: 100%;  
         flex-shrink: 0;  
     }  
     #card-panggilan.fullscreen-active .fullscreen-queue,  
     #card-panggilan.fullscreen-active .fullscreen-selesai {  
         width: 100%;  
         height: auto;  
         max-height: 55vh;  
         overflow-y: auto;  
         -webkit-overflow-scrolling: touch;  
     }  
     #card-panggilan.fullscreen-active .fullscreen-selesai-grid {  
         flex: none;  
         grid-template-columns: repeat(2, minmax(0, 1fr));  
         gap: 0.5rem;  
     }  
     #card-panggilan.fullscreen-active #nomor-aktif-fullscreen {  
         font-size: clamp(2rem, 12vw, 3.5rem);  
     }  
 }  
</style>  

<style>
 .waiting-date-picker {
 position: relative;
 }

 .waiting-date-picker-controls {
 display: flex;
 gap: 0.5rem;
 }

 .waiting-date-picker-display {
 width: 220px;
 cursor: pointer;
 }

 .waiting-date-calendar {
 position: absolute;
 top: calc(100% + 6px);
 right: 0;
 z-index: 1090;
 width: min(calc(100vw - 2rem), 360px);
 padding: 14px;
 border: 1px solid #cbd5e1;
 border-radius: 12px;
 background: #fff;
 box-shadow: 0 12px 30px rgba(15, 23, 42, 0.18);
 color: #1e293b;
 }

 .waiting-date-calendar[hidden] {
 display: none;
 }

 .waiting-date-calendar-header,
 .waiting-date-calendar-grid {
 display: grid;
 grid-template-columns: repeat(7, minmax(0, 1fr));
 gap: 4px;
 }

 .waiting-date-calendar-header {
 align-items: center;
 margin-bottom: 6px;
 }

 .waiting-date-calendar-header button {
 border: 0;
 border-radius: 6px;
 padding: 6px 2px;
 color: #334155;
 background: transparent;
 font-weight: 700;
 }

 .waiting-date-calendar-header button:hover:not(:disabled) {
 background: #e0f2fe;
 }

 .waiting-date-calendar-header button:disabled {
 opacity: 0.4;
 cursor: not-allowed;
 }

 .waiting-date-calendar-weekday,
 .waiting-date-calendar-day {
 display: flex;
 min-height: 38px;
 align-items: center;
 justify-content: center;
 border-radius: 8px;
 font-size: 0.84rem;
 }

 .waiting-date-calendar-weekday {
 color: #64748b;
 font-weight: 700;
 }

 .waiting-date-calendar-day {
 border: 1px solid transparent;
 color: #1e293b;
 background: transparent;
 }

 .waiting-date-calendar-day:hover:not(:disabled) {
 border-color: #0284c7;
 background: #e0f2fe;
 }

 .waiting-date-calendar-day.is-selected {
 color: #fff;
 background: #0284c7;
 }

 .waiting-date-calendar-day:disabled {
 color: #94a3b8;
 background: #f1f5f9;
 cursor: not-allowed;
 opacity: 0.55;
 }

 .waiting-date-calendar-day.is-holiday:disabled {
 color: #b91c1c;
 background: #fee2e2;
 text-decoration: line-through;
 }

 @media (max-width: 576px) {
 .waiting-date-picker-display {
 width: min(52vw, 220px);
 }
 }
</style>
  
<div class="container-fluid py-4">  
   
<!-- Baris Kontrol Atas (Switch Audio & Tombol Fullscreen Global) -->  
 <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-4 shadow-sm">  
 <div class="d-flex align-items-center gap-3">  
 <!-- Switch Uiverse untuk Suara -->  
 <div>  
 <input id="checkboxInput" type="checkbox" />  
 <label class="toggleSwitch" for="checkboxInput" title="Aktifkan/Matikan Suara Bel">  
 <div class="speaker">  
 <svg viewBox="0 0 75 75" version="1.0" xmlns="http://www.w3.org/2000/svg">  
 <path style="stroke:#fff;stroke-width:5;stroke-linejoin:round;fill:#fff;" d="M39.389,13.769 L22.235,28.606 L6,28.606 L6,47.699 L21.989,47.699 L39.389,62.75 L39.389,13.769z"></path>  
 <path style="fill:none;stroke:#fff;stroke-width:5;stroke-linecap:round" d="M48,27.6a19.5,19.5 0 0 1 0,21.4M55.1,20.5a30,30 0 0 1 0,35.6M61.6,14a38.8,38.8 0 0 1 0,48.6"></path>  
 </svg>  
 </div>  
 <div class="mute-speaker">  
 <svg stroke-width="5" stroke="#fff" viewBox="0 0 75 75" version="1.0">  
 <path stroke-linejoin="round" fill="#fff" d="m39,14-17,15H6V48H22l17,15z"></path>  
 <path stroke-linecap="round" fill="#fff" d="m49,26 20,24m0-24-20,24"></path>  
 </svg>  
 </div>  
 </label>  
 </div>  
 <div>  
 <h6 class="fw-bold m-0 text-dark">Kontrol Suara Bel</h6>  
 <small class="text-muted" id="status-audio-label">Suara Aktif (Klik switch untuk mute)</small>  
 </div>  
 </div>  
  
 <div class="d-flex flex-wrap align-items-center gap-2">
 <div class="waiting-date-picker" id="waitingDatePicker">
 <div class="waiting-date-picker-controls">
 <input type="text" id="waitingDateDisplay" class="form-control" placeholder="Pilih tanggal" aria-haspopup="dialog" aria-expanded="false" aria-controls="waitingDateCalendar" autocomplete="off" readonly>
 <input type="hidden" id="waitingDateValue" value="<?php echo e($selectedDate); ?>">
 <button type="button" class="btn btn-outline-secondary" id="waitingDateToggle" aria-label="Buka kalender tanggal ruang tunggu" aria-controls="waitingDateCalendar" aria-expanded="false">
 <i class="fa fa-calendar-days" aria-hidden="true"></i>
 </button>
 </div>
 <div class="waiting-date-calendar" id="waitingDateCalendar" role="dialog" aria-label="Pilih tanggal ruang tunggu" hidden>
 <div class="waiting-date-calendar-header">
 <button type="button" id="waitingPreviousMonth" aria-label="Bulan sebelumnya"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
 <strong class="text-center" id="waitingMonthLabel" style="grid-column: span 5;" aria-live="polite"></strong>
 <button type="button" id="waitingNextMonth" aria-label="Bulan berikutnya"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
 </div>
 <div class="waiting-date-calendar-grid mb-1" aria-hidden="true">
 <span class="waiting-date-calendar-weekday">Sen</span>
 <span class="waiting-date-calendar-weekday">Sel</span>
 <span class="waiting-date-calendar-weekday">Rab</span>
 <span class="waiting-date-calendar-weekday">Kam</span>
 <span class="waiting-date-calendar-weekday">Jum</span>
 <span class="waiting-date-calendar-weekday">Sab</span>
 <span class="waiting-date-calendar-weekday">Min</span>
 </div>
 <div class="waiting-date-calendar-grid" id="waitingCalendarDays" role="grid" aria-label="Tanggal dalam bulan"></div>
 </div>
 </div>
 <button onclick="toggleFullscreen()" class="btn btn-primary fw-bold px-4 py-2" style="background-color: #002060; border-color: #002060;">  
 <i class="fa fa-expand me-2"></i> Layar Penuh (Fullscreen)  
 </button>  
 </div>  
 </div>  

 <?php if (! ($holidayCalendarAvailable)): ?>
 <div class="alert alert-warning py-2" role="status">Kalender hari libur nasional tidak dapat dimuat saat ini. Akhir pekan tetap tidak tersedia.</div>
 <?php endif; ?>
 <div id="waitingRoomDataError" class="alert alert-danger py-2" role="alert" hidden></div>
  
 <div class="row g-4">  
 <!-- Kotak Utama: Nomor Antrian yang Sedang Dipanggil -->  
 <div class="col-lg-7">  
 <div id="card-panggilan" class="card border-0 shadow-lg text-white text-center p-4 p-md-5 rounded-4 position-relative" style="background: linear-gradient(135deg, #002060, #004080); min-height: 400px; display: flex; flex-direction: column; justify-content: center;">  
   
 <!-- Tombol Fullscreen Khusus di dalam Card -->  
 <button onclick="toggleFullscreen()" class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-3" title="Fullscreen Kartu Ini">  
 <i class="fa fa-expand"></i>  
 </button>  
  
 <div class="layout-normal w-100">  
 <h4 class="text-uppercase tracking-wider text-warning fw-bold mb-3"><i class="fa fa-bullhorn me-2"></i> Silakan Menuju Loket Pelayanan</h4>  
 <div class="w-100 px-2">  
     <h1 id="nomor-aktif" class="mb-3 text-white">---</h1>  
 </div>  
 <h3 id="nama-aktif" class="fw-light mb-2 text-light px-3">Memuat Data Antrian...</h3>  
 <span id="status-aktif" class="badge bg-warning text-dark px-3 py-2 fs-6 mx-auto mt-2">Standby</span>  
 </div>  
  
 <div class="layout-fullscreen">  
 <div class="fullscreen-main w-2/3 d-flex flex-column align-items-center justify-content-center">  
 <h4 class="text-uppercase tracking-wider text-warning fw-bold mb-3"><i class="fa fa-bullhorn me-2"></i> Silakan Menuju Loket Pelayanan</h4>  
 <div class="w-100 px-3">  
     <h1 id="nomor-aktif-fullscreen" class="fw-bold mb-3 text-white">---</h1>  
 </div>  
 <h3 id="nama-aktif-fullscreen" class="fw-light mb-2 text-light px-3">Memuat Data Antrian...</h3>  
 <span id="status-aktif-fullscreen" class="badge bg-warning text-dark px-3 py-2 fs-6 mx-auto mt-2">Standby</span>  
 </div>  
 <div class="fullscreen-selesai">  
 <h5 class="fw-bold mb-3" style="color: #002060;"><i class="fa fa-check-circle me-2 text-success"></i>Pelayanan Selesai</h5>  
 <div class="fullscreen-selesai-grid">  
 <div class="fullscreen-selesai-card" style="--accent-color: #2563eb;"><div class="fullscreen-selesai-title"><span class="badge bg-primary mb-1">A</span><span>Konsultasi Statistik</span></div><div id="fullscreen-selesai-A" class="fullscreen-selesai-number text-primary">—</div><small class="fullscreen-selesai-status">Selesai</small></div>  
 <div class="fullscreen-selesai-card" style="--accent-color: #059669;"><div class="fullscreen-selesai-title"><span class="badge bg-success mb-1">B</span><span>Konsultasi DTSEN</span></div><div id="fullscreen-selesai-B" class="fullscreen-selesai-number text-success">—</div><small class="fullscreen-selesai-status">Selesai</small></div>  
 <div class="fullscreen-selesai-card" style="--accent-color: #06b6d4;"><div class="fullscreen-selesai-title"><span class="badge bg-info text-dark mb-1">C</span><span>Permintaan Data</span></div><div id="fullscreen-selesai-C" class="fullscreen-selesai-number text-info">—</div><small class="fullscreen-selesai-status">Selesai</small></div>  
 <div class="fullscreen-selesai-card" style="--accent-color: #d97706;"><div class="fullscreen-selesai-title"><span class="badge bg-warning text-dark mb-1">D</span><span>Rekomendasi Survey</span></div><div id="fullscreen-selesai-D" class="fullscreen-selesai-number text-warning">—</div><small class="fullscreen-selesai-status">Selesai</small></div>  
 <div class="fullscreen-selesai-card" style="--accent-color: #dc2626;"><div class="fullscreen-selesai-title"><span class="badge bg-danger mb-1">E</span><span>Pengaduan / Lainnya</span></div><div id="fullscreen-selesai-E" class="fullscreen-selesai-number text-danger">—</div><small class="fullscreen-selesai-status">Selesai</small></div>  
 <div class="fullscreen-selesai-card" style="--accent-color: #475569;"><div class="fullscreen-selesai-title"><span class="badge bg-secondary mb-1">F</span><span>Perpustakaan</span></div><div id="fullscreen-selesai-F" class="fullscreen-selesai-number text-secondary">—</div><small class="fullscreen-selesai-status">Selesai</small></div>  
 </div>  
 </div>  
 </div>  
 </div>  
 </div>  
   
 <!-- Kolom Samping / Bawah untuk Tabel Antrian Menunggu -->  
 <div class="col-lg-5">  
 <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">  
 <h5 class="fw-bold mb-3 text-dark"><i class="fa fa-users me-2 text-primary"></i> Daftar Antrian Menunggu</h5>  
 <div class="table-responsive" style="max-height: 450px; overflow-y: auto;">  
 <table class="table table-hover align-middle">  
 <thead class="table-light sticky-top">  
 <tr>  
 <th>Nomor</th>  
 <th>Nama</th>  
 <th>Status</th>  
 </tr>  
 </thead>  
 <tbody id="list-menunggu">  
 <tr>  
 <td colspan="3" class="text-center text-muted py-3">Memuat data antrian...</td>  
 </tr>  
 </tbody>  
 </table>  
 </div>  
 </div>  
 </div>  
 </div>  
   
 <!-- Bagian Rekap Selesai (Tampil saat Normal) -->  
 <div id="selesai-section" class="mt-4">  
 <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">  
 <h5 class="fw-bold mb-3 text-dark"><i class="fa fa-check-circle me-2 text-success"></i> Rekapitulasi Pelayanan Selesai</h5>  
 <div class="selesai-grid">  
 <div class="selesai-card border-primary"><div class="selesai-title"><span class="badge bg-primary mb-1">A</span><span>Konsultasi Statistik</span></div><div id="selesai-A" class="selesai-number text-primary">—</div><div class="selesai-status">Status: Selesai</div></div>  
 <div class="selesai-card border-success"><div class="selesai-title"><span class="badge bg-success mb-1">B</span><span>Konsultasi DTSEN</span></div><div id="selesai-B" class="selesai-number text-success">—</div><div class="selesai-status">Status: Selesai</div></div>  
 <div class="selesai-card border-info"><div class="selesai-title"><span class="badge bg-info text-dark mb-1">C</span><span>Permintaan Data</span></div><div id="selesai-C" class="selesai-number text-info">—</div><div class="selesai-status">Status: Selesai</div></div>  
 <div class="selesai-card border-warning"><div class="selesai-title"><span class="badge bg-warning text-dark mb-1">D</span><span>Rekomendasi Survey</span></div><div id="selesai-D" class="selesai-number text-warning">—</div><div class="selesai-status">Status: Selesai</div></div>  
 <div class="selesai-card border-danger"><div class="selesai-title"><span class="badge bg-danger mb-1">E</span><span>Pengaduan / Lainnya</span></div><div id="selesai-E" class="selesai-number text-danger">—</div><div class="selesai-status">Status: Selesai</div></div>  
 <div class="selesai-card border-secondary"><div class="selesai-title"><span class="badge bg-secondary mb-1">F</span><span>Perpustakaan</span></div><div id="selesai-F" class="selesai-number text-secondary">—</div><div class="selesai-status">Status: Selesai</div></div>  
 </div>  
 </div>  
 </div>  
  
</div>  

<script>  
 let nomorTerakhirDipanggil = null;  
 let cardPanggilan = document.getElementById('card-panggilan');  
 let isFullscreen = false;  
 let waitingRoomRequestController = null;
 const waitingRoomHolidays = <?php echo \Illuminate\Support\Js::from($publicHolidays)->toHtml() ?>;
 const waitingRoomSelectedDate = <?php echo \Illuminate\Support\Js::from($selectedDate)->toHtml() ?>;
 const waitingRoomApiUrl = <?php echo \Illuminate\Support\Js::from(route('api.ruang.tunggu'))->toHtml() ?>;
  
 const audioCtxClass = window.AudioContext || window.webkitAudioContext;  
 let audioCtx = null;  
  
 function initAudio() {  
 if (!audioCtx) {  
 audioCtx = new audioCtxClass();  
 }  
 if (audioCtx.state === 'suspended') {  
 audioCtx.resume();  
 }  
 }  
  
 document.getElementById('checkboxInput').addEventListener('change', function () {  
 const labelStatus = document.getElementById('status-audio-label');  
 if (this.checked) {  
 labelStatus.innerText = "Suara Dimatikan (Mute)";  
 labelStatus.classList.replace('text-muted', 'text-danger');  
 } else {  
 labelStatus.innerText = "Suara Aktif (Klik switch untuk mute)";  
 labelStatus.classList.replace('text-danger', 'text-muted');  
 initAudio();  
 }  
 });  
  
 function playChime() {  
 const isMuted = document.getElementById('checkboxInput').checked;  
 if (isMuted) return;  
  
 initAudio();  
 if (!audioCtx) return;  
  
 const notes = [523.25, 659.25, 783.99]; // C5, E5, G5  
 let delay = 0;  
  
 notes.forEach((freq, index) => {  
 setTimeout(() => {  
 if (audioCtx.state === 'suspended') {  
 audioCtx.resume();  
 }  
  
 const osc = audioCtx.createOscillator();  
 const gain = audioCtx.createGain();  
  
 osc.type = 'sine';  
 osc.frequency.setValueAtTime(freq, audioCtx.currentTime);  
  
 gain.gain.setValueAtTime(0.4, audioCtx.currentTime);  
 gain.gain.exponentialRampToValueAtTime(0.0001, audioCtx.currentTime + 0.5);  
  
 osc.connect(gain);  
 gain.connect(audioCtx.destination);  
  
 osc.start();  
 osc.stop(audioCtx.currentTime + 0.5);  
 }, delay);  
 delay += 130;  
 });  
 }  
  
 function toggleFullscreen() {  
 if (!document.fullscreenElement) {  
 if (cardPanggilan.requestFullscreen) {  
 cardPanggilan.requestFullscreen();  
 } else if (cardPanggilan.webkitRequestFullscreen) {  
 cardPanggilan.webkitRequestFullscreen();  
 } else if (cardPanggilan.msRequestFullscreen) {  
 cardPanggilan.msRequestFullscreen();  
 }  
 } else {  
 if (document.exitFullscreen) {  
 document.exitFullscreen();  
 } else if (document.webkitExitFullscreen) {  
 document.webkitExitFullscreen();  
 } else if (document.msExitFullscreen) {  
 document.msExitFullscreen();  
 }  
 }  
 }  
  
 function updateFullscreenState() {  
 isFullscreen = (document.fullscreenElement || document.webkitFullscreenElement) === cardPanggilan;  
 cardPanggilan.classList.toggle('fullscreen-active', isFullscreen);  
 cardPanggilan.classList.toggle('w-screen', isFullscreen);  
 cardPanggilan.classList.toggle('h-screen', isFullscreen);  
 cardPanggilan.classList.toggle('p-8', isFullscreen);  
 cardPanggilan.classList.toggle('flex', isFullscreen);  
 cardPanggilan.classList.toggle('flex-row', isFullscreen);  
 cardPanggilan.classList.toggle('gap-6', isFullscreen);  
 cardPanggilan.classList.toggle('bg-[#002b6a]', isFullscreen);  
 document.getElementById('selesai-section').classList.toggle('fullscreen-hidden', isFullscreen);  
 document.documentElement.style.overflow = isFullscreen ? 'hidden' : '';  
 document.body.style.overflow = isFullscreen ? 'hidden' : '';  
 }  
  
 document.addEventListener('fullscreenchange', updateFullscreenState);  
 document.addEventListener('webkitfullscreenchange', updateFullscreenState);  

 function formatWaitingRoomDate(value) {
 const date = new Date(`${value}T00:00:00`);
 return new Intl.DateTimeFormat('id-ID', {
 weekday: 'long',
 day: 'numeric',
 month: 'long',
 year: 'numeric',
 }).format(date);
 }

 function initWaitingRoomDatePicker() {
 const picker = document.getElementById('waitingDatePicker');
 const display = document.getElementById('waitingDateDisplay');
 const value = document.getElementById('waitingDateValue');
 const toggle = document.getElementById('waitingDateToggle');
 const calendar = document.getElementById('waitingDateCalendar');
 const monthLabel = document.getElementById('waitingMonthLabel');
 const days = document.getElementById('waitingCalendarDays');
 const previousMonth = document.getElementById('waitingPreviousMonth');
 const nextMonth = document.getElementById('waitingNextMonth');
 const monthFormatter = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' });
 const dateFormatter = new Intl.DateTimeFormat('id-ID', {
 weekday: 'long',
 day: 'numeric',
 month: 'long',
 year: 'numeric',
 });
 let visibleMonth = new Date(`${value.value}T00:00:00`);
 visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);

 function updateDisplay() {
 display.value = value.value ? formatWaitingRoomDate(value.value) : '';
 }

 function renderCalendar() {
 const firstDay = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth(), 1);
 const firstGridDate = new Date(firstDay);
 firstGridDate.setDate(firstGridDate.getDate() - ((firstDay.getDay() + 6) % 7));
 monthLabel.textContent = monthFormatter.format(visibleMonth);
 days.replaceChildren();

 for (let index = 0; index < 42; index++) {
 const date = new Date(firstGridDate);
 date.setDate(firstGridDate.getDate() + index);
 const dateValue = [
 date.getFullYear(),
 String(date.getMonth() + 1).padStart(2, '0'),
 String(date.getDate()).padStart(2, '0'),
 ].join('-');
 const holidayName = waitingRoomHolidays[dateValue];
 const isWeekend = date.getDay() === 0 || date.getDay() === 6;
 const isOutsideMonth = date.getMonth() !== visibleMonth.getMonth();
 const dayButton = document.createElement('button');

 dayButton.type = 'button';
 dayButton.className = 'waiting-date-calendar-day';
 dayButton.textContent = String(date.getDate());
 dayButton.dataset.date = dateValue;
 dayButton.setAttribute('role', 'gridcell');
 dayButton.setAttribute('aria-label', dateFormatter.format(date));
 dayButton.disabled = isWeekend || Boolean(holidayName) || isOutsideMonth;
 dayButton.setAttribute('aria-pressed', dateValue === value.value ? 'true' : 'false');

 if (dateValue === value.value) {
 dayButton.classList.add('is-selected');
 }

 if (holidayName) {
 dayButton.classList.add('is-holiday');
 dayButton.title = `${holidayName} - hari libur nasional`;
 } else if (isWeekend) {
 dayButton.title = 'Akhir pekan - tanggal tidak tersedia';
 }

 days.appendChild(dayButton);
 }
 }

 function setCalendarOpen(isOpen) {
 calendar.hidden = !isOpen;
 display.setAttribute('aria-expanded', String(isOpen));
 toggle.setAttribute('aria-expanded', String(isOpen));
 }

 function openCalendar() {
 const selectedDate = new Date(`${value.value}T00:00:00`);
 visibleMonth = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), 1);
 renderCalendar();
 setCalendarOpen(true);
 }

 updateDisplay();
 renderCalendar();
 display.addEventListener('click', openCalendar);
 toggle.addEventListener('click', function () {
 setCalendarOpen(calendar.hidden);
 if (!calendar.hidden) {
 openCalendar();
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
 days.addEventListener('click', function (event) {
 const dayButton = event.target.closest('button[data-date]');
 if (!dayButton || dayButton.disabled) {
 return;
 }

 value.value = dayButton.dataset.date;
 updateDisplay();
 renderCalendar();
 setCalendarOpen(false);
 nomorTerakhirDipanggil = null;
 ambilDataRuangTunggu();
 });
 document.addEventListener('click', function (event) {
 if (!calendar.hidden && !picker.contains(event.target)) {
 setCalendarOpen(false);
 }
 });
 }

 initWaitingRoomDatePicker();
  
 function ambilDataRuangTunggu() {  
 const url = new URL(waitingRoomApiUrl, window.location.origin);
 url.searchParams.set('tanggal', document.getElementById('waitingDateValue').value);
 const dataError = document.getElementById('waitingRoomDataError');
 waitingRoomRequestController?.abort();
 waitingRoomRequestController = new AbortController();

 fetch(url, { signal: waitingRoomRequestController.signal })  
 .then(async response => {
 const data = await response.json();
 if (!response.ok) {
 throw new Error(data.message || 'Gagal memuat data ruang tunggu.');
 }
 return data;
 })
 .then(data => {  
 dataError.hidden = true;
 dataError.textContent = '';
 let boxNomor = document.getElementById('nomor-aktif');  
 let boxNama = document.getElementById('nama-aktif');  
 let boxStatus = document.getElementById('status-aktif');  
 let boxNomorFullscreen = document.getElementById('nomor-aktif-fullscreen');  
 let boxNamaFullscreen = document.getElementById('nama-aktif-fullscreen');  
 let boxStatusFullscreen = document.getElementById('status-aktif-fullscreen');  
  
 if (data.dipanggil) {  
 const nomorAntrian = data.dipanggil.nomor_antrian;  
 const namaPemohon = data.dipanggil.nama;  
 const statusAntrian = "Sedang Dipanggil";  
  
 boxNomor.innerText = nomorAntrian;  
 boxNama.innerText = namaPemohon;  
 boxStatus.innerText = statusAntrian;  
 boxNomorFullscreen.innerText = nomorAntrian;  
 boxNamaFullscreen.innerText = namaPemohon;  
 boxStatusFullscreen.innerText = statusAntrian;  
  
 if (document.getElementById('waitingDateValue').value === waitingRoomSelectedDate && nomorTerakhirDipanggil !== data.dipanggil.id) {  
 nomorTerakhirDipanggil = data.dipanggil.id;  
 playChime();  
 } else {
 nomorTerakhirDipanggil = data.dipanggil.id;
 }  
 } else {  
 boxNomor.innerText = "---";  
 boxNama.innerText = "Menunggu panggilan petugas...";  
 boxStatus.innerText = "Standby";  
 boxNomorFullscreen.innerText = "---";  
 boxNamaFullscreen.innerText = "Menunggu panggilan petugas...";  
 boxStatusFullscreen.innerText = "Standby";  
 }  
  
 const listContainers = [  
 document.getElementById('list-menunggu'),  
 ];  
  
 ['A', 'B', 'C', 'D', 'E', 'F'].forEach(prefix => {  
 const nomorSelesai = data.selesai?.[prefix] || '—';  
 document.getElementById(`selesai-${prefix}`).innerText = nomorSelesai;  
 document.getElementById(`fullscreen-selesai-${prefix}`).innerText = nomorSelesai;  
 });  
  
 if (data.menunggu && data.menunggu.length > 0) {  
 const rows = data.menunggu.map(item => `<tr>  
 <td class="fw-bold text-primary">${item.nomor_antrian}</td>  
 <td>${item.nama}</td>  
 <td><span class="badge bg-secondary">${item.status}</span></td>  
 </tr>`).join('');  
  
 listContainers.forEach(listContainer => {  
 listContainer.innerHTML = rows;  
 });  
 } else {  
 listContainers.forEach(listContainer => {  
 listContainer.innerHTML = `<tr><td colspan="3" class="text-center text-muted py-3">Tidak ada antrian menunggu.</td></tr>`;  
 });  
 }  
 })  
 .catch(error => {
 if (error.name === 'AbortError') {
 return;
 }
 dataError.textContent = error.message || 'Gagal memuat data ruang tunggu.';
 dataError.hidden = false;
 console.error('Gagal memuat data ruang tunggu:', error);
 });  
 }  
  
 ambilDataRuangTunggu();
 setInterval(ambilDataRuangTunggu, 3000);  
</script>  
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.main', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Kuliah\Magang\SANTIKA\resources\views/pages/ruang_tunggu.blade.php ENDPATH**/ ?>