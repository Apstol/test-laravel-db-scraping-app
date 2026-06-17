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
        Schema::create('incomes', function (Blueprint $table) {
            $table->id()->primary();
            $table->integer('income_id', unsigned: true);
            $table->string('number');
            $table->date('date');
            $table->date('last_change_date');
            $table->string('supplier_article');
            $table->string('tech_size');
            $table->integer('barcode');
            $table->integer('quantity', unsigned: true);
            $table->integer('total_price', unsigned: true);
            $table->date('date_close');
            $table->string('warehouse_name');
            $table->integer('nm_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
