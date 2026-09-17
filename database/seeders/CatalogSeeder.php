<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Color;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValue;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\ProductVariantAttributeValue;
use App\Models\Size;
use App\Models\SizeGroup;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding catalog data...');

        // Categories
        $categories = $this->seedCategories();

        // Brands
        $brands = $this->seedBrands();

        // Collections
        $collections = $this->seedCollections();

        // Colors
        $colors = $this->seedColors();

        // Size Groups & Sizes
        $sizeGroups = $this->seedSizeGroups();

        // Tags
        $tags = $this->seedTags();

        // Product Attributes
        $attributes = $this->seedProductAttributes($colors, $sizeGroups);

        // Products
        $products = $this->seedProducts($categories, $brands, $collections, $tags);

        // Product Variants with attributes
        $this->seedProductVariants($products, $attributes, $sizeGroups);

        $this->command->info('Catalog seeding completed!');
    }

    private function seedCategories(): array
    {
        $data = [
            ['name' => 'Men', 'slug' => 'men', 'description' => 'Men\'s clothing and accessories', 'sort_order' => 1],
            ['name' => 'Women', 'slug' => 'women', 'description' => 'Women\'s clothing and accessories', 'sort_order' => 2],
            ['name' => 'Kids', 'slug' => 'kids', 'description' => 'Children\'s clothing', 'sort_order' => 3],
            ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Accessories for all', 'sort_order' => 4],
            ['name' => 'Sale', 'slug' => 'sale', 'description' => 'Discounted items', 'sort_order' => 5],
        ];

        $categories = [];
        foreach ($data as $catData) {
            $categories[] = Category::firstOrCreate(['slug' => $catData['slug']], $catData);
        }

        // Subcategories
        $menCategory = Category::where('slug', 'men')->first();
        $womenCategory = Category::where('slug', 'women')->first();

        $subCategories = [
            ['name' => 'T-Shirts', 'slug' => 'men-t-shirts', 'parent_id' => $menCategory->id, 'sort_order' => 1],
            ['name' => 'Jeans', 'slug' => 'men-jeans', 'parent_id' => $menCategory->id, 'sort_order' => 2],
            ['name' => 'Shirts', 'slug' => 'men-shirts', 'parent_id' => $menCategory->id, 'sort_order' => 3],
            ['name' => 'Jackets', 'slug' => 'men-jackets', 'parent_id' => $menCategory->id, 'sort_order' => 4],
            ['name' => 'Dresses', 'slug' => 'women-dresses', 'parent_id' => $womenCategory->id, 'sort_order' => 1],
            ['name' => 'Tops', 'slug' => 'women-tops', 'parent_id' => $womenCategory->id, 'sort_order' => 2],
            ['name' => 'Skirts', 'slug' => 'women-skirts', 'parent_id' => $womenCategory->id, 'sort_order' => 3],
            ['name' => 'Outerwear', 'slug' => 'women-outerwear', 'parent_id' => $womenCategory->id, 'sort_order' => 4],
        ];

        foreach ($subCategories as $catData) {
            $categories[] = Category::firstOrCreate(['slug' => $catData['slug']], $catData);
        }

        return $categories;
    }

    private function seedBrands(): array
    {
        $data = [
            ['name' => 'Nike', 'slug' => 'nike', 'description' => 'Just Do It', 'is_featured' => true, 'sort_order' => 1],
            ['name' => 'Adidas', 'slug' => 'adidas', 'description' => 'Impossible is Nothing', 'is_featured' => true, 'sort_order' => 2],
            ['name' => 'Puma', 'slug' => 'puma', 'description' => 'Forever Faster', 'is_featured' => false, 'sort_order' => 3],
            ['name' => 'Under Armour', 'slug' => 'under-armour', 'description' => 'The Only Way is Through', 'is_featured' => false, 'sort_order' => 4],
            ['name' => 'New Balance', 'slug' => 'new-balance', 'description' => 'Fearlessly Independent', 'is_featured' => true, 'sort_order' => 5],
            ['name' => 'Levi\'s', 'slug' => 'levis', 'description' => 'Original Jeans', 'is_featured' => true, 'sort_order' => 6],
            ['name' => 'Tommy Hilfiger', 'slug' => 'tommy-hilfiger', 'description' => 'Classic American Cool', 'is_featured' => false, 'sort_order' => 7],
            ['name' => 'Calvin Klein', 'slug' => 'calvin-klein', 'description' => 'Modern Minimalist', 'is_featured' => false, 'sort_order' => 8],
        ];

        $brands = [];
        foreach ($data as $brandData) {
            $brands[] = Brand::firstOrCreate(['slug' => $brandData['slug']], $brandData);
        }

        return $brands;
    }

    private function seedCollections(): array
    {
        $data = [
            ['name' => 'Spring Collection 2024', 'slug' => 'spring-2024', 'description' => 'Fresh styles for spring', 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Summer Essentials', 'slug' => 'summer-essentials', 'description' => 'Light and breezy', 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Winter Warmers', 'slug' => 'winter-warmers', 'description' => 'Cozy up in style', 'is_active' => true, 'sort_order' => 3],
            ['name' => 'Athleisure', 'slug' => 'athleisure', 'description' => 'Sporty meets casual', 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Denim Edit', 'slug' => 'denim-edit', 'description' => 'Best jeans collection', 'is_active' => true, 'sort_order' => 5],
        ];

        $collections = [];
        foreach ($data as $colData) {
            $collections[] = Collection::firstOrCreate(['slug' => $colData['slug']], $colData);
        }

        return $collections;
    }

    private function seedColors(): array
    {
        $data = [
            ['name' => 'Black', 'slug' => 'black', 'hex' => '#000000', 'sort_order' => 1],
            ['name' => 'White', 'slug' => 'white', 'hex' => '#FFFFFF', 'sort_order' => 2],
            ['name' => 'Navy', 'slug' => 'navy', 'hex' => '#000080', 'sort_order' => 3],
            ['name' => 'Grey', 'slug' => 'grey', 'hex' => '#808080', 'sort_order' => 4],
            ['name' => 'Red', 'slug' => 'red', 'hex' => '#FF0000', 'sort_order' => 5],
            ['name' => 'Blue', 'slug' => 'blue', 'hex' => '#0000FF', 'sort_order' => 6],
            ['name' => 'Green', 'slug' => 'green', 'hex' => '#008000', 'sort_order' => 7],
            ['name' => 'Beige', 'slug' => 'beige', 'hex' => '#F5F5DC', 'sort_order' => 8],
            ['name' => 'Brown', 'slug' => 'brown', 'hex' => '#A52A2A', 'sort_order' => 9],
            ['name' => 'Pink', 'slug' => 'pink', 'hex' => '#FFC0CB', 'sort_order' => 10],
        ];

        $colors = [];
        foreach ($data as $colorData) {
            $colors[] = Color::firstOrCreate(['slug' => $colorData['slug']], $colorData);
        }

        return $colors;
    }

    private function seedSizeGroups(): array
    {
        // Clothing sizes
        $clothingGroup = SizeGroup::firstOrCreate(['slug' => 'clothing'], [
            'name' => 'Clothing Sizes',
            'slug' => 'clothing',
            'description' => 'Standard clothing sizes',
        ]);

        $clothingSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
        foreach ($clothingSizes as $index => $size) {
            Size::firstOrCreate(
                ['size_group_id' => $clothingGroup->id, 'value' => $size],
                ['name' => $size, 'slug' => Str::slug($size), 'sort_order' => $index + 1]
            );
        }

        // Shoe sizes
        $shoeGroup = SizeGroup::firstOrCreate(['slug' => 'shoes'], [
            'name' => 'Shoe Sizes',
            'slug' => 'shoes',
            'description' => 'EU shoe sizes',
        ]);

        for ($i = 35; $i <= 46; $i++) {
            Size::firstOrCreate(
                ['size_group_id' => $shoeGroup->id, 'value' => (string)$i],
                ['name' => "EU $i", 'slug' => "eu-$i", 'sort_order' => $i - 34]
            );
        }

        // Numeric sizes
        $numericGroup = SizeGroup::firstOrCreate(['slug' => 'numeric'], [
            'name' => 'Numeric Sizes',
            'slug' => 'numeric',
            'description' => 'Numeric clothing sizes',
        ]);

        for ($i = 32; $i <= 44; $i++) {
            Size::firstOrCreate(
                ['size_group_id' => $numericGroup->id, 'value' => (string)$i],
                ['name' => "Size $i", 'slug' => "size-$i", 'sort_order' => $i - 31]
            );
        }

        return [$clothingGroup, $shoeGroup, $numericGroup];
    }

    private function seedTags(): array
    {
        $data = [
            ['name' => 'New Arrival', 'slug' => 'new-arrival'],
            ['name' => 'Best Seller', 'slug' => 'best-seller'],
            ['name' => 'Limited Edition', 'slug' => 'limited-edition'],
            ['name' => 'Eco Friendly', 'slug' => 'eco-friendly'],
            ['name' => 'Premium', 'slug' => 'premium'],
            ['name' => 'Organic Cotton', 'slug' => 'organic-cotton'],
            ['name' => 'Waterproof', 'slug' => 'waterproof'],
            ['name' => 'Breathable', 'slug' => 'breathable'],
        ];

        $tags = [];
        foreach ($data as $tagData) {
            $tags[] = Tag::firstOrCreate(['slug' => $tagData['slug']], $tagData);
        }

        return $tags;
    }

    private function seedProductAttributes(array $colors, array $sizeGroups): array
    {
        // Color attribute
        $colorAttribute = ProductAttribute::firstOrCreate(['slug' => 'color'], [
            'name' => 'Color',
            'slug' => 'color',
            'type' => 'select',
            'display_type' => 'color_swatch',
            'is_filterable' => true,
            'is_required' => false,
            'sort_order' => 1,
        ]);

        foreach ($colors as $color) {
            ProductAttributeValue::firstOrCreate(
                ['attribute_id' => $colorAttribute->id, 'value' => $color->name],
                [
                    'slug' => $color->slug,
                    'color_code' => $color->hex,
                    'color_id' => $color->id,
                    'sort_order' => $color->sort_order,
                ]
            );
        }

        // Size attribute
        $sizeAttribute = ProductAttribute::firstOrCreate(['slug' => 'size'], [
            'name' => 'Size',
            'slug' => 'size',
            'type' => 'select',
            'display_type' => 'button_group',
            'is_filterable' => true,
            'is_required' => true,
            'sort_order' => 2,
        ]);

        $clothingSizes = Size::where('size_group_id', $sizeGroups[0]->id)->get();
        foreach ($clothingSizes as $index => $size) {
            ProductAttributeValue::firstOrCreate(
                ['attribute_id' => $sizeAttribute->id, 'value' => $size->value],
                [
                    'slug' => $size->slug,
                    'size_id' => $size->id,
                    'sort_order' => $index + 1,
                ]
            );
        }

        // Material attribute
        $materialAttribute = ProductAttribute::firstOrCreate(['slug' => 'material'], [
            'name' => 'Material',
            'slug' => 'material',
            'type' => 'select',
            'display_type' => 'dropdown',
            'is_filterable' => true,
            'is_required' => false,
            'sort_order' => 3,
        ]);

        $materials = ['Cotton', 'Polyester', 'Wool', 'Silk', 'Linen', 'Denim', 'Leather', 'Synthetic'];
        foreach ($materials as $index => $material) {
            ProductAttributeValue::firstOrCreate(
                ['attribute_id' => $materialAttribute->id, 'value' => $material],
                ['slug' => Str::slug($material), 'sort_order' => $index + 1]
            );
        }

        return [
            'color' => $colorAttribute,
            'size' => $sizeAttribute,
            'material' => $materialAttribute,
        ];
    }

    private function seedProducts(array $categories, array $brands, array $collections, array $tags): array
    {
        $productsData = [
            [
                'name' => 'Classic Cotton T-Shirt',
                'slug' => 'classic-cotton-t-shirt',
                'sku' => 'TSH-001',
                'description' => 'A comfortable classic cotton t-shirt for everyday wear.',
                'short_description' => 'Classic fit cotton t-shirt',
                'category_id' => $categories[0]->id, // Men -> T-Shirts
                'brand_id' => $brands[0]->id, // Nike
                'price' => 29.99,
                'compare_at_price' => 39.99,
                'cost_price' => 12.00,
                'quantity' => 100,
                'low_stock_threshold' => 10,
                'material' => '100% Cotton',
                'gender' => 'men',
                'season' => 'all_season',
                'is_active' => true,
                'is_featured' => true,
                'track_quantity' => true,
                'collection_ids' => [$collections[0]->id, $collections[3]->id],
                'tag_ids' => [$tags[0]->id, $tags[4]->id],
            ],
            [
                'name' => 'Slim Fit Jeans',
                'slug' => 'slim-fit-jeans',
                'sku' => 'JEA-001',
                'description' => 'Modern slim fit jeans with stretch comfort.',
                'short_description' => 'Slim fit stretch jeans',
                'category_id' => $categories[1]->id, // Men -> Jeans
                'brand_id' => $brands[5]->id, // Levi's
                'price' => 79.99,
                'compare_at_price' => 99.99,
                'cost_price' => 35.00,
                'quantity' => 50,
                'low_stock_threshold' => 5,
                'material' => '98% Cotton, 2% Elastane',
                'gender' => 'men',
                'season' => 'all_season',
                'is_active' => true,
                'is_featured' => true,
                'track_quantity' => true,
                'collection_ids' => [$collections[4]->id],
                'tag_ids' => [$tags[1]->id, $tags[5]->id],
            ],
            [
                'name' => 'Floral Summer Dress',
                'slug' => 'floral-summer-dress',
                'sku' => 'DRS-001',
                'description' => 'Beautiful floral print dress perfect for summer days.',
                'short_description' => 'Floral print summer dress',
                'category_id' => $categories[5]->id, // Women -> Dresses
                'brand_id' => $brands[6]->id, // Tommy Hilfiger
                'price' => 89.99,
                'compare_at_price' => 119.99,
                'cost_price' => 40.00,
                'quantity' => 30,
                'low_stock_threshold' => 5,
                'material' => '100% Viscose',
                'gender' => 'women',
                'season' => 'summer',
                'is_active' => true,
                'is_featured' => true,
                'track_quantity' => true,
                'collection_ids' => [$collections[1]->id],
                'tag_ids' => [$tags[0]->id, $tags[2]->id],
            ],
            [
                'name' => 'Running Shoes Pro',
                'slug' => 'running-shoes-pro',
                'sku' => 'SHO-001',
                'description' => 'Professional running shoes with advanced cushioning.',
                'short_description' => 'Pro running shoes',
                'category_id' => $categories[3]->id, // Accessories
                'brand_id' => $brands[1]->id, // Adidas
                'price' => 149.99,
                'compare_at_price' => 179.99,
                'cost_price' => 65.00,
                'quantity' => 40,
                'low_stock_threshold' => 5,
                'material' => 'Mesh & Synthetic',
                'gender' => 'unisex',
                'season' => 'all_season',
                'is_active' => true,
                'is_featured' => true,
                'track_quantity' => true,
                'collection_ids' => [$collections[3]->id],
                'tag_ids' => [$tags[1]->id, $tags[6]->id],
            ],
            [
                'name' => 'Hooded Sweatshirt',
                'slug' => 'hooded-sweatshirt',
                'sku' => 'HOD-001',
                'description' => 'Cozy hooded sweatshirt for cooler weather.',
                'short_description' => 'Comfortable hoodie',
                'category_id' => $categories[0]->id, // Men
                'brand_id' => $brands[3]->id, // Under Armour
                'price' => 59.99,
                'compare_at_price' => 79.99,
                'cost_price' => 25.00,
                'quantity' => 60,
                'low_stock_threshold' => 8,
                'material' => '80% Cotton, 20% Polyester',
                'gender' => 'men',
                'season' => 'autumn',
                'is_active' => true,
                'is_featured' => false,
                'track_quantity' => true,
                'collection_ids' => [$collections[2]->id, $collections[3]->id],
                'tag_ids' => [$tags[3]->id, $tags[7]->id],
            ],
        ];

        $products = [];
        foreach ($productsData as $productData) {
            $collectionIds = $productData['collection_ids'] ?? [];
            $tagIds = $productData['tag_ids'] ?? [];
            unset($productData['collection_ids'], $productData['tag_ids']);

            $product = Product::firstOrCreate(['sku' => $productData['sku']], $productData);
            $product->collections()->sync($collectionIds);
            $product->tags()->sync($tagIds);
            $products[] = $product;
        }

        return $products;
    }

    private function seedProductVariants(array $products, array $attributes, array $sizeGroups): void
    {
        $colorValues = ProductAttributeValue::where('attribute_id', $attributes['color']->id)->get();
        $sizeValues = ProductAttributeValue::where('attribute_id', $attributes['size']->id)->get();

        foreach ($products as $product) {
            // Create 3-4 variants per product
            $variantCount = rand(3, 4);
            $selectedColors = $colorValues->random(min($variantCount, $colorValues->count()));
            $selectedSizes = $sizeValues->random(min(3, $sizeValues->count()));

            foreach ($selectedColors as $color) {
                foreach ($selectedSizes as $size) {
                    $sku = $product->sku . '-' . strtoupper($color->slug) . '-' . $size->value;
                    
                    // Skip if already exists
                    if (ProductVariant::where('sku', $sku)->exists()) {
                        continue;
                    }

                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $sku,
                        'price' => $product->price + rand(-5, 10),
                        'compare_at_price' => $product->compare_at_price ? $product->compare_at_price + rand(-5, 10) : null,
                        'cost_price' => $product->cost_price + rand(-2, 5),
                        'quantity' => rand(5, 30),
                        'low_stock_threshold' => 3,
                        'is_active' => true,
                        'track_quantity' => true,
                    ]);

                    // Link attribute values
                    ProductVariantAttributeValue::create([
                        'variant_id' => $variant->id,
                        'attribute_value_id' => $color->id,
                    ]);

                    ProductVariantAttributeValue::create([
                        'variant_id' => $variant->id,
                        'attribute_value_id' => $size->id,
                    ]);

                    // Add inventory
                    Inventory::create([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'sku' => $variant->sku,
                        'quantity' => $variant->quantity,
                        'reserved' => 0,
                        'available' => $variant->quantity,
                        'low_stock_threshold' => $variant->low_stock_threshold,
                        'location' => 'Main Warehouse',
                        'track_quantity' => true,
                    ]);
                }
            }
        }
    }
}