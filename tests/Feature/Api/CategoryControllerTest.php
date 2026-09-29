<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_categories(): void
    {
        Category::factory()->count(5)->create(['is_active' => true, 'parent_id' => null]);

        $response = $this->getJson('/api/categories');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'description', 'children', 'products_count'],
                ],
            ]);
    }

    public function test_can_get_category_by_slug(): void
    {
        $category = Category::factory()->create(['slug' => 'test-category', 'is_active' => true]);

        $response = $this->getJson('/api/categories/test-category');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description', 'parent', 'children', 'products'],
            ]);
        $this->assertEquals('test-category', $response->json('data.slug'));
    }

    public function test_cannot_get_inactive_category(): void
    {
        Category::factory()->create(['slug' => 'inactive-category', 'is_active' => false]);

        $response = $this->getJson('/api/categories/inactive-category');

        $response->assertStatus(404);
    }

    public function test_can_get_category_products(): void
    {
        $category = Category::factory()->create(['slug' => 'test-category', 'is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        Product::factory()->count(5)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/categories/test-category/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'price', 'category', 'brand'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'category' => ['id', 'name', 'slug'],
            ]);
    }

    public function test_can_get_category_tree(): void
    {
        $parent = Category::factory()->create(['is_active' => true, 'parent_id' => null]);
        Category::factory()->count(3)->create(['is_active' => true, 'parent_id' => $parent->id]);

        $response = $this->getJson('/api/categories/tree');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'children'],
                ],
            ]);
    }
}
