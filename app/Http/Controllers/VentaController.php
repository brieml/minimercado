<?php

namespace App\Http\Controllers;

use App\Models\Factura;
use App\Models\Producto;
use App\Models\Venta;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $ventas = Venta::with(['factura.cliente', 'producto'])->get();

        return view('ventas.index', compact('ventas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $facturas = Factura::with('cliente')->get();
        $productos = Producto::all();

        return view('ventas.create', compact('facturas', 'productos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate($this->reglas());

        Venta::create($datos);

        return redirect()->route('ventas.index')->with('success', 'Venta creada correctamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): View
    {
        $venta = Venta::with(['factura.cliente', 'producto'])->findOrFail($id);

        return view('ventas.show', compact('venta'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        $venta = Venta::findOrFail($id);
        $facturas = Factura::with('cliente')->get();
        $productos = Producto::all();

        return view('ventas.edit', compact('venta', 'facturas', 'productos'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $venta = Venta::findOrFail($id);

        $venta->update($request->validate($this->reglas()));

        return redirect()->route('ventas.index')->with('success', 'Venta actualizada correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $venta = Venta::findOrFail($id);

        try {
            $venta->delete();
        } catch (Exception $e) {
            return redirect()->route('ventas.index')->with('error', 'No se pudo eliminar la venta.');
        }

        return redirect()->route('ventas.index')->with('success', 'Venta eliminada correctamente.');
    }

    /**
     * Reglas de validación compartidas por store y update.
     *
     * La cantidad admite hasta 3 decimales (productos que se venden al peso)
     * y cabe en la columna decimal(10, 3).
     *
     * @return array<string, array<int, mixed>>
     */
    private function reglas(): array
    {
        return [
            'factura_id' => ['required', Rule::exists('facturas', 'id')->whereNull('deleted_at')],
            'producto_id' => ['required', Rule::exists('productos', 'id')->whereNull('deleted_at')],
            'cantidad' => ['required', 'numeric', 'decimal:0,3', 'min:0.001', 'max:9999999.999'],
        ];
    }
}
