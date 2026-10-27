<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Antrian - {{ $data->nomor_antrian }} - SANTIKA BPS</title>
    
    <style>
        @page {
            size: 80mm 120mm;
            margin: 4mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .ticket-wrapper {
            background-color: #ffffff;
            width: 100%;
            max-width: 100%;
            border-radius: 10px;
            overflow: hidden;
            text-align: center;
            border: 1px solid #cbd5e1;
            margin: 0 auto;
        }
        .ticket-header {
            background-color: #002060;
            color: #ffffff;
            padding: 10px 10px 12px;
            border-bottom: 4px solid #f98324;
        }
        .ticket-header img {
            width: 38px !important;
            height: auto !important;
            max-width: 38px !important;
            display: block;
            margin: 0 auto 4px auto;
        }
        .ticket-header .institution-name {
            margin: 0;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.6px;
            color: #ffffff;
        }
        .ticket-header .application-name {
            margin: 2px 0 0;
            font-size: 18px;
            color: #facc15;
            font-weight: bold;
            letter-spacing: 0.8px;
        }
        .ticket-header .application-subtitle {
            margin: 1px 0 0;
            font-size: 8px;
            color: #ffffff;
            opacity: 0.9;
            line-height: 1.2;
        }
        .ticket-body {
            padding: 10px 12px;
        }
        .queue-label {
            font-size: 9.5px;
            color: #6c757d;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }
        
        .queue-prefix {
            font-size: 14px;
            font-weight: bold;
            color: #475569;
            letter-spacing: 1px;
            margin: 0;
        }
        .queue-number-main {
            font-size: 48px;
            font-weight: 900;
            color: #002060;
            margin: 0 0 8px 0;
            line-height: 1;
            letter-spacing: 1.5px;
        }

        .user-details {
            text-align: left;
            margin-top: 4px;
            border-top: 1.5px dashed #dee2e6;
            padding-top: 8px;
        }
        .detail-item {
            margin-bottom: 6px;
        }
        .detail-label {
            font-size: 8.5px;
            color: #888;
            text-transform: uppercase;
            font-weight: bold;
        }
        .detail-value {
            font-size: 11px;
            color: #333;
            font-weight: bold;
            margin-top: 1px;
        }
        .ticket-footer {
            background-color: #f8f9fa;
            color: #6c757d;
            padding: 8px 10px;
            font-size: 9px;
            border-top: 1px solid #eee;
            line-height: 1.2;
        }
    </style>
</head>
<body>

    <div class="ticket-wrapper">
        <div class="ticket-header">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png" alt="Logo BPS">
            <div class="institution-name">BADAN PUSAT STATISTIK KABUPATEN MAGELANG</div>
            <div class="application-name">SANTIKA</div>
            <div class="application-subtitle">Sistem Antrian Terpadu Informasi dan Konsultasi Statistik</div>
        </div>
        
        <div class="ticket-body">
            <div class="queue-label">Nomor Antrian Anda</div>
            
            @php
                $pecah = explode('-', $data->nomor_antrian);
                $prefix = $pecah[0] . '-' . $pecah[1];
                $nomorUrut = $pecah[2];
            @endphp

            <div class="queue-prefix">{{ $prefix }}</div>
            <div class="queue-number-main">{{ $nomorUrut }}</div>
            
            <div class="user-details">
                <div class="detail-item">
                    <div class="detail-label">Nama Pemohon</div>
                    <div class="detail-value">{{ $data->nama }}</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Waktu Pendaftaran</div>
                    <div class="detail-value">{{ \Carbon\Carbon::parse($data->created_at)->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Rencana Kunjungan</div>
                    <div class="detail-value">
                        @if($data->tanggal_kunjungan)
                            {{ \Carbon\Carbon::parse($data->tanggal_kunjungan)->locale('id')->translatedFormat('d F Y') }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                <div class="detail-item">
                    <div class="detail-label">Jenis Layanan</div>
                    <div class="detail-value">
                        @php
                            $serviceMeta = \App\Models\Registration::serviceBadgeMeta($data->jenis_layanan);
                        @endphp
                        <span style="{{ $serviceMeta['inlineStyle'] }} padding: 2px 5px; border-radius: 3px; font-size: 9.5px; font-weight: bold; display: inline-block;">
                            {{ $data->jenis_layanan }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="ticket-footer">
            Tunjukkan e-tiket ini (cetak/tangkapan layar) kepada petugas layanan di lokasi.
        </div>
    </div>

</body>
</html>