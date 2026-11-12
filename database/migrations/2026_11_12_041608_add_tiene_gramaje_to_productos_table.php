<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca los productos que se venden por peso (con gramaje). Solo ellos
     * admiten cantidades decimales en una venta; el resto se vende por unidades.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->boolean('tiene_gramaje')->default(false)->after('precio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn('tiene_gramaje');
        });
    }
};
