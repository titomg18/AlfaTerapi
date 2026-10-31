<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pasiens', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('alamat');
            $table->string('no_telepon', 20); // string, bukan angka, supaya angka 0 di depan tidak hilang
            $table->timestamps();

            $table->index('nama'); // mempercepat pencarian pasien berdasarkan nama
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pasiens');
    }
};