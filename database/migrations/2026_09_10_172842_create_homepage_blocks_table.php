<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('homepage_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('hero, featured_categories, featured_products, collection_banner, promotional_banner, product_carousel, newsletter, custom_html');
            $table->string('title')->nullable();
            $table->json('settings')->nullable()->comment('Block-specific configuration');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index(['start_date', 'end_date', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('homepage_blocks');
    }
};
