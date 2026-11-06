<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->string('descripcion', 150);
            $table->decimal('precio', 12, 2);

            $table->foreignId('categoria_id')
                ->constrained('categorias');

            $table->foreignId('proveedor_id')
                ->constrained('proveedores');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};