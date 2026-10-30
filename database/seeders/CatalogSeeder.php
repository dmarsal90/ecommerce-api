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
            ['name' => 'Men', 'slug' => 'men', 'description' => 'Men\'s streetwear', 'sort_order' => 1],
            ['name' => 'Women', 'slug' => 'women', 'description' => 'Women\'s streetwear', 'sort_order' => 2],
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
            ['name' => 'Tees', 'slug' => 'men-tees', 'parent_id' => $menCategory->id, 'sort_order' => 1],
            ['name' => 'Hoodies & Sweats', 'slug' => 'men-hoodies', 'parent_id' => $menCategory->id, 'sort_order' => 2],
            ['name' => 'Denim', 'slug' => 'men-denim', 'parent_id' => $menCategory->id, 'sort_order' => 3],
            ['name' => 'Jackets', 'slug' => 'men-jackets', 'parent_id' => $menCategory->id, 'sort_order' => 4],
            ['name' => 'Tops', 'slug' => 'women-tops', 'parent_id' => $womenCategory->id, 'sort_order' => 1],
            ['name' => 'Denim', 'slug' => 'women-denim', 'parent_id' => $womenCategory->id, 'sort_order' => 2],
            ['name' => 'Outerwear', 'slug' => 'women-outerwear', 'parent_id' => $womenCategory->id, 'sort_order' => 3],
        ];

        foreach ($subCategories as $catData) {
            $categories[] = Category::firstOrCreate(['slug' => $catData['slug']], $catData);
        }

        return $categories;
    }

    private function seedBrands(): array
    {
        $data = [
            ['name' => 'Sennitwo', 'slug' => 'sennitwo', 'description' => 'The house label', 'is_featured' => true, 'sort_order' => 1],
            ['name' => 'Concrete Club', 'slug' => 'concrete-club', 'description' => 'Built for the block', 'is_featured' => true, 'sort_order' => 2],
            ['name' => 'Night Shift', 'slug' => 'night-shift', 'description' => 'After dark essentials', 'is_featured' => true, 'sort_order' => 3],
            ['name' => 'Low Tide Co.', 'slug' => 'low-tide', 'description' => 'Washed, worn, wide', 'is_featured' => true, 'sort_order' => 4],
            ['name' => 'Alley Cat Supply', 'slug' => 'alley-cat', 'description' => 'Street cuts, no rules', 'is_featured' => true, 'sort_order' => 5],
            ['name' => 'Paper Planes', 'slug' => 'paper-planes', 'description' => 'Easy daily layers', 'is_featured' => false, 'sort_order' => 6],
            ['name' => 'Overgrown', 'slug' => 'overgrown', 'description' => 'Prints with attitude', 'is_featured' => false, 'sort_order' => 7],
        ];

        $brands = [];
        foreach ($data as $brandData) {
            $brands[] = Brand::firstOrCreate(['slug' => $brandData['slug']], $brandData + ['logo' => DemoPhoto::path('brand-'.$brandData['slug'], 'svg')]);
        }

        return $brands;
    }

    private function seedCollections(): array
    {
        $data = [
            ['name' => 'Drop 01', 'slug' => 'drop-01', 'description' => 'The first drop of the season', 'image' => DemoPhoto::path('overshirt-1'), 'is_active' => true, 'sort_order' => 1],
            ['name' => 'Core Essentials', 'slug' => 'core-essentials', 'description' => 'Pieces you wear on repeat', 'image' => DemoPhoto::path('tee-1'), 'is_active' => true, 'sort_order' => 2],
            ['name' => 'Winter Layers', 'slug' => 'winter-layers', 'description' => 'Hoods, jackets and heavy knits', 'image' => DemoPhoto::path('jacket-1'), 'is_active' => true, 'sort_order' => 3],
            ['name' => 'After Dark', 'slug' => 'after-dark', 'description' => 'Black on black, built for the night', 'image' => DemoPhoto::path('hoodie-1'), 'is_active' => true, 'sort_order' => 4],
            ['name' => 'Denim Edit', 'slug' => 'denim-edit', 'description' => 'Baggy, wide and washed', 'image' => DemoPhoto::path('jeans-1'), 'is_active' => true, 'sort_order' => 5],
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
                ['size_group_id' => $shoeGroup->id, 'value' => (string) $i],
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
                ['size_group_id' => $numericGroup->id, 'value' => (string) $i],
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

        $clothingSizes = Size::where('size_group_id', $sizeGroups[0]->id)->orderBy('sort_order')->get();
        foreach ($clothingSizes as $size) {
            ProductAttributeValue::firstOrCreate(
                ['attribute_id' => $sizeAttribute->id, 'value' => $size->value],
                [
                    'slug' => $size->slug,
                    'size_id' => $size->id,
                    'sort_order' => $size->sort_order,
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

    /** Streetwear demo catalogue; each product has a matching local photo in storage/app/public/demo (see CREDITS.md). */
    private function seedProducts(array $categories, array $brands, array $collections, array $tags): array
    {
        $category = collect($categories)->keyBy('slug');
        $brand = collect($brands)->keyBy('slug');
        $collection = collect($collections)->keyBy('slug');
        $tag = collect($tags)->keyBy('slug');

        $productsData = [
            [
                'name' => 'Graphic Hoodie',
                'slug' => 'graphic-hoodie',
                'sku' => 'HOD-001',
                'description' => 'Heavyweight black hoodie with a hand-drawn graphic on the chest. Boxy fit, brushed fleece inside.',
                'short_description' => 'Heavyweight hoodie with chest graphic',
                'category_id' => $category['men-hoodies']->id,
                'brand_id' => $brand['concrete-club']->id,
                'price' => 79.00,
                'compare_at_price' => null,
                'cost_price' => 31.00,
                'quantity' => 60,
                'low_stock_threshold' => 8,
                'material' => '80% Cotton, 20% Polyester',
                'gender' => 'unisex',
                'season' => 'autumn',
                'is_active' => true,
                'is_featured' => true,
                'track_quantity' => true,
                'collection_ids' => [$collection['drop-01']->id, $collection['winter-layers']->id, $collection['after-dark']->id],
                'tag_ids' => [$tag['new-arrival']->id, $tag['best-seller']->id],
                'images' => ['hoodie-1'],
            ],
            [
                'name' => 'Mustard Crewneck Sweatshirt',
                'slug' => 'mustard-crewneck-sweatshirt',
                'sku' => 'SWE-001',
                'description' => 'Relaxed crewneck in mustard with a small embroidered chest print. Pairs with everything baggy.',
                'short_description' => 'Relaxed mustard crewneck',
                'category_id' => $category['men-hoodies']->id,
                'brand_id' => $brand['paper-planes']->id,
                'price' => 64.00,
                'compare_at_price' => 79.00,
                'cost_price' => 24.00,
                'quantity' => 50,
                'low_stock_threshold' => 6,
                'material' => '100% Organic Cotton',
                'gender' => 'unisex',
                'season' => 'autumn',
                'is_active' => true,
                'is_featured' => true,
                'track_quantity' => true,
                'collection_ids' => [$collection['core-essentials']->id],
                'tag_ids' => [$tag['organic-cotton']->id],
                'images' => ['sweatshirt-1'],
            ],
            [
                'name' => 'Baggy Jeans Washed Blue',
                'slug' => 'baggy-jeans-washed-blue',
                'sku' => 'JEA-001',
                'description' => 'Wide-leg jeans with a faded light wash and a long break at the ankle. Rigid denim that softens with wear.',
                'short_description' => 'Wide-leg washed denim',
                'category_id' => $category['men-denim']->id,
                'brand_id' => $brand['low-tide']->id,
                'price' => 89.00,
                'compare_at_price' => null,
                'cost_price' => 35.00,
                'quantity' => 45,
                'low_stock_threshold' => 5,
                'material' => '100% Cotton denim',
                'gender' => 'men',
                'season' => 'all_season',
                'is_active' => true,
                'is_featured' => true,
                'track_quantity' => true,
                'collection_ids' => [$collection['denim-edit']->id, $collection['core-essentials']->id],
                'tag_ids' => [$tag['best-seller']->id],
                'images' => ['jeans-1', 'jeans-2'],
            ],
            [
                'name' => 'Wide-Leg Jeans Dark Wash',
                'slug' => 'wide-leg-jeans-dark-wash',
                'sku' => 'JEA-002',
                'description' => 'High-rise wide-leg jeans in a dark indigo wash. Relaxed through the hip and thigh, floor-skimming length.',
                'short_description' => 'High-rise wide-leg denim',
                'category_id' => $category['women-denim']->id,
                'brand_id' => $brand['alley-cat']->id,
                'price' => 92.00,
                'compare_at_price' => 115.00,
                'cost_price' => 38.00,
                'quantity' => 40,
                'low_stock_threshold' => 5,
                'material' => '100% Cotton denim',
                'gender' => 'women',
                'season' => 'all_season',
                'is_active' => true,
                'is_featured' => false,
                'track_quantity' => true,
                'collection_ids' => [$collection['denim-edit']->id, $collection['drop-01']->id],
                'tag_ids' => [$tag['new-arrival']->id],
                'images' => ['jeans-wide-1'],
            ],
            [
                'name' => 'Boxy Logo Tee',
                'slug' => 'boxy-logo-tee',
                'sku' => 'TSH-001',
                'description' => 'Boxy black tee in heavy cotton jersey with a small circular print on the chest.',
                'short_description' => 'Boxy heavy cotton tee',
                'category_id' => $category['men-tees']->id,
                'brand_id' => $brand['sennitwo']->id,
                'price' => 34.00,
                'compare_at_price' => null,
                'cost_price' => 11.00,
                'quantity' => 120,
                'low_stock_threshold' => 15,
                'material' => '100% Cotton',
                'gender' => 'unisex',
                'season' => 'all_season',
                'is_active' => true,
                'is_featured' => true,
                'track_quantity' => true,
                'collection_ids' => [$collection['core-essentials']->id, $collection['after-dark']->id],
                'tag_ids' => [$tag['best-seller']->id, $tag['organic-cotton']->id],
                'images' => ['tee-1'],
            ],
            [
                'name' => 'Hooded Coach Jacket',
                'slug' => 'hooded-coach-jacket',
                'sku' => 'JAC-001',
                'description' => 'Lightweight coach jacket with a contrast hood and back print. Water-resistant shell, snap front.',
                'short_description' => 'Coach jacket with contrast hood',
                'category_id' => $category['men-jackets']->id,
                'brand_id' => $brand['night-shift']->id,
                'price' => 119.00,
                'compare_at_price' => 149.00,
                'cost_price' => 52.00,
                'quantity' => 35,
                'low_stock_threshold' => 5,
                'material' => 'Nylon shell, polyester lining',
                'gender' => 'unisex',
                'season' => 'autumn',
                'is_active' => true,
                'is_featured' => true,
                'track_quantity' => true,
                'collection_ids' => [$collection['winter-layers']->id, $collection['after-dark']->id],
                'tag_ids' => [$tag['waterproof']->id, $tag['limited-edition']->id],
                'images' => ['jacket-1'],
            ],
            [
                'name' => 'Printed Overshirt',
                'slug' => 'printed-overshirt',
                'sku' => 'OVS-001',
                'description' => 'Oversized overshirt with an all-over print, worn open over a cropped top. Soft drape, dropped shoulders.',
                'short_description' => 'Oversized printed overshirt',
                'category_id' => $category['women-outerwear']->id,
                'brand_id' => $brand['overgrown']->id,
                'price' => 98.00,
                'compare_at_price' => null,
                'cost_price' => 41.00,
                'quantity' => 30,
                'low_stock_threshold' => 4,
                'material' => '100% Viscose',
                'gender' => 'women',
                'season' => 'autumn',
                'is_active' => true,
                'is_featured' => false,
                'track_quantity' => true,
                'collection_ids' => [$collection['drop-01']->id],
                'tag_ids' => [$tag['new-arrival']->id, $tag['limited-edition']->id],
                'images' => ['overshirt-1'],
            ],
        ];

        $products = [];
        foreach ($productsData as $productData) {
            $collectionIds = $productData['collection_ids'];
            $tagIds = $productData['tag_ids'];
            $images = $productData['images'];
            unset($productData['collection_ids'], $productData['tag_ids'], $productData['images']);

            $product = Product::firstOrCreate(['sku' => $productData['sku']], $productData);
            $product->collections()->sync($collectionIds);
            $product->tags()->sync($tagIds);

            // Photos are local-only; products simply have no image when the files are not there.
            foreach ($images as $position => $file) {
                if ($path = DemoPhoto::path($file)) {
                    $product->images()->firstOrCreate(
                        ['path' => $path],
                        ['alt_text' => $product->name, 'sort_order' => $position + 1, 'is_primary' => $position === 0],
                    );
                }
            }

            $products[] = $product;
        }

        return $products;
    }

    private function seedProductVariants(array $products, array $attributes, array $sizeGroups): void
    {
        $colorValues = ProductAttributeValue::where('attribute_id', $attributes['color']->id)->get();
        $sizeValues = ProductAttributeValue::where('attribute_id', $attributes['size']->id)->get();

        // Colourways that make sense for each product; sizes are the usual clothing run.
        $colorways = [
            'HOD-001' => ['black', 'grey'], 'SWE-001' => ['beige', 'black'], 'JEA-001' => ['blue', 'grey'],
            'JEA-002' => ['navy', 'black'], 'TSH-001' => ['black', 'white'], 'JAC-001' => ['black', 'navy'],
            'OVS-001' => ['green', 'brown'],
        ];

        foreach ($products as $product) {
            $selectedColors = $colorValues->whereIn('slug', $colorways[$product->sku] ?? ['black']);
            $selectedSizes = $sizeValues->whereIn('slug', ['s', 'm', 'l', 'xl']);

            foreach ($selectedColors as $color) {
                foreach ($selectedSizes as $size) {
                    $sku = $product->sku.'-'.strtoupper($color->slug).'-'.$size->value;

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
