<!DOCTYPE html>
<html>
<head>
    <title>Tiket Antrian</title>
    <style>
        body { font-family: sans-serif; text-align: center; margin: 0; padding: 20px; color: #333; }
        .container { border: 2px dashed #007bff; padding: 20px; border-radius: 10px; max-width: 400px; margin: 0 auto; background-color: #f8f9fa; }
        h3 { margin-top: 0; color: #2c3e50; }
        .nomor { font-size: 32px; font-weight: bold; color: #007bff; margin: 15px 0; }
        hr { border: 1px solid #dee2e6; margin: 15px 0; }
        .info-text { font-size: 14px; margin: 8px 0; text-align: left; padding: 0 15px; }
        .footer { font-size: 12px; color: #6c757d; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h3>TIKET ANTRIAN PST BPS</h3>
        <hr>
        <p style="margin-bottom: 5px; font-size: 13px; color: #555;">Nomor Antrian Anda:</p>
        <div class="nomor">{{ $pendaftaran->nomor_antrian }}</div>
        <hr>
        
        @php
            $serviceMeta = \App\Models\Registration::serviceBadgeMeta($pendaftaran->jenis_layanan);
        @endphp
        <div class="info-text">
            <p style="margin: 6px 0;"><strong>Nama:</strong> {{ $pendaftaran->nama }}</p>
            <p style="margin: 6px 0;"><strong>Jenis Layanan:</strong> <span style="{{ $serviceMeta['inlineStyle'] }}; display: inline-block; border-radius: 4px; padding: 2px 8px;">{{ $pendaftaran->jenis_layanan }}</span></p>
            <p style="margin: 6px 0;"><strong>Tanggal:</strong> {{ \Carbon\Carbon::parse($pendaftaran->tanggal)->translatedFormat('d F Y') }}</p>
        </div>

        <hr>
        <div class="footer">
            Harap simpan file PDF ini atau tunjukkan kepada petugas loket saat nomor Anda dipanggil.
        </div>
    </div>
</body>
</html>