<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PegawaiController extends Controller
{
    // READ: daftar pengguna
    public function index()
    {
        return view('admin.user', [
            'users' => User::with('role')->latest()->get(),
            'roles' => Role::orderBy('id')->get(),
        ]);
    }

    // CREATE
    public function store(Request $request)
    {
        $data = $request->validate($this->rules(), $this->messages());

        User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'role_id' => $data['role_id'],
            'password' => $data['password'], // di-hash otomatis oleh cast di model User
        ]);

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', 'Pengguna "'.$data['name'].'" berhasil ditambahkan.');
    }

    // UPDATE
    public function update(Request $request, User $user)
    {
        $isSelf = $user->is($request->user());

        $data = $request->validate($this->rules($user, $isSelf), $this->messages());

        $payload = [
            'name' => $data['name'],
            'username' => $data['username'],
        ];

        // Admin tidak boleh mengubah role akunnya sendiri
        // (supaya tidak ada kondisi sistem tanpa admin).
        if (! $isSelf) {
            $payload['role_id'] = $data['role_id'];
        }

        // Kata sandi hanya diganti jika diisi.
        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        $user->update($payload);

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', 'Data "'.$user->name.'" berhasil diperbarui.');
    }

    // DELETE
    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return redirect()
                ->route('admin.pegawai.index')
                ->with('error', 'Anda tidak bisa menghapus akun yang sedang dipakai.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', 'Pengguna "'.$name.'" berhasil dihapus.');
    }

    private function rules(?User $user = null, bool $isSelf = false): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required', 'string', 'max:50',
                Rule::unique('users', 'username')->ignore($user?->id),
            ],
            'role_id' => $isSelf ? ['nullable'] : ['required', 'exists:roles,id'],
            // Saat edit, kata sandi boleh dikosongkan.
            'password' => $user
                ? ['nullable', 'string', 'min:8', 'confirmed']
                : ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah dipakai.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ];
    }
}
