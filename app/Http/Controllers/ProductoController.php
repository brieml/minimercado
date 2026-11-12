<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $productos = Producto::with(['categoria', 'proveedor'])->get();

        return view('productos.index', compact('productos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $categorias = Categoria::all();
        $proveedores = Proveedor::all();

        return view('productos.create', compact('categorias', 'proveedores'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        Producto::create($datos);

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $producto = Producto::with(['categoria', 'proveedor'])->withCount('ventas')->findOrFail($id);

        return view('productos.show', compact('producto'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $producto = Producto::findOrFail($id);
        $categorias = Categoria::all();
        $proveedores = Proveedor::all();

        return view('productos.edit', compact('producto', 'categorias', 'proveedores'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $producto = Producto::findOrFail($id);

        $producto->update($request->validate($this->reglas()));

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $producto = Producto::findOrFail($id);

        if ($producto->ventas()->exists()) {
            return redirect()->route('productos.index')->with('error', 'No se puede eliminar el producto porque tiene ventas relacionadas.');
        }

        try {
            $producto->delete();
        } catch (Exception $e) {
            return redirect()->route('productos.index')->with('error', 'No se pudo eliminar el producto.');
        }

        return redirect()->route('productos.index')->with('success', 'Producto eliminado correctamente.');
    }

    /**
     * Reglas de validación compartidas por store y update.
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglas(): array
    {
        return [
            'descripcion' => ['required', 'string', 'max:150'],
            'precio' => ['required', 'numeric', 'decimal:0,2', 'min:0', 'max:9999999999.99'],
            'categoria_id' => ['required', Rule::exists('categorias', 'id')->whereNull('deleted_at')],
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->whereNull('deleted_at')],
        ];
    }
}
