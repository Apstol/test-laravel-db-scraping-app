<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id()->primary();
            $table->date('date');
            $table->date('last_change_date')->nullable();
            $table->string('supplier_article')->nullable();
            $table->string('tech_size')->nullable();
            $table->integer('barcode');
            $table->integer('quantity', unsigned: true);
            $table->boolean('is_supply')->nullable();
            $table->boolean('is_realization')->nullable();
            $table->integer('quantity_full', unsigned: true)->nullable();
            $table->string('warehouse_name');
            $table->integer('in_way_to_client', unsigned: true)->nullable();
            $table->integer('in_way_from_client', unsigned: true)->nullable();
            $table->integer('nm_id');
            $table->string('subject')->nullable();
            $table->string('category')->nullable();
            $table->string('brand')->nullable();
            $table->integer('sc_code', unsigned: true)->nullable();
            $table->integer('price', unsigned: true)->nullable();
            $table->integer('discount', unsigned: true)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
