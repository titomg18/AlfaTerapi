<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            // Salinan no. kursi saat voucher dipakai, supaya laporan tidak berubah
            $table->string('no_kursi', 20)->nullable()->after('kursi_id');
        });

        // Isi untuk voucher yang sudah pernah dipakai sebelum kolom ini ada
        DB::table('vouchers')
            ->whereNotNull('kursi_id')
            ->update([
                'no_kursi' => DB::raw('(select no_kursi from kursis where kursis.id = vouchers.kursi_id)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn('no_kursi');
        });
    }
};
