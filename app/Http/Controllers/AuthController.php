<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credenciales = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $usuario = Usuario::where('correo', $request->correo)
            ->where('estado', 'Activo')
            ->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            return back()->withErrors([
                'correo' => 'Las credenciales ingresadas no son correctas.',
            ])->withInput();
        }

        Auth::login($usuario);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
