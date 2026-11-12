<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $clientes = Cliente::all();

        return view('clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('clientes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        Cliente::create($datos);

        return redirect()->route('clientes.index')->with('success', 'Cliente creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $cliente = Cliente::with('facturas.usuario')->findOrFail($id);

        return view('clientes.show', compact('cliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $cliente = Cliente::findOrFail($id);

        return view('clientes.edit', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $cliente = Cliente::findOrFail($id);

        $cliente->update($request->validate($this->reglas()));

        return redirect()->route('clientes.index')->with('success', 'Cliente actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $cliente = Cliente::findOrFail($id);

        if ($cliente->facturas()->exists()) {
            return redirect()->route('clientes.index')->with('error', 'No se puede eliminar el cliente porque tiene facturas relacionadas.');
        }

        try {
            $cliente->delete();
        } catch (Exception $e) {
            return redirect()->route('clientes.index')->with('error', 'No se pudo eliminar el cliente.');
        }

        return redirect()->route('clientes.index')->with('success', 'Cliente eliminado correctamente.');
    }

    /**
     * Reglas de validación compartidas por store y update.
     *
     * @return array<string, array<int, string>>
     */
    private function reglas(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
        ];
    }
}
