<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Notifikasi Antrian SANTIKA BPS</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6; padding: 20px;">
    <div style="max-width: 500px; margin: 0 auto; background: #ffffff; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
        <h3 style="color: #002060; margin-top: 0;">Halo, {{ $pendaftaran->nama }}!</h3>
        @if($isPerubahan)
            <p>Data tiket antrian Anda telah diperbarui oleh petugas. Berikut adalah ringkasan e-tiket terbaru Anda:</p>
        @else
            <p>Pendaftaran nomor antrian Anda di SANTIKA BPS Kabupaten Magelang telah berhasil dicatat.</p>
        @endif
        
        <p style="background-color: #f8fafc; padding: 12px; border-radius: 6px; border-left: 4px solid #002060;">
            Nomor Antrian Anda: <strong>{{ $pendaftaran->nomor_antrian }}</strong>
        </p>

        <p>Jenis Layanan: <strong>{{ $pendaftaran->jenis_layanan }}</strong></p>

        <p>Silakan lihat dan unduh file <strong>PDF e-tiket</strong> yang terlampir pada email ini. Tunjukkan file tersebut (atau cetak) kepada petugas saat nomor Anda dipanggil di lokasi.</p>
        
        <p style="font-size: 12px; color: #64748b; margin-top: 30px; border-top: 1px solid #eee; pt: 15px;">
            Pesan otomatis dari SANTIKA BPS. Mohon tidak membalas email ini.
        </p>
    </div>
</body>
</html>