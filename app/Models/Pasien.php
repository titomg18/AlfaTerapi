<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'alamat', 'no_telepon'])]
class Pasien extends Model
{
    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    // Pencarian berdasarkan nama atau no. telepon: Pasien::cari($q)
    public function scopeCari(Builder $query, ?string $q): Builder
    {
        $q = trim((string) $q);

        return $query->when($q !== '', function ($query) use ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                  ->orWhere('no_telepon', 'like', "%{$q}%");
            });
        });
    }
}