<?php

namespace Tests\Feature;

use App\Models\Kursi;
use App\Models\Pasien;
use App\Models\Role;
use App\Models\Sesi;
use App\Models\Timer;
use App\Models\User;
use App\Models\Voucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PegawaiPagesTest extends TestCase
{
    use RefreshDatabase;

    private User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $rolePegawai = Role::create([
            'name' => 'pegawai',
            'label' => 'Pegawai',
        ]);

        $this->pegawai = User::create([
            'name' => 'Staf Pegawai',
            'username' => 'pegawai01',
            'password' => 'password123',
            'role_id' => $rolePegawai->id,
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/pegawai/dashboard')->assertRedirect('/login');
        $this->get('/pegawai/kursi')->assertRedirect('/login');
        $this->get('/pegawai/timer')->assertRedirect('/login');
    }

    public function test_pegawai_can_access_dashboard_with_sidebar_and_footer(): void
    {
        $response = $this->actingAs($this->pegawai)->get('/pegawai/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Pegawai');
        $response->assertSee('Alpha Theraphy');
        $response->assertSee('Status Kursi');
        $response->assertSee('Hak cipta dilindungi');
    }

    public function test_pegawai_kursi_is_read_only(): void
    {
        $kursi = Kursi::create(['no_kursi' => '1A', 'keterangan' => 'Dekat jendela']);

        $response = $this->actingAs($this->pegawai)->get('/pegawai/kursi');

        $response->assertStatus(200);
        $response->assertSee('Kursi 1A');
        $response->assertSee('Dekat jendela');
        $response->assertSee('Pegawai hanya dapat melihat status kursi');
        $response->assertDontSee('+ Tambah Kursi');
        $response->assertDontSee('admin.kursi.destroy');

        // Pegawai cannot create or delete kursi (route does not exist or method not allowed)
        $this->actingAs($this->pegawai)->post('/pegawai/kursi', ['no_kursi' => '1B'])->assertStatus(405);
        $this->actingAs($this->pegawai)->delete('/pegawai/kursi/'.$kursi->id)->assertNotFound();
    }

    public function test_pegawai_timer_is_read_only(): void
    {
        $timer = Timer::create(['nama' => '30 Menit', 'durasi_menit' => 30]);

        $response = $this->actingAs($this->pegawai)->get('/pegawai/timer');

        $response->assertStatus(200);
        $response->assertSee('30 Menit');
        $response->assertSee('Pegawai hanya dapat melihat timer yang tersedia');
        $response->assertDontSee('+ Buat Timer');

        // Pegawai cannot create or delete timer (route does not exist or method not allowed)
        $this->actingAs($this->pegawai)->post('/pegawai/timer', ['nama' => '45 Menit', 'durasi_menit' => 45])->assertStatus(405);
        $this->actingAs($this->pegawai)->delete('/pegawai/timer/'.$timer->id)->assertNotFound();
    }

    public function test_pegawai_can_access_pasien_and_voucher(): void
    {
        $this->actingAs($this->pegawai)->get('/pegawai/pasien')->assertStatus(200);
        $this->actingAs($this->pegawai)->get('/pegawai/voucher')->assertStatus(200);
    }

    public function test_transaksi_page_renders_completed_sessions_properly(): void
    {
        $pasien = Pasien::create(['nama' => 'Budi Santoso', 'no_telepon' => '08123456789', 'alamat' => 'Jl. Mawar No. 1']);
        $timer = Timer::create(['nama' => 'Terapi Standar', 'durasi_menit' => 30]);
        $kursi = Kursi::create(['no_kursi' => '1A']);

        $voucher = Voucher::create([
            'kode' => 'VCR-TEST01',
            'pasien_id' => $pasien->id,
            'timer_id' => $timer->id,
            'user_id' => $this->pegawai->id,
            'nama_timer' => $timer->nama,
            'durasi_menit' => 30,
            'jumlah_sesi' => 30,
            'berlaku_sampai' => now()->addMonths(6),
        ]);

        Sesi::create([
            'voucher_id' => $voucher->id,
            'pasien_id' => $pasien->id,
            'kursi_id' => $kursi->id,
            'user_id' => $this->pegawai->id,
            'nomor' => 1,
            'no_kursi' => '1A',
            'nama_pegawai' => $this->pegawai->name,
            'durasi_menit' => 30,
            'dimulai_pada' => now()->subHour(),
            'selesai_pada' => now()->subMinutes(30),
        ]);

        $response = $this->actingAs($this->pegawai)->get('/pegawai/transaksi');

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso');
        $response->assertSee('VCR-TEST01');
        $response->assertSee('Kursi 1A');
        $response->assertSee('30 menit');
        $response->assertSee('sesi 1');
    }

    public function test_pegawai_can_delete_patient_without_vouchers(): void
    {
        $pasien = Pasien::create(['nama' => 'Pasien Baru', 'no_telepon' => '0899999999', 'alamat' => 'Jl. Baru']);

        $response = $this->actingAs($this->pegawai)->delete('/pegawai/pasien/'.$pasien->id);

        $response->assertRedirect('/pegawai/pasien');
        $this->assertDatabaseMissing('pasiens', ['id' => $pasien->id]);
    }

    public function test_pegawai_cannot_delete_patient_with_vouchers(): void
    {
        $pasien = Pasien::create(['nama' => 'Pasien Tetap', 'no_telepon' => '0811111111', 'alamat' => 'Jl. Tetap']);
        $timer = Timer::create(['nama' => 'Terapi', 'durasi_menit' => 30]);
        Voucher::create([
            'kode' => 'VCR-TETAP',
            'pasien_id' => $pasien->id,
            'timer_id' => $timer->id,
            'user_id' => $this->pegawai->id,
            'nama_timer' => 'Terapi',
            'durasi_menit' => 30,
            'jumlah_sesi' => 30,
            'berlaku_sampai' => now()->addMonths(6),
        ]);

        $response = $this->actingAs($this->pegawai)->delete('/pegawai/pasien/'.$pasien->id);

        $response->assertRedirect('/pegawai/pasien');
        $this->assertDatabaseHas('pasiens', ['id' => $pasien->id]);
    }
}
