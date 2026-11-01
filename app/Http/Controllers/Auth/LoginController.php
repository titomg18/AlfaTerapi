<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Arahkan ke dashboard sesuai role (admin / pegawai).
            $home = $request->user()->homeRoute();

            if (! $home) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()
                    ->withErrors(['username' => 'Akun Anda belum memiliki role. Hubungi admin.'])
                    ->onlyInput('username');
            }

            return redirect()
                ->route($home)
                ->with('success', 'Berhasil masuk! Selamat datang kembali, '.$request->user()->name.'.');
        }

        return back()
            ->withErrors(['username' => 'Username atau kata sandi salah.'])
            ->onlyInput('username');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Anda telah berhasil keluar dari akun.');
    }
}
