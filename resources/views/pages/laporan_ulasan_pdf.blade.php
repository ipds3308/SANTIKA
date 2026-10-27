<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Ulasan & Saran - SANTIKA BPS</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            color: #000;
            margin: 0;
            padding: 0;
        }

        .kop-surat {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .kop-surat td {
            vertical-align: middle;
        }

        .garis-ganda {
            border-top: 3px solid black;
            border-bottom: 1px solid black;
            height: 2px;
            margin-bottom: 15px;
            margin-top: 5px;
        }

        .judul-laporan {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
            text-decoration: underline;
            font-size: 13pt;
        }

        .info-periode {
            margin-bottom: 15px;
        }

        .ringkasan-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        .ringkasan-box td {
            border: 1px solid #000;
            padding: 8px 12px;
            width: 50%;
            text-align: center;
        }

        .ringkasan-box .label {
            font-size: 9pt;
            color: #444;
            display: block;
            margin-bottom: 3px;
        }

        .ringkasan-box .angka {
            font-size: 16pt;
            font-weight: bold;
        }

        .tabel-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            font-size: 9.5pt;
        }

        .tabel-data th,
        .tabel-data td {
            border: 1px solid black;
            padding: 5px;
            vertical-align: top;
        }

        .tabel-data th {
            background-color: #002060;
            color: #fff;
            font-weight: bold;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .bintang {
            color: #f59e0b;
            font-weight: bold;
            white-space: nowrap;
        }

        .ttd-box {
            width: 100%;
            margin-top: 20px;
        }

        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body onload="window.print();">

    <table class="kop-surat">
        <tr>
            <td style="width: 15%; text-align: right; padding-right: 22px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/2/28/Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg/960px-Lambang_Badan_Pusat_Statistik_%28BPS%29_Indonesia.svg.png"
                     width="42" height="42" style="width: 42px; height: 42px;" alt="Logo BPS">
            </td>
            <td style="width: 70%; text-align: center;">
                <div style="font-size: 18pt; font-weight: bold; line-height: 1.2;">SANTIKA</div>
                <div style="font-size: 11pt; font-weight: bold; line-height: 1.2;">BADAN PUSAT STATISTIK KABUPATEN MAGELANG</div>
                <div style="font-size: 8.5pt; line-height: 1.2;">Jl. Soekarno-Hatta No. 4 Kota Mungkid, Kabupaten Magelang</div>
            </td>
            <td style="width: 15%; text-align: left; padding-left: 15px;">
                <img src="{{ route('assets.santika-logo-hitam') }}"
                     width="42" height="42" style="width: 42px; height: 42px;" alt="Logo SANTIKA">
            </td>
        </tr>
    </table>

    <div class="garis-ganda"></div>

    <div class="judul-laporan">LAPORAN REKAPITULASI ULASAN &amp; SARAN PENGGUNA</div>
        <p class="info-periode">
            Periode: {{ $labelPeriode }}<br>
            Dicetak pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </p>

    <table class="ringkasan-box">
        <tr>
            <td>
                <span class="label">Rata-rata Rating</span>
                <span class="angka">{{ number_format($averageRating ?? 0, 1) }} / 5.0</span>
            </td>
            <td>
                <span class="label">Total Ulasan Masuk</span>
                <span class="angka">{{ $totalReview }} Responden</span>
            </td>
        </tr>
    </table>

    <table class="tabel-data">
        <thead>
            <tr>
                <th style="width: 4%;">No</th>
                <th style="width: 13%;">Tanggal Waktu</th>
                <th style="width: 18%;">Nama Pemohon</th>
                <th style="width: 12%;">No. Antrian</th>
                <th style="width: 10%;">Rating</th>
                <th style="width: 43%;">Kritik &amp; Saran</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reviews as $index => $review)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $review->created_at->format('d-m-Y H:i') }}</td>
                    <td>{{ $review->registration->nama ?? 'Pemohon Umum' }}</td>
                    <td class="text-center">{{ $review->registration->nomor_antrian ?? '-' }}</td>
                    <td class="text-center bintang">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }} ({{ $review->rating }}/5)</td>
                    <td>{{ $review->kritik_saran ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada ulasan atau penilaian yang masuk.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="ttd-box">
        <tr>
            <td width="65%"></td>
            <td width="35%" class="text-center">
                <p>Magelang, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>Mengetahui,<br><b>Petugas Pelayanan SANTIKA</b></p>
                <br><br><br>
                <p><b><u>(....................................)</u></b></p>
            </td>
        </tr>
    </table>

</body>
</html>