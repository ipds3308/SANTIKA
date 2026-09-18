<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('registrations', function (Blueprint $table) {
            // Menambahkan kolom alamat setelah no_wa
            $table->text('alamat')->nullable()->after('no_wa');
        });
    }

    public function down()
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('alamat');
        });
    }
};