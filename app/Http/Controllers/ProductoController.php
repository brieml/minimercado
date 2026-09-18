<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductoController extends Controller
{
    public function index(Request $request): View
    {
        $productos = Producto::query()
            ->with(['categoria', 'proveedor'])
            ->when(
                $request->string('buscar')->toString(),
                fn ($query, $buscar) => $query->where('descripcion', 'like', "%{$buscar}%")
            )
            ->orderBy('descripcion')
            ->paginate(10)
            ->withQueryString();

        return view('productos.index', compact('productos'));
    }

    public function create(): View
    {
        $categorias = Categoria::orderBy('descripcion')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();

        return view('productos.create', compact('categorias', 'proveedores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:150'],
            'precio' => ['required', 'numeric', 'min:0'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
        ]);

        Producto::create($validated);

        return redirect()->route('productos.index')->with('status', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto): View
    {
        $categorias = Categoria::orderBy('descripcion')->get();
        $proveedores = Proveedor::orderBy('nombre')->get();

        return view('productos.edit', compact('producto', 'categorias', 'proveedores'));
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:150'],
            'precio' => ['required', 'numeric', 'min:0'],
            'categoria_id' => ['required', 'integer', 'exists:categorias,id'],
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
        ]);

        $producto->update($validated);

        return redirect()->route('productos.index')->with('status', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return redirect()->route('productos.index')->with('status', 'Producto eliminado correctamente.');
    }
}
