<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function index(Request $request): View
    {
        $facturas = Factura::query()
            ->with(['cliente', 'usuario', 'ventas.producto'])
            ->when(
                $request->string('buscar')->toString(),
                fn ($query, $buscar) => $query->whereHas('cliente', fn ($q) => $q->where('nombre', 'like', "%{$buscar}%"))
            )
            ->latest('fecha')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('ventas.index', compact('facturas'));
    }

    public function create(): View
    {
        $clientes = Cliente::orderBy('nombre')->get();
        $productos = Producto::orderBy('descripcion')->get();
        $clienteGeneralId = $clientes->firstWhere('nombre', 'Cliente General')?->id;

        return view('ventas.create', compact('clientes', 'productos', 'clienteGeneralId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validarVenta($request);

        $factura = DB::transaction(function () use ($validated) {
            $factura = Factura::create([
                'fecha' => $validated['fecha'],
                'cliente_id' => $validated['cliente_id'],
                'usuario_id' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $factura->ventas()->create($item);
            }

            return $factura;
        });

        return redirect()->route('ventas.show', $factura)->with('status', 'Venta registrada correctamente.');
    }

    public function show(Factura $venta): View
    {
        $venta->load(['cliente', 'usuario', 'ventas.producto']);

        return view('ventas.show', ['factura' => $venta]);
    }

    public function edit(Factura $venta): View
    {
        $venta->load('ventas');
        $clientes = Cliente::orderBy('nombre')->get();
        $productos = Producto::orderBy('descripcion')->get();

        return view('ventas.edit', ['factura' => $venta, 'clientes' => $clientes, 'productos' => $productos]);
    }

    public function update(Request $request, Factura $venta): RedirectResponse
    {
        $validated = $this->validarVenta($request);

        DB::transaction(function () use ($venta, $validated) {
            $venta->update([
                'fecha' => $validated['fecha'],
                'cliente_id' => $validated['cliente_id'],
            ]);

            $venta->ventas()->forceDelete();

            foreach ($validated['items'] as $item) {
                $venta->ventas()->create($item);
            }
        });

        return redirect()->route('ventas.show', $venta)->with('status', 'Venta actualizada correctamente.');
    }

    public function destroy(Factura $venta): RedirectResponse
    {
        DB::transaction(function () use ($venta) {
            $venta->ventas()->delete();
            $venta->delete();
        });

        return redirect()->route('ventas.index')->with('status', 'Venta eliminada correctamente.');
    }

    /**
     * @return array{fecha: string, cliente_id: int, items: array<int, array{producto_id: int, cantidad: float}>}
     */
    private function validarVenta(Request $request): array
    {
        return $request->validate([
            'fecha' => ['required', 'date'],
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'numeric', 'min:0.001'],
        ]);
    }
}
