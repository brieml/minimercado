<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $proveedores = Proveedor::all();

        return view('proveedores.index', compact('proveedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('proveedores.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        Proveedor::create($datos);

        return redirect()->route('proveedores.index')->with('success', 'Proveedor creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $proveedor = Proveedor::with('productos.categoria')->findOrFail($id);

        return view('proveedores.show', compact('proveedor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $proveedor = Proveedor::findOrFail($id);

        return view('proveedores.edit', compact('proveedor'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $proveedor = Proveedor::findOrFail($id);

        $proveedor->update($request->validate($this->reglas()));

        return redirect()->route('proveedores.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $proveedor = Proveedor::findOrFail($id);

        if ($proveedor->productos()->exists()) {
            return redirect()->route('proveedores.index')->with('error', 'No se puede eliminar el proveedor porque tiene productos relacionados.');
        }

        try {
            $proveedor->delete();
        } catch (Exception $e) {
            return redirect()->route('proveedores.index')->with('error', 'No se pudo eliminar el proveedor.');
        }

        return redirect()->route('proveedores.index')->with('success', 'Proveedor eliminado correctamente.');
    }

    /**
     * Reglas de validación compartidas por store y update.
     *
     * @return array<string, array<int, string>>
     */
    private function reglas(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
        ];
    }
}
