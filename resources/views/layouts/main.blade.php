<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SANTIKA BPS')</title>
    <!-- Favicon resmi dari Wikipedia -->
    <link rel="icon" href="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png" type="image/png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bps-navy: #002060;
            --bps-gold: #ffb800;
        }
        
        /* Pengaturan Layout Sticky Footer */
        html, body {
            height: 100%;
            margin: 0;
        }
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .top-utility-bar {
            background-color: #001338;
            color: #adb5bd;
            font-size: 12px;
            padding: 8px 0;
            border-bottom: 2px solid var(--bps-gold);
        }
        .main-header {
            background-color: #002b6a;
            color: white;
            padding: 20px 0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .brand-title h1 {
            font-size: 1.25rem;
            font-weight: 800;
            margin: 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .brand-title p {
            font-size: 0.82rem;
            margin: 2px 0 0 0;
            color: #d1d8e0;
        }
        .navbar-bps {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-bottom: 1px solid #dee2e6;
        }
        .navbar-bps .nav-link {
            color: var(--bps-navy);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 12px;
        }
        .navbar-bps .nav-link:hover {
            color: var(--bps-gold);
        }

        /* Konten utama fleksibel mengisi ruang agar footer menempel ke bawah */
        .main-content-wrapper {
            flex: 1 0 auto;
        }

        footer {
            background-color: #002b6a;
            color: #adb5bd;
            padding: 40px 0 0 0;
            border-top: 4px solid var(--bps-gold);
            font-size: 0.9rem;
            flex-shrink: 0;
        }
        footer h5 {
            color: white;
            font-size: 1rem;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid var(--bps-gold);
            display: inline-block;
            padding-bottom: 5px;
        }
        .footer-bottom {
            background-color: #000b1d;
            padding: 18px 0;
            margin-top: 30px;
            font-size: 0.85rem;
            text-align: center;
            color: #cbd5e1 !important;
        }
        .footer-link {
            display: inline-block;
            width: fit-content;
            max-width: 100%;
            align-self: flex-start;
        }
    </style>
</head>
<body>

    <!-- Top Utility Bar: Ditambahkan flex-column flex-md-row & text-center agar rapi di HP -->
    <div class="top-utility-bar">
        <div class="container d-flex align-items-center text-start">
            <span><i class="fa fa-globe me-2"></i> Situs Resmi Badan Pusat Statistik Kabupaten Magelang</span>
        </div>
    </div>

    <!-- Main Header: Ditambahkan flex-column flex-md-row & text-center untuk penyesuaian layar kecil -->
    <header class="main-header">
        <div class="container d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex flex-column align-items-center align-items-md-start text-center text-md-start gap-2">
                <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                    <img src="{{ route('assets.santika-logo') }}"
                        alt="Logo SANTIKA" style="height: 60px; width: auto;">

                    <div class="brand-title">
                        <h1 class="mb-0 fw-bolder" style="font-size: clamp(1.5rem, 3vw, 2.25rem); color: #ffffff;">SANTIKA</h1>
                        <p class="mb-0 text-white" style="font-size: clamp(0.75rem, 1.5vw, 0.95rem);">Sistem Antrian Terpadu Informasi dan Konsultasi Statistik</p>
                    </div>
                </div>
            </div>
            <div class="d-none d-md-block text-end">
                <span class="badge bg-warning text-dark px-3 py-2 fw-bold"><i class="fa fa-shield-alt me-1"></i> Portal Layanan Resmi</span>
            </div>
        </div>
    </header>

    <!-- Navbar Menu Utama: Diatur responsif turun ke bawah jika layar sempit -->
    <nav class="navbar navbar-expand-lg navbar-bps">
        <div class="container d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 py-2">
            <span class="navbar-brand fw-bold text-primary fs-6 m-0"><i class="fa fa-layer-group me-2"></i> Menu Utama</span>
            <div class="navbar-nav flex-row flex-wrap justify-content-center gap-1 ms-0">
                <a class="nav-link" href="{{ route('home') }}"><i class="fa fa-home me-1"></i> Beranda Pendaftaran</a>
                <a class="nav-link" href="https://magelangkab.bps.go.id/id" target="_blank"><i class="fa fa-external-link-alt me-1"></i> Website BPS Kabupaten Magelang</a>
            </div>
        </div>
    </nav>

    <!-- Konten Utama Halaman -->
    <main class="main-content-wrapper container my-4">
        @yield('content')
    </main>

    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-3 mb-4">
                    <h5>Tentang SANTIKA BPS</h5>
                    <p class="small text-light">SANTIKA (Sistem Antrean Terpadu Informasi dan Konsultasi Statistik) merupakan sistem pengelolaan antrean terintegrasi yang mengatur alur pelayanan agar masyarakat dapat mengakses layanan informasi, konsultasi, dan data statistik secara lebih tertib, cepat, transparan, dan profesional.</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>Kontak Resmi</h5>
                    <ul class="list-unstyled small text-light">
                        <li><i class="fa fa-map-marker-alt me-2 text-warning"></i> Jl. Soekarno-Hatta No. 4 Kota Mungkid, Kabupaten Magelang</li>
                        <li><i class="fa fa-envelope me-2 text-warning"></i> bps3308@bps.go.id</li>
                        <li><i class="fa fa-phone me-2 text-warning"></i> (62-293) 788143</li>
                    </ul>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>Standar Layanan</h5>
                    <p class="small text-light">Berkomitmen memberikan pelayanan prima berdasarkan prinsip integritas, objektivitas, dan profesionalisme demi kemajuan statistik nasional.</p>
                </div>
                <div class="col-md-3 mb-4">
                    <h5>Tautan Lainnya</h5>
                    <div class="d-flex flex-column gap-2 small">
                        <a href="https://pst.bps.go.id/" target="_blank" rel="noopener noreferrer" class="footer-link text-light text-decoration-none">Pelayanan Statistik Terpadu (PST) BPS</a>
                        <a href="http://s.bps.go.id/SAMBAT3308" target="_blank" rel="noopener noreferrer" class="footer-link text-light text-decoration-none">Sistem Aspirasi Masyarakat Bersama BPS Terkini (SAMBAT)</a>
                        <a href="https://lentera.akses.online/" target="_blank" rel="noopener noreferrer" class="footer-link text-light text-decoration-none">Lentera Statistika</a>
                        <a href="https://mdp.web.bps.go.id/" target="_blank" rel="noopener noreferrer" class="footer-link text-light text-decoration-none">Magelang Dalam Peta (MDP)</a>
                        <a href="https://play.google.com/store/apps/details?id=com.layanan.smartstat" target="_blank" rel="noopener noreferrer" class="footer-link text-light text-decoration-none">Aplikasi Statistik Kabupaten Magelang (SIKMA)</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; {{ date('Y') }} Badan Pusat Statistik Kabupaten Magelang. All Rights Reserved.
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>