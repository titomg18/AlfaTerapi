<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 20)->unique();

            // Pasien pemilik voucher: tidak boleh dihapus selama punya voucher
            $table->foreignId('pasien_id')->constrained('pasiens')->restrictOnDelete();

            // Timer & penjual: boleh hilang, riwayat voucher tetap aman
            $table->foreignId('timer_id')->nullable()->constrained('timers')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Salinan data timer saat voucher dibeli
            $table->string('nama_timer', 100);
            $table->unsignedSmallInteger('durasi_menit');

            // Diisi saat voucher dipakai di kursi
            $table->foreignId('kursi_id')->nullable()->constrained('kursis')->nullOnDelete();
            $table->timestamp('dipakai_pada')->nullable();
            $table->timestamp('selesai_pada')->nullable();

            $table->timestamps();

            $table->index('selesai_pada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
