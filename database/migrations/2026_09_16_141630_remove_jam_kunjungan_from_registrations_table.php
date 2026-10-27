<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('registrations', 'jam_kunjungan')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropColumn('jam_kunjungan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->time('jam_kunjungan')->nullable()->after('tanggal_kunjungan');
        });
    }
};
