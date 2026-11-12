<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CategoriaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $categorias = Categoria::all();

        return view('categorias.index', compact('categorias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('categorias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        Categoria::create($datos);

        return redirect()->route('categorias.index')->with('success', 'Categoría creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $categoria = Categoria::with('productos')->findOrFail($id);

        return view('categorias.show', compact('categoria'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $categoria = Categoria::findOrFail($id);

        return view('categorias.edit', compact('categoria'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $categoria = Categoria::findOrFail($id);

        $categoria->update($request->validate($this->reglas($categoria)));

        return redirect()->route('categorias.index')->with('success', 'Categoría actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $categoria = Categoria::findOrFail($id);

        if ($categoria->productos()->exists()) {
            return redirect()->route('categorias.index')->with('error', 'No se puede eliminar la categoría porque tiene productos relacionados.');
        }

        try {
            $categoria->delete();
        } catch (Exception $e) {
            return redirect()->route('categorias.index')->with('error', 'No se pudo eliminar la categoría.');
        }

        return redirect()->route('categorias.index')->with('success', 'Categoría eliminada correctamente.');
    }

    /**
     * Reglas de validación compartidas por store y update.
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglas(?Categoria $categoria = null): array
    {
        return [
            'descripcion' => ['required', 'string', 'max:100', Rule::unique('categorias', 'descripcion')->ignore($categoria)],
        ];
    }
}
