<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'facturas';

    protected $fillable = [
        'fecha',
        'cliente_id',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

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

    /**
     * Suma de los importes de todas las ventas de la factura.
     */
    protected function total(): Attribute
    {
        return Attribute::make(
            get: fn () => round($this->ventas->sum('subtotal'), 2),
        );
    }
}
