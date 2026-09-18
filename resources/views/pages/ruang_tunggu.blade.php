@extends('layouts.main')

@section('title', 'Ruang Tunggu - SANTIKA BPS')

@section('content')

<!-- CSS Kustom untuk Switch Uiverse -->
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

    #card-panggilan.fullscreen-active #nomor-aktif-fullscreen {
        font-size: 7vw;
        line-height: 1;
        white-space: nowrap;
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

    #card-panggilan.fullscreen-active .fullscreen-selesai-card {
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
        min-height: 3.25rem;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.2;
        text-align: left;
    }

    #card-panggilan.fullscreen-active .fullscreen-selesai-title > span:first-child {
        min-width: 0;
        flex: 1 1 auto;
    }

    #card-panggilan.fullscreen-active .fullscreen-selesai-title .badge {
        flex: 0 0 auto;
        font-size: 0.85rem;
        padding: 0.35rem 0.5rem;
    }

    #card-panggilan.fullscreen-active .fullscreen-selesai-number {
        margin: auto 0;
        padding: 0.4rem 0;
        font-size: clamp(1rem, 2vw, 2rem);
        font-weight: 800;
        line-height: 1.1;
        white-space: nowrap;
    }

    #card-panggilan.fullscreen-active .fullscreen-selesai-status {
        color: #020617;
        font-size: 0.9rem;
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

    .selesai-card {
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
        min-height: 44px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        line-height: 1.25;
    }

    .selesai-title > span:first-child {
        flex: 1 1 auto;
        min-width: 0;
    }

    .selesai-title .badge {
        flex: 0 0 auto;
    }

    .selesai-number {
        margin: auto 0;
        padding: 0.5rem 0;
        color: #1e293b;
        font-size: clamp(0.85rem, 1.35vw, 1.25rem);
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap;
        text-align: center;
    }

    .selesai-status {
        color: #94a3b8;
        font-size: 0.75rem;
        font-weight: 500;
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

        <div>
            <button onclick="toggleFullscreen()" class="btn btn-primary fw-bold px-4 py-2" style="background-color: #002060; border-color: #002060;">
                <i class="fa fa-expand me-2"></i> Layar Penuh (Fullscreen)
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kotak Utama: Nomor Antrian yang Sedang Dipanggil -->
        <div class="col-lg-7">
            <div id="card-panggilan" class="card border-0 shadow-lg text-white text-center p-5 rounded-4 position-relative" style="background: linear-gradient(135deg, #002060, #004080); min-height: 400px; display: flex; flex-direction: column; justify-content: center;">
                
                <!-- Tombol Fullscreen Khusus di dalam Card -->
                <button onclick="toggleFullscreen()" class="btn btn-sm btn-outline-light position-absolute top-0 end-0 m-3" title="Fullscreen Kartu Ini">
                    <i class="fa fa-expand"></i>
                </button>

                <div class="layout-normal">
                    <h4 class="text-uppercase tracking-wider text-warning fw-bold mb-3"><i class="fa fa-bullhorn me-2"></i> Silakan Menuju Loket Pelayanan</h4>
                    <h1 id="nomor-aktif" class="display-1 fw-bold mb-3" style="font-size: 5.5rem; letter-spacing: 2px;">---</h1>
                    <h3 id="nama-aktif" class="fw-light mb-2 text-light">Memuat Data Antrian...</h3>
                    <span id="status-aktif" class="badge bg-warning text-dark px-3 py-2 fs-6 mx-auto mt-2">Standby</span>
                </div>

                <div class="layout-fullscreen">
                    <div class="fullscreen-main w-2/3">
                        <h4 class="text-uppercase tracking-wider text-warning fw-bold mb-3"><i class="fa fa-bullhorn me-2"></i> Silakan Menuju Loket Pelayanan</h4>
                        <h1 id="nomor-aktif-fullscreen" class="fw-bold mb-3">---</h1>
                        <h3 id="nama-aktif-fullscreen" class="fw-light mb-2 text-light">Memuat Data Antrian...</h3>
                        <span id="status-aktif-fullscreen" class="badge bg-warning text-dark px-3 py-2 fs-6 mx-auto mt-2">Standby</span>
                    </div>
                    <div class="fullscreen-selesai">
                        <h5 class="fw-bold mb-3" style="color: #002060;"><i class="fa fa-check-circle me-2 text-success"></i>Pelayanan Selesai</h5>
                        <div class="fullscreen-selesai-grid">
                            <div class="fullscreen-selesai-card" style="--accent-color: #2563eb;"><div class="fullscreen-selesai-title"><span>Konsultasi Statistik</span><span class="badge bg-primary">A</span></div><div id="fullscreen-selesai-A" class="fullscreen-selesai-number text-primary">—</div><small class="fullscreen-selesai-status">Selesai</small></div>
                            <div class="fullscreen-selesai-card" style="--accent-color: #059669;"><div class="fullscreen-selesai-title"><span>Konsultasi DTSEN</span><span class="badge bg-success">B</span></div><div id="fullscreen-selesai-B" class="fullscreen-selesai-number text-success">—</div><small class="fullscreen-selesai-status">Selesai</small></div>
                            <div class="fullscreen-selesai-card" style="--accent-color: #06b6d4;"><div class="fullscreen-selesai-title"><span>Permintaan Data</span><span class="badge bg-info text-dark">C</span></div><div id="fullscreen-selesai-C" class="fullscreen-selesai-number text-info">—</div><small class="fullscreen-selesai-status">Selesai</small></div>
                            <div class="fullscreen-selesai-card" style="--accent-color: #f59e0b;"><div class="fullscreen-selesai-title"><span>Rekomendasi Kegiatan Statistik</span><span class="badge bg-warning text-dark">D</span></div><div id="fullscreen-selesai-D" class="fullscreen-selesai-number text-warning">—</div><small class="fullscreen-selesai-status">Selesai</small></div>
                            <div class="fullscreen-selesai-card" style="--accent-color: #dc2626;"><div class="fullscreen-selesai-title"><span>Pengaduan</span><span class="badge bg-danger">E</span></div><div id="fullscreen-selesai-E" class="fullscreen-selesai-number text-danger">—</div><small class="fullscreen-selesai-status">Selesai</small></div>
                            <div class="fullscreen-selesai-card" style="--accent-color: #64748b;"><div class="fullscreen-selesai-title"><span>Lainnya</span><span class="badge bg-secondary">F</span></div><div id="fullscreen-selesai-F" class="fullscreen-selesai-number text-secondary">—</div><small class="fullscreen-selesai-status">Selesai</small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Samping: Daftar Antrian Berikutnya -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white py-3 border-bottom border-2 border-primary">
                    <h5 class="fw-bold m-0" style="color: #002060;"><i class="fa fa-list-ol me-2"></i> Antrian Berikutnya</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Antrian</th>
                                    <th>Nama Pemohon</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="list-menunggu">
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Memuat data antrian...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section id="selesai-section" class="my-6">
    <div class="mb-3">
        <h4 class="fw-bold mb-1" style="color: #002060;"><i class="fa fa-check-circle text-success me-2"></i>Pelayanan Terakhir Selesai</h4>
        <p class="small text-muted mb-0">Nomor terakhir yang selesai dilayani hari ini berdasarkan jenis pelayanan.</p>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 my-6 selesai-grid">
        <div class="bg-white rounded-xl p-4 shadow-md border border-slate-100 text-center selesai-card d-flex flex-column justify-content-between h-100 border-top border-4 border-primary">
            <div class="small fw-bold text-dark selesai-title"><span>Konsultasi Statistik</span><span class="badge bg-primary shrink-0">A</span></div>
            <div id="selesai-A" class="fs-4 fw-bold text-primary selesai-number">—</div>
            <small class="text-muted selesai-status">Selesai Dilayani</small>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-md border border-slate-100 text-center selesai-card d-flex flex-column justify-content-between h-100 border-top border-4 border-success">
            <div class="small fw-bold text-dark selesai-title"><span>Konsultasi DTSEN</span><span class="badge bg-success shrink-0">B</span></div>
            <div id="selesai-B" class="fs-4 fw-bold text-success selesai-number">—</div>
            <small class="text-muted selesai-status">Selesai Dilayani</small>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-md border border-slate-100 text-center selesai-card d-flex flex-column justify-content-between h-100 border-top border-4 border-info">
            <div class="small fw-bold text-dark selesai-title"><span>Permintaan Data</span><span class="badge bg-info text-dark shrink-0">C</span></div>
            <div id="selesai-C" class="fs-4 fw-bold text-info selesai-number">—</div>
            <small class="text-muted selesai-status">Selesai Dilayani</small>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-md border border-slate-100 text-center selesai-card d-flex flex-column justify-content-between h-100 border-top border-4 border-warning">
            <div class="small fw-bold text-dark selesai-title"><span>Rekomendasi Kegiatan Statistik</span><span class="badge bg-warning text-dark shrink-0">D</span></div>
            <div id="selesai-D" class="fs-4 fw-bold text-warning selesai-number">—</div>
            <small class="text-muted selesai-status">Selesai Dilayani</small>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-md border border-slate-100 text-center selesai-card d-flex flex-column justify-content-between h-100 border-top border-4 border-danger">
            <div class="small fw-bold text-dark selesai-title"><span>Pengaduan</span><span class="badge bg-danger shrink-0">E</span></div>
            <div id="selesai-E" class="fs-4 fw-bold text-danger selesai-number">—</div>
            <small class="text-muted selesai-status">Selesai Dilayani</small>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-md border border-slate-100 text-center selesai-card d-flex flex-column justify-content-between h-100 border-top border-4 border-secondary">
            <div class="small fw-bold text-dark selesai-title"><span>Lainnya</span><span class="badge bg-secondary shrink-0">F</span></div>
            <div id="selesai-F" class="fs-4 fw-bold text-secondary selesai-number">—</div>
            <small class="text-muted selesai-status">Selesai Dilayani</small>
        </div>
    </div>
</section>

<!-- Skrip JavaScript -->
<script>
    let nomorTerakhirDipanggil = null;
    let isFullscreen = false;
    const cardPanggilan = document.getElementById('card-panggilan');
    const checkboxAudio = document.getElementById('checkboxInput');
    const labelAudioStatus = document.getElementById('status-audio-label');

    checkboxAudio.addEventListener('change', function() {
        if (this.checked) {
            labelAudioStatus.innerText = "Suara Dimatikan (Mute)";
            labelAudioStatus.style.color = "red";
        } else {
            labelAudioStatus.innerText = "Suara Aktif";
            labelAudioStatus.style.color = "green";
            playChime();
        }
    });

    function playChime() {
        if (checkboxAudio.checked) return;

        try {
            const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const frequencies = [1318.51, 1174.66, 880.00, 1046.50];
            let delay = 0;

            frequencies.forEach((freq) => {
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
        } catch (e) {
            console.log("Audio Context error:", e);
        }
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

    function ambilDataRuangTunggu() {
        fetch("{{ route('api.ruang.tunggu') }}")
            .then(response => response.json())
            .then(data => {
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

                    if (nomorTerakhirDipanggil !== data.dipanggil.id) {
                        nomorTerakhirDipanggil = data.dipanggil.id;
                        playChime();
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
            .catch(error => console.error('Gagal memuat data:', error));
    }

    setInterval(ambilDataRuangTunggu, 3000);
    window.onload = ambilDataRuangTunggu;
</script>
@endsection