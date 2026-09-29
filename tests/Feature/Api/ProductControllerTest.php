<?php

namespace Tests\Feature\Api;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        Product::factory()->count(5)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'name', 'slug', 'sku', 'price', 'category', 'brand', 'images'],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
                'links' => ['first', 'last', 'prev', 'next'],
            ]);
    }

    public function test_can_filter_products_by_category(): void
    {
        $category1 = Category::factory()->create(['slug' => 'category-1', 'is_active' => true]);
        $category2 = Category::factory()->create(['slug' => 'category-2', 'is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        Product::factory()->create(['category_id' => $category1->id, 'brand_id' => $brand->id, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category2->id, 'brand_id' => $brand->id, 'is_active' => true]);

        $response = $this->getJson('/api/products?category=category-1');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($category1->id, $data[0]['category']['id']);
    }

    public function test_can_filter_products_by_brand(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand1 = Brand::factory()->create(['slug' => 'brand-1', 'is_active' => true]);
        $brand2 = Brand::factory()->create(['slug' => 'brand-2', 'is_active' => true]);

        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand1->id, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand2->id, 'is_active' => true]);

        $response = $this->getJson('/api/products?brand=brand-1');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals($brand1->id, $data[0]['brand']['id']);
    }

    public function test_can_filter_products_by_price_range(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'price' => 10, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'price' => 50, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'price' => 100, 'is_active' => true]);

        $response = $this->getJson('/api/products?min_price=20&max_price=80');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertEquals(50, $data[0]['price']);
    }

    public function test_can_search_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'name' => 'Red T-Shirt', 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'name' => 'Blue Jeans', 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'name' => 'Green Hat', 'is_active' => true]);

        $response = $this->getJson('/api/products?search=Red');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('Red', $data[0]['name']);
    }

    public function test_can_sort_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'price' => 50, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'price' => 10, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'price' => 100, 'is_active' => true]);

        $response = $this->getJson('/api/products?sort=price_asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertEquals(10, $data[0]['price']);
        $this->assertEquals(50, $data[1]['price']);
        $this->assertEquals(100, $data[2]['price']);
    }

    public function test_can_get_featured_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'is_featured' => true, 'is_active' => true]);
        Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'is_featured' => false, 'is_active' => true]);

        $response = $this->getJson('/api/products/featured');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertTrue($data[0]['is_featured']);
    }

    public function test_can_get_new_arrivals(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        Product::factory()->count(10)->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'is_active' => true]);

        $response = $this->getJson('/api/products/new-arrivals');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(8, $data);
    }

    public function test_can_get_on_sale_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'compare_at_price' => 100,
            'is_active' => true,
        ]);
        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'price' => 50,
            'compare_at_price' => null,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/products/on-sale');

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertNotNull($data[0]['compare_at_price']);
        $this->assertLessThan($data[0]['compare_at_price'], $data[0]['price']);
    }

    public function test_can_get_product_by_slug(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'slug' => 'test-product',
            'is_active' => true,
        ]);
        ProductImage::factory()->create(['product_id' => $product->id]);

        $response = $this->getJson('/api/products/test-product');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => ['id', 'name', 'slug', 'sku', 'description', 'price', 'category', 'brand', 'images', 'variants'],
            ]);
        $this->assertEquals('test-product', $response->json('data.slug'));
    }

    public function test_cannot_get_inactive_product(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'slug' => 'inactive-product',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/products/inactive-product');

        $response->assertStatus(404);
    }

    public function test_can_get_related_products(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        $brand = Brand::factory()->create(['is_active' => true]);

        $product = Product::factory()->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'is_active' => true]);
        Product::factory()->count(5)->create(['category_id' => $category->id, 'brand_id' => $brand->id, 'is_active' => true]);

        $response = $this->getJson("/api/products/{$product->slug}/related");

        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(4, $data);
        foreach ($data as $related) {
            $this->assertNotEquals($product->id, $related['id']);
        }
    }
}
