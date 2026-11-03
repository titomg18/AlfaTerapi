<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'alamat', 'no_telepon', 'status', 'catatan_penolakan'])]
class Pasien extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_DISETUJUI = 'disetujui';

    public const STATUS_DITOLAK = 'ditolak';

    public function vouchers(): HasMany
    {
        return $this->hasMany(Voucher::class);
    }

    public function sesis(): HasMany
    {
        return $this->hasMany(Sesi::class);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDisetujui(): bool
    {
        return $this->status === self::STATUS_DISETUJUI;
    }

    public function isDitolak(): bool
    {
        return $this->status === self::STATUS_DITOLAK;
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Menunggu ACC',
            self::STATUS_DITOLAK => 'Ditolak',
            default => 'Disetujui',
        };
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeDisetujui(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DISETUJUI);
    }

    public function scopeDitolak(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DITOLAK);
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        $status = trim((string) $status);

        return $query->when(in_array($status, [self::STATUS_PENDING, self::STATUS_DISETUJUI, self::STATUS_DITOLAK], true), function ($q) use ($status) {
            $q->where('status', $status);
        });
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
