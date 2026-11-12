<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FacturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $facturas = Factura::with(['cliente', 'usuario'])->get();

        return view('facturas.index', compact('facturas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $clientes = Cliente::all();
        $usuarios = Usuario::all();

        return view('facturas.create', compact('clientes', 'usuarios'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        Factura::create($datos);

        return redirect()->route('facturas.index')->with('success', 'Factura creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $factura = Factura::with(['cliente', 'usuario', 'ventas.producto'])->findOrFail($id);

        return view('facturas.show', compact('factura'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $factura = Factura::findOrFail($id);
        $clientes = Cliente::all();
        $usuarios = Usuario::all();

        return view('facturas.edit', compact('factura', 'clientes', 'usuarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $factura = Factura::findOrFail($id);

        $factura->update($request->validate($this->reglas()));

        return redirect()->route('facturas.index')->with('success', 'Factura actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $factura = Factura::findOrFail($id);

        if ($factura->ventas()->exists()) {
            return redirect()->route('facturas.index')->with('error', 'No se puede eliminar la factura porque tiene ventas relacionadas.');
        }

        try {
            $factura->delete();
        } catch (Exception $e) {
            return redirect()->route('facturas.index')->with('error', 'No se pudo eliminar la factura.');
        }

        return redirect()->route('facturas.index')->with('success', 'Factura eliminada correctamente.');
    }

    /**
     * Reglas de validación compartidas por store y update.
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglas(): array
    {
        return [
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'usuario_id' => ['required', Rule::exists('usuarios', 'id')->whereNull('deleted_at')],
        ];
    }
}
