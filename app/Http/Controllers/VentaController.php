<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Venta;
use App\Rules\CantidadSegunProducto;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $clientes = Cliente::orderBy('nombre')->get();
        $productos = Producto::orderBy('descripcion')->get();

        return view('ventas.create', compact('clientes', 'productos'));
    }

    /**
     * Registra una venta: genera la factura del cliente y una venta por cada producto.
     *
     * La factura se crea sola (fecha de hoy y el usuario que atiende), todo dentro de
     * una transacción para que nunca quede una factura sin sus productos.
     */
    public function store(Request $request): RedirectResponse
    {
        $datos = $request->validate([
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->whereNull('deleted_at')],
            'lineas' => ['required', 'array', 'min:1', 'max:50'],
            'lineas.*.producto_id' => ['required', 'distinct', Rule::exists('productos', 'id')->whereNull('deleted_at')],
            'lineas.*.cantidad' => $this->reglasDeCantidad(),
        ]);

        try {
            $factura = DB::transaction(function () use ($datos, $request) {
                $factura = Factura::create([
                    'fecha' => now()->toDateString(),
                    'cliente_id' => $datos['cliente_id'],
                    'usuario_id' => $request->user()->id,
                ]);

                $factura->ventas()->createMany($datos['lineas']);

                return $factura;
            });
        } catch (Exception $e) {
            report($e);

            return back()->withInput()->with('error', 'No se pudo registrar la venta. Intenta de nuevo.');
        }

        return redirect()->route('facturas.show', $factura)
            ->with('success', "Venta registrada. Se generó la factura #{$factura->id}.");
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
        $venta = Venta::with('factura.cliente')->findOrFail($id);
        $productos = Producto::orderBy('descripcion')->get();

        return view('ventas.edit', compact('venta', 'productos'));
    }

    /**
     * Update the specified resource in storage.
     *
     * La factura de la venta no cambia: solo se corrige el producto o la cantidad.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $venta = Venta::findOrFail($id);

        $venta->update($request->validate([
            'producto_id' => ['required', Rule::exists('productos', 'id')->whereNull('deleted_at')],
            'cantidad' => $this->reglasDeCantidad(),
        ]));

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
     * Reglas de la cantidad: positiva, dentro de la columna decimal(10, 3) y entera
     * salvo en los productos con gramaje, que admiten hasta 3 decimales.
     *
     * @return array<int, mixed>
     */
    private function reglasDeCantidad(): array
    {
        return ['required', 'numeric', 'min:0.001', 'max:9999999.999', new CantidadSegunProducto];
    }
}
