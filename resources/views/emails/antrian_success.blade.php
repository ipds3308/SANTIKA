<!DOCTYPE html>
<html>
<head>
    <title>Nomor Antrian Anda</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
        <h2 style="color: #2c3e50; text-align: center;">Halo, {{ $pendaftaran->nama }}!</h2>
        <p>Terima kasih telah mendaftar. Berikut adalah detail antrian Anda:</p>
        
        <div style="background: #f4f7f6; padding: 15px; border-radius: 5px; text-align: center; margin: 20px 0;">
            <p style="margin: 0; font-size: 16px;">Nomor Antrian Anda:</p>
            <h1 style="margin: 10px 0; color: #007bff; font-size: 32px;">{{ $pendaftaran->nomor_antrian }}</h1>
            <p style="margin: 0; font-size: 14px; color: #666;">Tanggal: {{ \Carbon\Carbon::parse($pendaftaran->tanggal)->format('d F Y') }}</p>
        </div>

        <p>Harap tunjukkan email ini kepada petugas kami saat Anda datang.</p>
        <p>Salam hangat,<br><strong>Sistem Pendaftaran Online</strong></p>
    </div>
</body>
</html>