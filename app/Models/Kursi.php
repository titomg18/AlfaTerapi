<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['no_kursi', 'keterangan'])]
class Kursi extends Model
{
    public function sesis(): HasMany
    {
        return $this->hasMany(Sesi::class);
    }

    // Sesi yang sedang berjalan di kursi ini (null = kursi kosong)
    public function sesiAktif(): HasOne
    {
        return $this->hasOne(Sesi::class)->where('selesai_pada', '>', now());
    }

    public function sedangDipakai(): bool
    {
        return $this->sesis()->where('selesai_pada', '>', now())->exists();
    }
}