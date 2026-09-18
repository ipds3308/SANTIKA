<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .email-container { max-width: 600px; background-color: #ffffff; margin: 0 auto; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .header { background-color: #002060; color: #ffffff; text-align: center; padding: 20px; border-bottom: 4px solid #f98324; }
        .content { padding: 30px; color: #333333; line-height: 1.6; }
        .nomor-antrian { font-size: 40px; font-weight: bold; color: #002060; text-align: center; margin: 20px 0; padding: 15px; background-color: #f8f9fa; border-radius: 8px; border: 1px dashed #ccc; }
        .btn { display: inline-block; padding: 12px 25px; background-color: #f98324; color: #ffffff; text-decoration: none; font-weight: bold; border-radius: 5px; text-align: center; }
        .footer { background-color: #f8f9fa; padding: 15px; text-align: center; font-size: 12px; color: #777; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h2 style="margin: 0;">BADAN PUSAT STATISTIK</h2>
            <p style="margin: 5px 0 0; color: #ffc107;">Pelayanan Statistik Terpadu (PST)</p>
        </div>
        <div class="content">
            <p>Halo, <strong>{{ $pendaftaran->nama }}</strong>,</p>
            <p>Terima kasih telah melakukan pendaftaran antrian layanan tatap muka di PST BPS. Berikut adalah detail nomor antrian Anda:</p>
            
            <div class="nomor-antrian">{{ $pendaftaran->nomor_antrian }}</div>
            
            @php
                $serviceMeta = \App\Models\Registration::serviceBadgeMeta($pendaftaran->jenis_layanan);
            @endphp
            <table style="width: 100%; margin-bottom: 25px; font-size: 14px;">
                <tr><td width="38%"><strong>Tanggal Pendaftaran</strong></td><td>: {{ \Carbon\Carbon::parse($pendaftaran->created_at)->timezone('Asia/Jakarta')->format('d F Y, H:i') }} WIB</td></tr>
                <tr><td><strong>Alamat Pemohon</strong></td><td>: {{ $pendaftaran->alamat }}</td></tr>
                <tr><td><strong>Jenis Layanan</strong></td><td>: <span style="{{ $serviceMeta['inlineStyle'] }}; font-weight: bold; display: inline-block; border-radius: 4px; padding: 2px 8px;">{{ $pendaftaran->jenis_layanan }}</span></td></tr>
            </table>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('pendaftaran.cetak', $pendaftaran->id) }}" class="btn">Unduh Tiket PDF</a>
            </div>
        </div>
        <div class="footer">
            <p>Email ini dihasilkan otomatis oleh sistem antrian BPS. Harap tidak membalas email ini.</p>
        </div>
    </div>
</body>
</html>