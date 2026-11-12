<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FacturaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use App\Models\Usuario;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login'])
    ->name('login.post');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Cada módulo solo es accesible para los roles que lo tienen en Usuario::MODULOS_POR_ROL.
    $soloRolesDe = fn (string $modulo): string => 'role:'.implode(',', Usuario::rolesConAccesoA($modulo));

    Route::resource('usuarios', UsuarioController::class)
        ->middleware($soloRolesDe('usuarios'));

    Route::resource('clientes', ClienteController::class)
        ->middleware($soloRolesDe('clientes'));

    Route::resource('categorias', CategoriaController::class)
        ->middleware($soloRolesDe('categorias'));

    Route::resource('proveedores', ProveedorController::class)
        ->parameters(['proveedores' => 'proveedor'])
        ->middleware($soloRolesDe('proveedores'));

    Route::resource('productos', ProductoController::class)
        ->middleware($soloRolesDe('productos'));

    // Las facturas no se crean a mano: las genera el registro de una venta.
    Route::resource('facturas', FacturaController::class)
        ->except(['create', 'store'])
        ->middleware($soloRolesDe('facturas'));

    Route::resource('ventas', VentaController::class)
        ->middleware($soloRolesDe('ventas'));

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
