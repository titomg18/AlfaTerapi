<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timers', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100)->unique();    // contoh: "Terapi 30 menit"
            $table->unsignedSmallInteger('durasi_menit'); // disimpan dalam menit
            $table->timestamps();

            $table->index('durasi_menit');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timers');
    }
};
