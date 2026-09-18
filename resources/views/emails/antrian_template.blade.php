<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>E-Tiket Antrian BPS</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 20px;">
    <div style="max-width: 450px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.08);">
        
        <!-- Header Biru BPS -->
        <div style="background-color: #002060; color: #ffffff; text-align: center; padding: 25px 20px;">
            <h2 style="margin: 0; font-size: 16px; letter-spacing: 0.5px;">BADAN PUSAT STATISTIK</h2>
            <p style="margin: 5px 0 0 0; font-size: 12px; color: #ffc107; font-weight: bold;">PELAYANAN STATISTIK TERPADU (PST)</p>
        </div>

        <!-- Konten Utama Tiket -->
        <div style="padding: 30px 20px; text-align: center;">
            <p style="color: #64748b; font-size: 12px; text-transform: uppercase; margin-bottom: 5px; font-weight: bold;">Nomor Antrian Anda</p>
            <h1 style="color: #002060; font-size: 38px; margin: 0 0 20px 0; letter-spacing: 1px;">{{ $pendaftaran->nomor_antrian }}</h1>
            
            <hr style="border: none; border-top: 1px dashed #cbd5e1; margin: 20px 0;">

            <div style="text-align: left; font-size: 14px; color: #334155; line-height: 1.6;">
                <p style="margin: 8px 0;"><strong>Nama Pemohon:</strong> {{ $pendaftaran->nama }}</p>
                <p style="margin: 8px 0;"><strong>Jenis Layanan:</strong> {{ $pendaftaran->jenis_layanan }}</p>
                <p style="margin: 8px 0;"><strong>Waktu Pendaftaran:</strong> {{ \Carbon\Carbon::parse($pendaftaran->created_at)->translatedFormat('d F Y, H:i') }} WIB</p>
                <p style="margin: 8px 0;"><strong>Status Antrian:</strong> <span style="background-color: #ffc107; color: #000; padding: 3px 10px; border-radius: 4px; font-size: 12px; font-weight: bold;">{{ $pendaftaran->status }}</span></p>
            </div>
        </div>

        <!-- Footer Footer Pesan -->
        <div style="background-color: #f8fafc; padding: 15px 20px; text-align: center; font-size: 11px; color: #64748b; border-top: 1px solid #e2e8f0;">
            <p style="margin: 0;">Tunjukkan email/e-tiket ini kepada petugas layanan saat nomor antrian Anda dipanggil di lokasi.</p>
        </div>
    </div>
</body>
</html>