<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email');
            $table->string('no_wa');
            $table->date('tanggal');
            // Menyimpan angka urutan murni (1, 2, 3) untuk mempermudah reset & increment
            $table->integer('nomor_urut');
            // Menyimpan format visual (contoh: 20260803-001)
            $table->string('nomor_antrian')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};