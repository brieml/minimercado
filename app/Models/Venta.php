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
     * Cantidad lista para mostrar: sin decimales si es entera (2) y sin ceros
     * sobrantes si no lo es (1,5), como en los productos con gramaje.
     */
    protected function cantidadFormateada(): Attribute
    {
        return Attribute::make(
            get: fn () => rtrim(rtrim(number_format((float) $this->cantidad, 3, ',', '.'), '0'), ','),
        );
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
