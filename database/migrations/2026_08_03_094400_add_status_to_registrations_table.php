<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('registrations', function (Blueprint $table) {
        // Menambahkan kolom status dengan nilai bawaan 'Menunggu'
        $table->string('status')->default('Menunggu'); 
    });
}

public function down()
{
    Schema::table('registrations', function (Blueprint $table) {
        $table->dropColumn('status');
    });
}
};
