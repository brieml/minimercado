<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'descripcion',
        'precio',
        'tiene_gramaje',
        'categoria_id',
        'proveedor_id',
    ];

    protected $casts = [
        'tiene_gramaje' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
