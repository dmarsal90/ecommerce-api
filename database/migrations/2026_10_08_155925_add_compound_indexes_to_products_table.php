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
        Schema::table('products', function (Blueprint $table) {
            // Composite indexes for common filter combinations
            $table->index(['is_active', 'category_id', 'brand_id'], 'products_active_cat_brand_idx');
            $table->index(['is_active', 'is_featured', 'category_id'], 'products_active_featured_cat_idx');
            $table->index(['is_active', 'gender', 'category_id'], 'products_active_gender_cat_idx');
            $table->index(['is_active', 'season', 'category_id'], 'products_active_season_cat_idx');
            $table->index(['is_active', 'price'], 'products_active_price_idx');
            $table->index(['is_active', 'created_at'], 'products_active_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_active_cat_brand_idx');
            $table->dropIndex('products_active_featured_cat_idx');
            $table->dropIndex('products_active_gender_cat_idx');
            $table->dropIndex('products_active_season_cat_idx');
            $table->dropIndex('products_active_price_idx');
            $table->dropIndex('products_active_created_idx');
        });
    }
};
