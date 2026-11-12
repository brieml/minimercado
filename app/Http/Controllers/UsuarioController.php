<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Closure;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $usuarios = Usuario::all();

        return view('usuarios.index', compact('usuarios'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $roles = Usuario::ROLES;
        $estados = Usuario::ESTADOS;

        return view('usuarios.create', compact('roles', 'estados'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        $datos['password'] = Hash::make($datos['password']);

        Usuario::create($datos);

        return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $usuario = Usuario::with('facturas.cliente')->findOrFail($id);

        return view('usuarios.show', compact('usuario'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $usuario = Usuario::findOrFail($id);
        $roles = Usuario::ROLES;
        $estados = Usuario::ESTADOS;

        return view('usuarios.edit', compact('usuario', 'roles', 'estados'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $usuario = Usuario::findOrFail($id);

        $datos = $request->validate($this->reglas($usuario));

        // La contraseña solo se cambia cuando se escribe una nueva.
        if (blank($datos['password'] ?? null)) {
            unset($datos['password']);
        } else {
            $datos['password'] = Hash::make($datos['password']);
        }

        $usuario->update($datos);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id): RedirectResponse
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->is($request->user())) {
            return redirect()->route('usuarios.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($usuario->facturas()->exists()) {
            return redirect()->route('usuarios.index')->with('error', 'No se puede eliminar el usuario porque tiene facturas relacionadas.');
        }

        try {
            $usuario->delete();
        } catch (Exception $e) {
            return redirect()->route('usuarios.index')->with('error', 'No se pudo eliminar el usuario.');
        }

        return redirect()->route('usuarios.index')->with('success', 'Usuario eliminado correctamente.');
    }

    /**
     * Reglas de validación compartidas por store y update.
     *
     * Al editar, la contraseña es opcional y un usuario no puede desactivarse
     * a sí mismo (el login solo admite usuarios activos).
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglas(?Usuario $usuario = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario)],
            'password' => [$usuario ? 'nullable' : 'required', 'string', 'min:8'],
            'rol' => ['required', Rule::in(Usuario::ROLES)],
            'estado' => [
                'required',
                Rule::in(Usuario::ESTADOS),
                function (string $atributo, mixed $valor, Closure $fallar) use ($usuario): void {
                    if ($usuario?->is(auth()->user()) && $valor === 'Inactivo') {
                        $fallar('No puedes desactivar tu propio usuario.');
                    }
                },
            ],
        ];
    }
}
