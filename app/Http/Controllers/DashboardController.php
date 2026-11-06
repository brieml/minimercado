<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Usuario;
use App\Models\Venta;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $productos = Producto::count();
        $clientes = Cliente::count();
        $categorias = Categoria::count();
        $proveedores = Proveedor::count();
        $usuarios = Usuario::count();
        $facturas = Factura::count();
        $ventas = Venta::count();

        return view('dashboard', compact(
            'productos',
            'clientes',
            'categorias',
            'proveedores',
            'usuarios',
            'facturas',
            'ventas'
        ));
    }
}
