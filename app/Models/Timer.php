<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nama', 'durasi_menit'])]
class Timer extends Model
{
    // Contoh: 90 => "1 jam 30 menit", 45 => "45 menit", 60 => "1 jam"
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
}