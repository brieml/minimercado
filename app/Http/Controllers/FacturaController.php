<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Usuario;
use Illuminate\Http\Request;
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
