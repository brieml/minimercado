<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    public function index(Request $request): View
    {
        $categorias = Categoria::query()
            ->when(
                $request->string('buscar')->toString(),
                fn ($query, $buscar) => $query->where('descripcion', 'like', "%{$buscar}%")
            )
            ->orderBy('descripcion')
            ->paginate(10)
            ->withQueryString();

        return view('categorias.index', compact('categorias'));
    }

    public function create(): View
    {
        return view('categorias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:100', 'unique:categorias,descripcion'],
        ]);

        Categoria::create($validated);

        return redirect()->route('categorias.index')->with('status', 'Categoría creada correctamente.');
    }

    public function edit(Categoria $categoria): View
    {
        return view('categorias.edit', compact('categoria'));
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:100', 'unique:categorias,descripcion,'.$categoria->id],
        ]);

        $categoria->update($validated);

        return redirect()->route('categorias.index')->with('status', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $categoria->delete();

        return redirect()->route('categorias.index')->with('status', 'Categoría eliminada correctamente.');
    }
}
