<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_brands(): void
    {
        Brand::factory()->count(5)->create(['is_active' => true]);

        $response = $this->getJson('/api/brands');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'description', 'logo', 'is_featured', 'products_count'],
                ],
            ]);
    }

    public function test_can_get_featured_brands(): void
    {
        Brand::factory()->create(['is_active' => true, 'is_featured' => true]);
        Brand::factory()->create(['is_active' => true, 'is_featured' => false]);

        $response = $this->getJson('/api/brands/featured');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['is_featured']);
    }

    public function test_can_get_brand_by_slug(): void
    {
        $brand = Brand::factory()->create(['slug' => 'test-brand', 'is_active' => true]);

        $response = $this->getJson('/api/brands/test-brand');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description', 'logo', 'is_featured', 'products'],
            ]);
        $this->assertEquals('test-brand', $response->json('data.slug'));
    }

    public function test_cannot_get_inactive_brand(): void
    {
        Brand::factory()->create(['slug' => 'inactive-brand', 'is_active' => false]);

        $response = $this->getJson('/api/brands/inactive-brand');

        $response->assertStatus(404);
    }

    public function test_can_get_brand_products(): void
    {
        $brand = Brand::factory()->create(['slug' => 'test-brand', 'is_active' => true]);
        $category = Category::factory()->create(['is_active' => true]);

        Product::factory()->count(5)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/brands/test-brand/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'price', 'category', 'brand'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'brand' => ['id', 'name', 'slug'],
            ]);
    }
}
