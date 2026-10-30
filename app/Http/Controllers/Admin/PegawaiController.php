<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class PegawaiController extends Controller
{
    public function index()
    {
        $users = User::with('role')->latest()->get();
        $roles = Role::orderBy('id')->get();

        return view('admin.user', compact('users', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'role_id'  => ['required', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required'      => 'Nama wajib diisi.',
            'username.required'  => 'Username wajib diisi.',
            'username.unique'    => 'Username sudah dipakai.',
            'role_id.required'   => 'Role wajib dipilih.',
            'role_id.exists'     => 'Role tidak valid.',
            'password.required'  => 'Kata sandi wajib diisi.',
            'password.min'       => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak sama.',
        ]);

        User::create([
            'name'     => $data['name'],
            'username' => $data['username'],
            'role_id'  => $data['role_id'],
            'password' => $data['password'], // otomatis di-hash oleh cast di model User
        ]);

        $label = Role::find($data['role_id'])->label;

        return redirect()
            ->route('admin.pegawai.index')
            ->with('success', $label . ' "' . $data['name'] . '" berhasil ditambahkan.');
    }
}