<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->string('sku');
            $table->integer('quantity')->default(0);
            $table->integer('reserved')->default(0);
            $table->integer('available')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->string('location')->nullable()->comment('Warehouse location/bin');
            $table->boolean('track_quantity')->default(true);
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('cascade');
            $table->unique(['product_id', 'variant_id', 'location']);
            $table->index(['sku']);
            $table->index(['available', 'low_stock_threshold']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
