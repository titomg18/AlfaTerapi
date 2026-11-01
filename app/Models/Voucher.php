<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'kode', 'pasien_id', 'timer_id', 'user_id',
    'nama_timer', 'durasi_menit', 'jumlah_sesi', 'berlaku_sampai',
])]
class Voucher extends Model
{
    public const JUMLAH_SESI = 30;   // isi satu paket voucher

    public const MASA_BERLAKU_BULAN = 6;    // berlaku sejak tanggal pembelian

    // Batas sesi per pasien dalam sehari. null = tanpa batas. Isi 1 untuk maksimal 1 sesi/hari.
    public const MAKS_SESI_PER_HARI = null;

    protected function casts(): array
    {
        return [
            'berlaku_sampai' => 'datetime',
        ];
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function timer(): BelongsTo
    {
        return $this->belongsTo(Timer::class);
    }

    public function penjual(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sesis(): HasMany
    {
        return $this->hasMany(Sesi::class)->orderBy('nomor');
    }

    // Jumlah sesi yang sudah dipakai (pakai withCount('sesis') di query list agar hemat query)
    protected function sesiTerpakai(): Attribute
    {
        return Attribute::get(
            fn () => (int) ($this->getAttribute('sesis_count') ?? $this->sesis()->count())
        );
    }

    protected function sisaSesi(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->jumlah_sesi - $this->sesi_terpakai));
    }

    // aktif = masih bisa dipakai, habis = semua sesi terpakai, kedaluwarsa = lewat masa berlaku
    protected function status(): Attribute
    {
        return Attribute::get(function () {
            if ($this->sesi_terpakai >= $this->jumlah_sesi) {
                return 'habis';
            }

            if ($this->berlaku_sampai?->isPast()) {
                return 'kedaluwarsa';
            }

            return 'aktif';
        });
    }

    // Durasi tiap sesi. Contoh: 90 => "1 jam 30 menit"
    protected function durasiLabel(): Attribute
    {
        return Attribute::get(function () {
            $jam = intdiv($this->durasi_menit, 60);
            $menit = $this->durasi_menit % 60;

            return collect([
                $jam ? "{$jam} jam" : null,
                $menit ? "{$menit} menit" : null,
            ])->filter()->implode(' ');
        });
    }

    public function scopeCari(Builder $query, ?string $q): Builder
    {
        $q = trim((string) $q);

        return $query->when($q !== '', function ($query) use ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('kode', 'like', "%{$q}%")
                    ->orWhereHas('pasien', fn ($p) => $p->where('nama', 'like', "%{$q}%"));
            });
        });
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        $hitung = '(select count(*) from sesis where sesis.voucher_id = vouchers.id)';

        return match ($status) {
            'aktif' => $query->where('berlaku_sampai', '>=', now())
                ->whereRaw("{$hitung} < vouchers.jumlah_sesi"),
            'habis' => $query->whereRaw("{$hitung} >= vouchers.jumlah_sesi"),
            'kedaluwarsa' => $query->where('berlaku_sampai', '<', now())
                ->whereRaw("{$hitung} < vouchers.jumlah_sesi"),
            default => $query,
        };
    }

    // Kode mudah dibaca: tanpa huruf/angka yang mirip (O, 0, I, 1)
    public static function buatKode(): string
    {
        $huruf = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $kode = 'VCR-';
            for ($i = 0; $i < 6; $i++) {
                $kode .= $huruf[random_int(0, strlen($huruf) - 1)];
            }
        } while (static::where('kode', $kode)->exists());

        return $kode;
    }

    // Pasien membeli paket: 30 sesi, berlaku 6 bulan sejak hari pembelian.
    public static function jual(Pasien $pasien, Timer $timer, User $penjual): self
    {
        return static::create([
            'kode' => static::buatKode(),
            'pasien_id' => $pasien->id,
            'timer_id' => $timer->id,
            'user_id' => $penjual->id,
            'nama_timer' => $timer->nama,
            'durasi_menit' => $timer->durasi_menit,
            'jumlah_sesi' => static::JUMLAH_SESI,
            'berlaku_sampai' => now()->addMonthsNoOverflow(static::MASA_BERLAKU_BULAN)->endOfDay(),
        ]);
    }

    // Pakai satu sesi di kursi kosong. Hitung mundur dimulai sekarang.
    public function pakaiDi(Kursi $kursi, User $pegawai): Sesi
    {
        return DB::transaction(function () use ($kursi, $pegawai) {
            // Kunci baris supaya dua pegawai tidak bisa memakai kursi/voucher yang sama bersamaan.
            $kursiKunci = Kursi::whereKey($kursi->getKey())->lockForUpdate()->firstOrFail();
            $voucherKunci = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            $terpakai = $voucherKunci->sesis()->count();

            if ($terpakai >= $voucherKunci->jumlah_sesi) {
                throw new DomainException('Semua '.$voucherKunci->jumlah_sesi.' sesi voucher ini sudah terpakai.');
            }

            if ($voucherKunci->berlaku_sampai?->isPast()) {
                throw new DomainException(
                    'Voucher sudah kedaluwarsa pada '.$voucherKunci->berlaku_sampai->locale('id')->translatedFormat('j F Y').'.'
                );
            }

            $sedangTerapi = Sesi::with('kursi')
                ->where('pasien_id', $voucherKunci->pasien_id)
                ->where('selesai_pada', '>', now())
                ->first();

            if ($sedangTerapi) {
                throw new DomainException(
                    'Pasien ini masih sedang terapi di kursi '.($sedangTerapi->no_kursi ?? $sedangTerapi->kursi?->no_kursi ?? '-').'.'
                );
            }

            if (static::MAKS_SESI_PER_HARI !== null) {
                $hariIni = Sesi::where('pasien_id', $voucherKunci->pasien_id)
                    ->whereBetween('dimulai_pada', [now()->startOfDay(), now()->endOfDay()])
                    ->count();

                if ($hariIni >= static::MAKS_SESI_PER_HARI) {
                    throw new DomainException('Pasien ini sudah mencapai batas '.static::MAKS_SESI_PER_HARI.' sesi untuk hari ini.');
                }
            }

            if ($kursiKunci->sedangDipakai()) {
                throw new DomainException('Kursi '.$kursiKunci->no_kursi.' sedang dipakai. Pilih kursi lain.');
            }

            $mulai = now();

            return $voucherKunci->sesis()->create([
                'pasien_id' => $voucherKunci->pasien_id,
                'kursi_id' => $kursiKunci->id,
                'user_id' => $pegawai->id,
                'nomor' => $terpakai + 1,
                'no_kursi' => $kursiKunci->no_kursi,
                'nama_pegawai' => $pegawai->name,
                'durasi_menit' => $voucherKunci->durasi_menit,
                'dimulai_pada' => $mulai,
                'selesai_pada' => $mulai->copy()->addMinutes($voucherKunci->durasi_menit),
            ]);
        });
    }
}
