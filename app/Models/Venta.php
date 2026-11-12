<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [
        'factura_id',
        'producto_id',
        'cantidad',
    ];

    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    /**
     * Importe de la línea: cantidad por el precio del producto.
     */
    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn () => round((float) $this->cantidad * (float) ($this->producto?->precio ?? 0), 2),
        );
    }
}
