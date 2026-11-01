<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Tabel riwayat sesi: satu baris = satu kali duduk di kursi
        Schema::create('sesis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->restrictOnDelete();
            $table->foreignId('pasien_id')->constrained('pasiens')->restrictOnDelete();
            $table->foreignId('kursi_id')->nullable()->constrained('kursis')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->unsignedSmallInteger('nomor');          // sesi ke-1 .. ke-30
            $table->string('no_kursi', 20)->nullable();     // salinan, supaya laporan lama tetap utuh
            $table->string('nama_pegawai')->nullable();     // salinan nama terapis
            $table->unsignedSmallInteger('durasi_menit');
            $table->dateTime('dimulai_pada');
            $table->dateTime('selesai_pada');
            $table->timestamps();

            $table->unique(['voucher_id', 'nomor']);        // nomor sesi tidak bisa dobel
            $table->index('dimulai_pada');
            $table->index('selesai_pada');
        });

        // 2) Voucher mendapat kuota sesi & masa berlaku
        Schema::table('vouchers', function (Blueprint $table) {
            $table->unsignedSmallInteger('jumlah_sesi')->default(30)->after('durasi_menit');
            $table->dateTime('berlaku_sampai')->nullable()->after('jumlah_sesi');
        });

        // 3) Pindahkan data lama: voucher yang pernah dipakai menjadi sesi ke-1
        DB::table('vouchers')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $v) {
                DB::table('vouchers')->where('id', $v->id)->update([
                    'berlaku_sampai' => Carbon::parse($v->created_at ?? now())
                        ->addMonthsNoOverflow(6)->endOfDay()->format('Y-m-d H:i:s'),
                ]);

                if ($v->dipakai_pada) {
                    DB::table('sesis')->insert([
                        'voucher_id'   => $v->id,
                        'pasien_id'    => $v->pasien_id,
                        'kursi_id'     => $v->kursi_id,
                        'user_id'      => null,
                        'nomor'        => 1,
                        'no_kursi'     => $v->no_kursi ?? null,
                        'nama_pegawai' => null,
                        'durasi_menit' => $v->durasi_menit,
                        'dimulai_pada' => $v->dipakai_pada,
                        'selesai_pada' => $v->selesai_pada,
                        'created_at'   => $v->dipakai_pada,
                        'updated_at'   => $v->dipakai_pada,
                    ]);
                }
            }
        });

        // 4) Kolom lama di voucher tidak dipakai lagi
        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropIndex(['selesai_pada']);
            $table->dropConstrainedForeignId('kursi_id');
        });

        $buang = array_values(array_filter(
            ['no_kursi', 'dipakai_pada', 'selesai_pada'],
            fn ($kolom) => Schema::hasColumn('vouchers', $kolom)
        ));

        Schema::table('vouchers', function (Blueprint $table) use ($buang) {
            $table->dropColumn($buang);
        });
    }

    public function down(): void
    {
        // Mengembalikan struktur lama. Riwayat di tabel `sesis` tidak ikut dikembalikan.
        Schema::table('vouchers', function (Blueprint $table) {
            $table->foreignId('kursi_id')->nullable()->constrained('kursis')->nullOnDelete();
            $table->string('no_kursi', 20)->nullable();
            $table->dateTime('dipakai_pada')->nullable();
            $table->dateTime('selesai_pada')->nullable()->index();
        });

        Schema::table('vouchers', function (Blueprint $table) {
            $table->dropColumn(['jumlah_sesi', 'berlaku_sampai']);
        });

        Schema::dropIfExists('sesis');
    }
};