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
        Schema::create('sales', function (Blueprint $table) {
            $table->id()->primary();
            $table->string('g_number');
            $table->date('date');
            $table->date('last_change_date');
            $table->string('supplier_article');
            $table->string('tech_size');
            $table->integer('barcode');
            $table->decimal('total_price', total: 10, places: 2);
            $table->integer('discount_percent', unsigned: true);
            $table->boolean('is_supply');
            $table->boolean('is_realization');
            $table->integer('promo_code_discount', unsigned: true)->nullable();
            $table->string('warehouse_name');
            $table->string('country_name');
            $table->string('oblast_okrug_name');
            $table->string('region_name');
            $table->bigInteger('income_id', unsigned: true);
            $table->string('sale_id');
            $table->string('odid')->nullable();
            $table->integer('spp');
            $table->decimal('for_pay', total: 10, places: 2);
            $table->decimal('finished_price', total: 10, places: 2);
            $table->decimal('price_with_disc', total: 10, places: 2);
            $table->integer('nm_id');
            $table->string('subject');
            $table->string('category');
            $table->string('brand');
            $table->boolean('is_storno')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
