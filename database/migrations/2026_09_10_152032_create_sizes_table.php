<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('size_group_id');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('value')->nullable()->comment('Numeric value for sorting, e.g., 38 for EU shoe size');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('size_group_id')->references('id')->on('size_groups')->onDelete('cascade');
            $table->index(['size_group_id', 'is_active', 'sort_order']);
            $table->unique(['size_group_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sizes');
    }
};