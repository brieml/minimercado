<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    public function index(Request $request): View
    {
        $usuarios = Usuario::query()
            ->when(
                $request->string('buscar')->toString(),
                fn ($query, $buscar) => $query->where(fn ($q) => $q
                    ->where('nombre', 'like', "%{$buscar}%")
                    ->orWhere('apellido', 'like', "%{$buscar}%")
                    ->orWhere('correo', 'like', "%{$buscar}%"))
            )
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        return view('usuarios.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'password' => ['required', 'string', 'min:8'],
            'rol' => ['required', Rule::in(['Administrador', 'Cajero', 'Inventario'])],
            'estado' => ['required', Rule::in(['Activo', 'Inactivo'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        Usuario::create($validated);

        return redirect()->route('usuarios.index')->with('status', 'Usuario creado correctamente.');
    }

    public function edit(Usuario $usuario): View
    {
        return view('usuarios.edit', compact('usuario'));
    }

    public function update(Request $request, Usuario $usuario): RedirectResponse
    {
        abort_if($usuario->is(auth()->user()) && $request->input('estado') !== 'Activo', 403, 'No puedes desactivar tu propio usuario.');

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo,'.$usuario->id],
            'password' => ['nullable', 'string', 'min:8'],
            'rol' => ['required', Rule::in(['Administrador', 'Cajero', 'Inventario'])],
            'estado' => ['required', Rule::in(['Activo', 'Inactivo'])],
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $usuario->update($validated);

        return redirect()->route('usuarios.index')->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $usuario): RedirectResponse
    {
        abort_if($usuario->is(auth()->user()), 403, 'No puedes eliminar tu propio usuario.');

        $usuario->delete();

        return redirect()->route('usuarios.index')->with('status', 'Usuario eliminado correctamente.');
    }
}
