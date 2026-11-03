<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\Role;
use App\Models\Timer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasienRegistrationAndApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::create([
            'name' => 'admin',
            'label' => 'Admin',
        ]);

        $rolePegawai = Role::create([
            'name' => 'pegawai',
            'label' => 'Pegawai',
        ]);

        $this->admin = User::create([
            'name' => 'Super Admin',
            'username' => 'admin01',
            'password' => 'password123',
            'role_id' => $roleAdmin->id,
        ]);

        $this->pegawai = User::create([
            'name' => 'Staff Klinik',
            'username' => 'pegawai01',
            'password' => 'password123',
            'role_id' => $rolePegawai->id,
        ]);
    }

    public function test_guest_can_access_register_page_and_alias(): void
    {
        $this->get('/register')
            ->assertStatus(200)
            ->assertSee('Pendaftaran Pasien Mandiri')
            ->assertSee('Formulir Pendaftaran Pasien');

        $this->get('/althe/register')
            ->assertRedirect('/register');
    }

    public function test_guest_can_register_new_patient_with_pending_status(): void
    {
        $response = $this->post('/register', [
            'nama' => 'Budi Santoso',
            'no_telepon' => '081234567890',
            'alamat' => 'Jl. Diponegoro No. 10, Surabaya',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHas('success');
        $response->assertSessionHas('registered_pasien');

        $this->assertDatabaseHas('pasiens', [
            'nama' => 'Budi Santoso',
            'no_telepon' => '081234567890',
            'status' => 'pending',
        ]);
    }

    public function test_admin_and_pegawai_can_view_pending_patients_and_filters(): void
    {
        $pasienPending = Pasien::create([
            'nama' => 'Pasien Mandiri 1',
            'no_telepon' => '081234567891',
            'alamat' => 'Jl. Basuki Rahmat No. 1',
            'status' => 'pending',
        ]);

        $pasienApproved = Pasien::create([
            'nama' => 'Pasien Aktif 2',
            'no_telepon' => '081234567892',
            'alamat' => 'Jl. Basuki Rahmat No. 2',
            'status' => 'disetujui',
        ]);

        // Admin view
        $responseAdmin = $this->actingAs($this->admin)->get('/althe/admin/pasien');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Menunggu ACC');
        $responseAdmin->assertSee($pasienPending->nama);
        $responseAdmin->assertSee($pasienApproved->nama);

        // Filter pending for Admin
        $responseFilter = $this->actingAs($this->admin)->get('/althe/admin/pasien?status=pending');
        $responseFilter->assertStatus(200);
        $responseFilter->assertSee($pasienPending->nama);

        // Pegawai view
        $responsePegawai = $this->actingAs($this->pegawai)->get('/althe/pegawai/pasien');
        $responsePegawai->assertStatus(200);
        $responsePegawai->assertSee('Menunggu ACC');
        $responsePegawai->assertSee($pasienPending->nama);
    }

    public function test_admin_can_approve_and_reject_patient(): void
    {
        $pasien = Pasien::create([
            'nama' => 'Calon Pasien',
            'no_telepon' => '081234567893',
            'alamat' => 'Jl. Gubeng No. 15',
            'status' => 'pending',
        ]);

        // Approve (ACC)
        $responseApprove = $this->actingAs($this->admin)
            ->post("/althe/admin/pasien/{$pasien->id}/approve");

        $responseApprove->assertSessionHas('success');
        $this->assertTrue($pasien->fresh()->isDisetujui());

        // Reject (Tolak)
        $responseReject = $this->actingAs($this->admin)
            ->post("/althe/admin/pasien/{$pasien->id}/reject");

        $responseReject->assertSessionHas('success');
        $this->assertTrue($pasien->fresh()->isDitolak());
    }

    public function test_pegawai_can_approve_and_reject_patient(): void
    {
        $pasien = Pasien::create([
            'nama' => 'Calon Pasien Pegawai',
            'no_telepon' => '081234567894',
            'alamat' => 'Jl. Darmo No. 20',
            'status' => 'pending',
        ]);

        // Pegawai Approve (ACC)
        $responseApprove = $this->actingAs($this->pegawai)
            ->post("/althe/pegawai/pasien/{$pasien->id}/approve");

        $responseApprove->assertSessionHas('success');
        $this->assertTrue($pasien->fresh()->isDisetujui());

        // Pegawai Reject (Tolak)
        $responseReject = $this->actingAs($this->pegawai)
            ->post("/althe/pegawai/pasien/{$pasien->id}/reject");

        $responseReject->assertSessionHas('success');
        $this->assertTrue($pasien->fresh()->isDitolak());
    }

    public function test_voucher_can_only_be_sold_to_approved_patient(): void
    {
        $timer = Timer::create([
            'nama' => 'Terapi Ringan',
            'durasi_menit' => 30,
        ]);

        $pasienPending = Pasien::create([
            'nama' => 'Pasien Belum ACC',
            'no_telepon' => '081234567895',
            'alamat' => 'Jl. Pemuda No. 5',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->pegawai)->post('/althe/pegawai/voucher', [
            'pasien_id' => $pasienPending->id,
            'timer_id' => $timer->id,
        ]);

        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('vouchers', [
            'pasien_id' => $pasienPending->id,
        ]);
    }
}
