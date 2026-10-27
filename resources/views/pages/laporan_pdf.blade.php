<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Antrian - {{ $tanggal_format }} - SANTIKA BPS</title>
    <link rel="icon" type="image/png" href="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png">
    <style>
        body { font-family: "Times New Roman", Times, serif; font-size: 11pt; color: #000; margin: 20px; }
        .kop-surat { width: 100%; border-collapse: collapse; margin-bottom: 5px; }
        .kop-surat td { vertical-align: middle; }
        .garis-ganda { border-top: 3px solid black; border-bottom: 1px solid black; height: 2px; margin-bottom: 15px; margin-top: 5px; }
        .judul-laporan { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 5px; text-decoration: underline; }
        .info-laporan { margin-bottom: 15px; font-size: 11pt; }
        .tabel-data { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 10pt; }
        .tabel-data th, .tabel-data td { border: 1px solid black; padding: 6px; vertical-align: top; }
        .tabel-data th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .ttd-box { width: 100%; margin-top: 20px; page-break-inside: avoid; }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 20px; text-align: right;">
        <button onclick="window.print()" style="padding: 8px 15px; background: #002060; color: white; border: none; cursor: pointer; border-radius: 4px; font-weight: bold;">Cetak / Simpan PDF</button>
        <a href="{{ route('admin.dashboard') }}" style="padding: 8px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; margin-left: 5px; display: inline-block;">Kembali</a>
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
        <tr>
            <td style="width: 15%; text-align: right; vertical-align: middle; padding-right: 15px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png" style="height: 50px; width: auto;" alt="Logo BPS Kiri">
            </td>
            <td style="width: 70%; text-align: center; vertical-align: middle;">
                <div style="font-size: 18pt; font-weight: bold; line-height: 1.2;">SANTIKA</div>
                <div style="font-size: 11pt; font-weight: bold; line-height: 1.2;">BADAN PUSAT STATISTIK KABUPATEN MAGELANG</div>
                <div style="font-size: 8.5pt; font-weight: normal; line-height: 1.2;">Jl. Soekarno-Hatta No. 4 Kota Mungkid, Kabupaten Magelang</div>
            </td>
            <td style="width: 15%; text-align: left; vertical-align: middle; padding-left: 15px;">
                <img src="{{ route('assets.santika-logo-hitam') }}" style="height: 50px; width: auto;" alt="Logo SANTIKA Hitam">
            </td>
        </tr>
    </table>
    
    <div class="garis-ganda"></div>
    
    <div class="judul-laporan">LAPORAN REKAPITULASI ANTRIAN LAYANAN</div>
    <div class="info-laporan">
        Periode: {{ $tanggal_format }}
    </div>

    <table class="tabel-data">
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th style="width: 12%;">Tanggal Kunjungan</th>
                <th style="width: 10%;">Antrian</th>
                <th style="width: 16%;">Nama</th>
                <th style="width: 20%;">Jenis Layanan</th>
                <th style="width: 15%;">Email</th>
                <th style="width: 11%;">No. WA</th>
                <th style="width: 11%;">Alamat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pendaftar as $index => $row)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal_kunjungan)->format('d-m-Y') }}</td>
                    <td class="text-center"><b>{{ $row->nomor_antrian }}</b></td>
                    <td>{{ $row->nama }}</td>
                    <td>{{ $row->jenis_layanan }}</td>
                    <td>{{ $row->email }}</td>
                    <td class="text-center">{{ $row->no_wa }}</td>
                    <td>{{ $row->alamat }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px;">Tidak ada data rekapitulasi antrian pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd-box">
        <tr>
            <td width="65%"></td>
            <td width="35%" class="text-center">
                <p>Magelang, 18 September 2026<br>Mengetahui,<br><b>Petugas Pelayanan SANTIKA</b></p>
                <br><br><br>
                <p><b><u>( ................................................... )</u></b></p>
            </td>
        </tr>
    </table>

</body>
</html>