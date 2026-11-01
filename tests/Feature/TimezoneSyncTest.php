<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TimezoneSyncTest extends TestCase
{
    public function test_application_timezone_and_locale_are_configured_for_indonesia(): void
    {
        $this->assertSame('Asia/Jakarta', config('app.timezone'));
        $this->assertSame('id', config('app.locale'));
        $this->assertSame('Asia/Jakarta', date_default_timezone_get());
        $this->assertSame('+07:00', now()->format('P'));
    }

    public function test_database_and_php_time_are_synchronized(): void
    {
        $phpNow = now();
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            $dbNowString = DB::select('SELECT NOW() as now')[0]->now;
            $dbNow = Carbon::parse($dbNowString, config('app.timezone'));

            $diffInSeconds = abs($phpNow->diffInSeconds($dbNow));
            $this->assertLessThanOrEqual(2, $diffInSeconds, 'PHP and MySQL time differ by more than 2 seconds.');

            $sessionTz = DB::select('SELECT @@time_zone as tz')[0]->tz;
            $this->assertSame('+07:00', $sessionTz);
        } else {
            $dbNowString = DB::select("SELECT datetime('now', 'localtime') as now")[0]->now;
            $dbNow = Carbon::parse($dbNowString, config('app.timezone'));

            $diffInSeconds = abs($phpNow->diffInSeconds($dbNow));
            $this->assertLessThanOrEqual(2, $diffInSeconds, 'PHP and SQLite time differ by more than 2 seconds.');
        }
    }

    public function test_carbon_formats_dates_in_indonesian(): void
    {
        $carbon = Carbon::create(2026, 11, 2, 10, 0, 0, 'Asia/Jakarta');
        $this->assertSame('Senin, 2 November 2026', $carbon->translatedFormat('l, j F Y'));
    }
}
