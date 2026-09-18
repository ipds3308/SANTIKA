<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    // Menentukan tabel secara eksplisit (opsional tapi praktik yang baik)
    protected $table = 'registrations';

    protected $fillable = [
        'nama',
        'email',
        'no_wa',
        'alamat',
        'jenis_layanan',
        'tanggal',
        'nomor_urut',
        'nomor_antrian',
        'status',
        'tanggal_kunjungan',
    ];

    /**
     * Mendefinisikan tema badge layanan agar konsisten di dashboard, tiket, dan email.
     *
     * @return array{badgeClass: string, inlineStyle: string}
     */
    public static function serviceBadgeMeta(?string $jenisLayanan): array
    {
        $normalized = match (true) {
            is_null($jenisLayanan) => 'Lainnya',
            str_starts_with($jenisLayanan, 'Lainnya:') => 'Lainnya',
            in_array($jenisLayanan, [
                'Konsultasi Statistik',
                'Konsultasi DTSEN',
                'Permintaan Data',
                'Rekomendasi Kegiatan Statistik',
                'Pengaduan',
                'Lainnya',
            ], true) => $jenisLayanan,
            default => 'Lainnya',
        };

        return match ($normalized) {
            'Konsultasi Statistik' => [
                'badgeClass' => 'bg-blue-100 text-blue-800',
                'inlineStyle' => 'background-color: #dbeafe; color: #1d4ed8; border: 1px solid #93c5fd;',
            ],
            'Konsultasi DTSEN' => [
                'badgeClass' => 'bg-emerald-100 text-emerald-800',
                'inlineStyle' => 'background-color: #d1fae5; color: #047857; border: 1px solid #a7f3d0;',
            ],
            'Permintaan Data' => [
                'badgeClass' => 'bg-cyan-100 text-cyan-800',
                'inlineStyle' => 'background-color: #cffafe; color: #0f766e; border: 1px solid #67e8f9;',
            ],
            'Rekomendasi Kegiatan Statistik' => [
                'badgeClass' => 'bg-amber-100 text-amber-800',
                'inlineStyle' => 'background-color: #fef3c7; color: #b45309; border: 1px solid #fcd34d;',
            ],
            'Pengaduan' => [
                'badgeClass' => 'bg-red-100 text-red-800',
                'inlineStyle' => 'background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5;',
            ],
            default => [
                'badgeClass' => 'bg-slate-200 text-slate-800',
                'inlineStyle' => 'background-color: #e2e8f0; color: #334155; border: 1px solid #cbd5e1;',
            ],
        };
    }

    // Jika nanti membutuhkan relasi (misalnya pendaftar terhubung ke user tertentu)
    // bisa ditambahkan di sini, contoh:
    // public function user() { return $this->belongsTo(User::class); }
}