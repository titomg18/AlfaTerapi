<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kursis', function (Blueprint $table) {
            $table->id();
            $table->string('no_kursi', 20)->unique(); // string supaya bisa "1", "A1", dst.
            $table->string('keterangan')->nullable(); // opsional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kursis');
    }
};
