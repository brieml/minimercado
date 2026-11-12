<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Usuario;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Las facturas no se crean desde aquí: las genera VentaController al registrar una venta.
 * Este controlador solo las lista, consulta, corrige y elimina.
 */
class FacturaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $facturas = Factura::with(['cliente', 'usuario', 'ventas.producto'])->get();

        return view('facturas.index', compact('facturas'));
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
     *
     * Una factura nace siempre con sus productos vendidos, así que al eliminarla
     * también se eliminan (borrado lógico) las ventas que contiene.
     */
    public function destroy(string $id): RedirectResponse
    {
        $factura = Factura::findOrFail($id);

        try {
            DB::transaction(function () use ($factura) {
                $factura->ventas()->delete();
                $factura->delete();
            });
        } catch (Exception $e) {
            return redirect()->route('facturas.index')->with('error', 'No se pudo eliminar la factura.');
        }

        return redirect()->route('facturas.index')->with('success', 'Factura eliminada correctamente.');
    }

    /**
     * Reglas de validación de la edición.
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
