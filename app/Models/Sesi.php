<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'voucher_id', 'pasien_id', 'kursi_id', 'user_id',
    'nomor', 'no_kursi', 'nama_pegawai', 'durasi_menit',
    'dimulai_pada', 'selesai_pada',
])]
class Sesi extends Model
{
    protected $table = 'sesis';

    protected function casts(): array
    {
        return [
            'dimulai_pada' => 'datetime',
            'selesai_pada' => 'datetime',
        ];
    }

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(Voucher::class);
    }

    public function pasien(): BelongsTo
    {
        return $this->belongsTo(Pasien::class);
    }

    public function kursi(): BelongsTo
    {
        return $this->belongsTo(Kursi::class);
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Sesi yang timernya sudah habis
    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('selesai_pada', '<=', now());
    }

    // Cari berdasarkan nama pasien atau kode voucher
    public function scopeCari(Builder $query, ?string $q): Builder
    {
        $q = trim((string) $q);

        return $query->when($q !== '', function ($query) use ($q) {
            $query->where(function ($w) use ($q) {
                $w->whereHas('pasien', fn ($p) => $p->where('nama', 'like', "%{$q}%"))
                  ->orWhereHas('voucher', fn ($v) => $v->where('kode', 'like', "%{$q}%"));
            });
        });
    }
}