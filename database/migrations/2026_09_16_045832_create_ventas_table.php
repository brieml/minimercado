<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('factura_id')
                ->constrained('facturas')
                ->cascadeOnDelete();

            $table->foreignId('producto_id')
                ->constrained('productos');

            $table->decimal('cantidad', 10, 3);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
