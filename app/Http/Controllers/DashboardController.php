<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $usuario = $request->user();

        // Solo se cuentan (y se muestran) los módulos a los que el rol tiene acceso.
        $totales = collect([
            'productos' => Producto::class,
            'categorias' => Categoria::class,
            'proveedores' => Proveedor::class,
            'clientes' => Cliente::class,
            'facturas' => Factura::class,
            'ventas' => Venta::class,
            'usuarios' => Usuario::class,
        ])
            ->filter(fn (string $modelo, string $modulo) => $usuario->puedeAccederA($modulo))
            ->map(fn (string $modelo) => $modelo::count());

        return view('dashboard', compact('totales'));
    }
}
