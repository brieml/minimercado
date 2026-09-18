<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $productos = Producto::count();
        $clientes = Cliente::count();
        $categorias = Categoria::count();
        $proveedores = Proveedor::count();

        $ventasHoy = null;

        if (in_array(auth()->user()->rol, ['Administrador', 'Cajero'])) {
            $facturasHoy = Factura::whereDate('fecha', today())
                ->with('ventas.producto')
                ->get();

            $ventasHoy = [
                'cantidad' => $facturasHoy->count(),
                'total' => $facturasHoy->sum(fn (Factura $factura) => $factura->total()),
            ];
        }

        return view('dashboard', compact(
            'productos',
            'clientes',
            'categorias',
            'proveedores',
            'ventasHoy'
        ));
    }
}
