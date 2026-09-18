<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'facturas';

    protected $fillable = [
        'fecha',
        'cliente_id',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
        ];
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function total(): float
    {
        return $this->ventas->sum(fn (Venta $venta) => $venta->subtotal());
    }
}
