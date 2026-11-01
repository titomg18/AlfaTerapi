<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'kode', 'pasien_id', 'timer_id', 'user_id',
    'nama_timer', 'durasi_menit',
    'kursi_id', 'dipakai_pada', 'selesai_pada',
])]
class Voucher extends Model
{
    protected function casts(): array
    {
        return [
            'dipakai_pada' => 'datetime',
            'selesai_pada' => 'datetime',
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

    public function kursi(): BelongsTo
    {
        return $this->belongsTo(Kursi::class);
    }

    public function penjual(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // tersedia = belum dipakai, berjalan = sedang terapi, selesai = waktu habis
    protected function status(): Attribute
    {
        return Attribute::get(function () {
            if (! $this->dipakai_pada) {
                return 'tersedia';
            }

            return $this->selesai_pada?->isFuture() ? 'berjalan' : 'selesai';
        });
    }

    // Contoh: 90 => "1 jam 30 menit"
    protected function durasiLabel(): Attribute
    {
        return Attribute::get(function () {
            $jam   = intdiv($this->durasi_menit, 60);
            $menit = $this->durasi_menit % 60;

            return collect([
                $jam   ? "{$jam} jam"     : null,
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
        return match ($status) {
            'tersedia' => $query->whereNull('dipakai_pada'),
            'berjalan' => $query->whereNotNull('dipakai_pada')->where('selesai_pada', '>', now()),
            'selesai'  => $query->whereNotNull('dipakai_pada')->where('selesai_pada', '<=', now()),
            default    => $query,
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

    // Pasien membeli voucher untuk satu timer.
    public static function jual(Pasien $pasien, Timer $timer, User $penjual): self
    {
        return static::create([
            'kode'         => static::buatKode(),
            'pasien_id'    => $pasien->id,
            'timer_id'     => $timer->id,
            'user_id'      => $penjual->id,
            'nama_timer'   => $timer->nama,
            'durasi_menit' => $timer->durasi_menit,
        ]);
    }

    // Pakai voucher di kursi kosong, hitung mundur dimulai sekarang.
    public function pakaiDi(Kursi $kursi): void
    {
        DB::transaction(function () use ($kursi) {
            // Kunci baris supaya dua pegawai tidak bisa memakai kursi/voucher yang sama bersamaan.
            $kursiKunci   = Kursi::whereKey($kursi->getKey())->lockForUpdate()->firstOrFail();
            $voucherKunci = static::whereKey($this->getKey())->lockForUpdate()->firstOrFail();

            if ($voucherKunci->dipakai_pada) {
                throw new DomainException('Voucher ini sudah dipakai.');
            }

            $sedangTerapi = static::with('kursi')
                ->where('pasien_id', $voucherKunci->pasien_id)
                ->where('selesai_pada', '>', now())
                ->first();

            if ($sedangTerapi) {
                throw new DomainException(
                    'Pasien ini masih sedang terapi di kursi ' . ($sedangTerapi->kursi?->no_kursi ?? '-') . '.'
                );
            }

            if ($kursiKunci->sedangDipakai()) {
                throw new DomainException('Kursi ' . $kursiKunci->no_kursi . ' sedang dipakai. Pilih kursi lain.');
            }

            $mulai = now();

            $voucherKunci->update([
                'kursi_id'     => $kursiKunci->id,
                'dipakai_pada' => $mulai,
                'selesai_pada' => $mulai->copy()->addMinutes($voucherKunci->durasi_menit),
            ]);
        });

        $this->refresh();
    }
}