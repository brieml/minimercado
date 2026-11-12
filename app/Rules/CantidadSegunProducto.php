<?php

namespace App\Rules;

use App\Models\Producto;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida la cantidad vendida según el producto elegido en el mismo formulario:
 * los productos con gramaje admiten hasta 3 decimales y el resto solo enteros.
 *
 * El producto se busca en el campo hermano `producto_id`, tanto en un formulario
 * de una sola venta (`cantidad`) como en una venta con varias líneas (`lineas.*.cantidad`).
 */
class CantidadSegunProducto implements DataAwareRule, ValidationRule
{
    /**
     * Todos los datos que se están validando.
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $producto = $this->productoDe($attribute);

        // Si el producto falta o no existe lo informan las reglas de `producto_id`,
        // y si la cantidad no es un número lo informa la regla `numeric`.
        if ($producto === null || ! is_numeric($value)) {
            return;
        }

        $cantidad = trim((string) $value);

        if ($producto->tiene_gramaje) {
            if (! preg_match('/^[+-]?\d*\.?\d{0,3}$/', $cantidad)) {
                $fail("La cantidad de «{$producto->descripcion}» debe ser un número con hasta 3 decimales.");
            }

            return;
        }

        if (! preg_match('/^[+-]?\d+(\.0+)?$/', $cantidad)) {
            $fail("«{$producto->descripcion}» no se vende por gramaje: la cantidad debe ser un número entero.");
        }
    }

    /**
     * Producto elegido junto a la cantidad que se está validando.
     */
    private function productoDe(string $attribute): ?Producto
    {
        $id = data_get($this->data, preg_replace('/cantidad$/', 'producto_id', $attribute));

        return is_scalar($id) ? Producto::find($id) : null;
    }
}
