<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_collections(): void
    {
        Collection::factory()->count(5)->create(['is_active' => true]);

        $response = $this->getJson('/api/collections');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'description', 'image', 'is_featured', 'products_count'],
                ],
            ]);
    }

    public function test_can_get_collection_by_slug(): void
    {
        $collection = Collection::factory()->create(['slug' => 'test-collection', 'is_active' => true]);

        $response = $this->getJson('/api/collections/test-collection');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'description', 'image', 'is_featured', 'products'],
            ]);
        $this->assertEquals('test-collection', $response->json('data.slug'));
    }

    public function test_cannot_get_inactive_collection(): void
    {
        Collection::factory()->create(['slug' => 'inactive-collection', 'is_active' => false]);

        $response = $this->getJson('/api/collections/inactive-collection');

        $response->assertStatus(404);
    }

    public function test_can_get_collection_products(): void
    {
        $collection = Collection::factory()->create(['slug' => 'test-collection', 'is_active' => true]);
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        $products = Product::factory()->count(5)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);

        $collection->products()->attach($products->pluck('id'));

        $response = $this->getJson('/api/collections/test-collection/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'price', 'category', 'brand'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'collection' => ['id', 'name', 'slug'],
            ]);
    }

    public function test_collection_brand_and_category_images_are_loadable_urls(): void
    {
        Collection::factory()->create(['is_active' => true, 'image' => 'demo/tee-1.jpg']);
        Brand::factory()->create(['is_active' => true, 'logo' => 'brands/x.png']);
        Category::factory()->create(['is_active' => true, 'image' => null]);

        $this->assertStringEndsWith('/storage/demo/tee-1.jpg', $this->getJson('/api/collections')->assertOk()->json('data.0.image'));
        $this->assertStringEndsWith('/storage/brands/x.png', $this->getJson('/api/brands')->assertOk()->json('data.0.logo'));
        $this->assertNull($this->getJson('/api/categories')->assertOk()->json('data.0.image'));
    }
}
