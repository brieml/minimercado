<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
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

    Route::middleware('role:Administrador')->group(function () {
        Route::resource('usuarios', UsuarioController::class)->except('show');
    });

    Route::middleware('role:Administrador,Inventario')->group(function () {
        Route::resource('categorias', CategoriaController::class)->except('show');
        Route::resource('proveedores', ProveedorController::class)->except('show')
            ->parameters(['proveedores' => 'proveedor']);
        Route::resource('productos', ProductoController::class)->except('show');
    });

    Route::middleware('role:Administrador,Cajero')->group(function () {
        Route::resource('clientes', ClienteController::class)->except('show');
        Route::resource('ventas', VentaController::class);
    });

    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');
});
