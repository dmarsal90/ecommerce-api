<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding orders and related data...');

        $this->seedUsers();
        $this->seedInventory();
        $this->seedOrders();
        $this->seedReviews();
        $this->seedStockMovements();

        $this->command->info('Order seeding completed!');
    }

    private function seedUsers(): void
    {
        $users = [
            [
                'email' => 'john.doe@example.com',
                'name' => 'John Doe',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
            [
                'email' => 'jane.smith@example.com',
                'name' => 'Jane Smith',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
            [
                'email' => 'mike.wilson@example.com',
                'name' => 'Mike Wilson',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
            [
                'email' => 'sarah.johnson@example.com',
                'name' => 'Sarah Johnson',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
            [
                'email' => 'david.brown@example.com',
                'name' => 'David Brown',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(['email' => $userData['email']], $userData);
        }
    }

    private function seedInventory(): void
    {
        $products = Product::with('variants')->get();

        foreach ($products as $product) {
            // Main product inventory
            Inventory::firstOrCreate(
                ['product_id' => $product->id, 'variant_id' => null],
                [
                    'sku' => $product->sku,
                    'quantity' => $product->quantity,
                    'reserved' => 0,
                    'available' => $product->quantity,
                    'low_stock_threshold' => $product->low_stock_threshold,
                    'location' => 'A-' . rand(1, 10) . '-' . rand(1, 20),
                    'track_quantity' => true,
                ]
            );

            // Variant inventories
            foreach ($product->variants as $variant) {
                Inventory::firstOrCreate(
                    ['product_id' => $product->id, 'variant_id' => $variant->id],
                    [
                        'sku' => $variant->sku,
                        'quantity' => $variant->quantity,
                        'reserved' => 0,
                        'available' => $variant->quantity,
                        'low_stock_threshold' => $variant->low_stock_threshold,
                        'location' => 'B-' . rand(1, 10) . '-' . rand(1, 20),
                        'track_quantity' => true,
                    ]
                );
            }
        }
    }

    private function seedOrders(): void
    {
        $users = User::where('email', '!=', 'admin@example.com')->where('email', '!=', 'test@example.com')->where('email', '!=', 'customer@test.com')->get();
        $products = Product::with('variants')->get();
        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
        $fulfillmentStatuses = ['unfulfilled', 'partial', 'fulfilled'];

        $addressesByUser = [
            'john.doe@example.com' => [
                ['type' => 'billing', 'first_name' => 'John', 'last_name' => 'Doe', 'company' => '', 'address_line_1' => '123 Main St', 'address_line_2' => 'Apt 4B', 'city' => 'New York', 'state' => 'NY', 'postal_code' => '10001', 'country' => 'US', 'phone' => '+1-555-0101'],
                ['type' => 'shipping', 'first_name' => 'John', 'last_name' => 'Doe', 'company' => '', 'address_line_1' => '123 Main St', 'address_line_2' => 'Apt 4B', 'city' => 'New York', 'state' => 'NY', 'postal_code' => '10001', 'country' => 'US', 'phone' => '+1-555-0101'],
            ],
            'jane.smith@example.com' => [
                ['type' => 'billing', 'first_name' => 'Jane', 'last_name' => 'Smith', 'company' => '', 'address_line_1' => '456 Oak Ave', 'address_line_2' => '', 'city' => 'Los Angeles', 'state' => 'CA', 'postal_code' => '90001', 'country' => 'US', 'phone' => '+1-555-0102'],
                ['type' => 'shipping', 'first_name' => 'Jane', 'last_name' => 'Smith', 'company' => '', 'address_line_1' => '456 Oak Ave', 'address_line_2' => '', 'city' => 'Los Angeles', 'state' => 'CA', 'postal_code' => '90001', 'country' => 'US', 'phone' => '+1-555-0102'],
            ],
            'mike.wilson@example.com' => [
                ['type' => 'billing', 'first_name' => 'Mike', 'last_name' => 'Wilson', 'company' => '', 'address_line_1' => '789 Pine Rd', 'address_line_2' => '', 'city' => 'Chicago', 'state' => 'IL', 'postal_code' => '60601', 'country' => 'US', 'phone' => '+1-555-0103'],
                ['type' => 'shipping', 'first_name' => 'Mike', 'last_name' => 'Wilson', 'company' => '', 'address_line_1' => '789 Pine Rd', 'address_line_2' => '', 'city' => 'Chicago', 'state' => 'IL', 'postal_code' => '60601', 'country' => 'US', 'phone' => '+1-555-0103'],
            ],
            'sarah.johnson@example.com' => [
                ['type' => 'billing', 'first_name' => 'Sarah', 'last_name' => 'Johnson', 'company' => '', 'address_line_1' => '321 Elm St', 'address_line_2' => 'Suite 200', 'city' => 'Houston', 'state' => 'TX', 'postal_code' => '77001', 'country' => 'US', 'phone' => '+1-555-0104'],
                ['type' => 'shipping', 'first_name' => 'Sarah', 'last_name' => 'Johnson', 'company' => '', 'address_line_1' => '321 Elm St', 'address_line_2' => 'Suite 200', 'city' => 'Houston', 'state' => 'TX', 'postal_code' => '77001', 'country' => 'US', 'phone' => '+1-555-0104'],
            ],
            'david.brown@example.com' => [
                ['type' => 'billing', 'first_name' => 'David', 'last_name' => 'Brown', 'company' => '', 'address_line_1' => '555 Cedar Blvd', 'address_line_2' => '', 'city' => 'Phoenix', 'state' => 'AZ', 'postal_code' => '85001', 'country' => 'US', 'phone' => '+1-555-0105'],
                ['type' => 'shipping', 'first_name' => 'David', 'last_name' => 'Brown', 'company' => '', 'address_line_1' => '555 Cedar Blvd', 'address_line_2' => '', 'city' => 'Phoenix', 'state' => 'AZ', 'postal_code' => '85001', 'country' => 'US', 'phone' => '+1-555-0105'],
            ],
        ];

        for ($i = 0; $i < 20; $i++) {
            $user = $users->random();
            $status = $statuses[array_rand($statuses)];
            $paymentStatus = $paymentStatuses[array_rand($paymentStatuses)];
            $fulfillmentStatus = $fulfillmentStatuses[array_rand($fulfillmentStatuses)];

            // Pick 1-3 products
            $orderProducts = $products->random(rand(1, 3));
            $subtotal = 0;
            $items = [];

            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $useVariant = $product->variants->isNotEmpty() && rand(0, 1);
                
                if ($useVariant) {
                    $variant = $product->variants->random();
                    $price = $variant->price;
                    $items[] = [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'compare_at_price' => $variant->compare_at_price,
                        'total' => $price * $quantity,
                        'product_name' => $product->name,
                        'variant_name' => $variant->sku,
                        'sku' => $variant->sku,
                    ];
                } else {
                    $price = $product->price;
                    $items[] = [
                        'product_id' => $product->id,
                        'variant_id' => null,
                        'quantity' => $quantity,
                        'price' => $price,
                        'compare_at_price' => $product->compare_at_price,
                        'total' => $price * $quantity,
                        'product_name' => $product->name,
                        'variant_name' => null,
                        'sku' => $product->sku,
                    ];
                }
                $subtotal += $price * $quantity;
            }

            $tax = round($subtotal * 0.08, 2);
            $shipping = $subtotal > 50 ? 0 : 9.99;
            $discount = 0;
            $total = $subtotal + $tax + $shipping - $discount;

            $order = Order::create([
                'user_id' => $user->id,
                'order_number' => 'ORD-' . strtoupper(Str::random(8)),
                'email' => $user->email,
                'status' => $status,
                'payment_status' => $paymentStatus,
                'fulfillment_status' => $fulfillmentStatus,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shipping,
                'discount' => $discount,
                'total' => $total,
                'currency' => 'USD',
                'notes' => $i % 3 === 0 ? 'Customer requested gift wrapping' : null,
            ]);

            // Create addresses
            $userAddresses = $addressesByUser[$user->email] ?? $addressesByUser['john.doe@example.com'];
            foreach ($userAddresses as $addr) {
                OrderAddress::create(array_merge($addr, ['order_id' => $order->id]));
            }

            // Create order items
            foreach ($items as $item) {
                OrderItem::create(array_merge($item, ['order_id' => $order->id]));
            }
        }
    }

    private function seedReviews(): void
    {
        $users = User::where('email', '!=', 'admin@example.com')->where('email', '!=', 'test@example.com')->where('email', '!=', 'customer@test.com')->get();
        $products = Product::all();

        $reviews = [
            ['rating' => 5, 'title' => 'Excellent quality!', 'comment' => 'Love this product, great fit and material.', 'status' => 'approved'],
            ['rating' => 4, 'title' => 'Good value', 'comment' => 'Good product for the price, shipping was fast.', 'status' => 'approved'],
            ['rating' => 5, 'title' => 'Perfect!', 'comment' => 'Exactly what I was looking for. Highly recommend.', 'status' => 'approved'],
            ['rating' => 3, 'title' => 'Okay', 'comment' => 'It\'s okay, but runs a bit small. Order size up.', 'status' => 'approved'],
            ['rating' => 4, 'title' => 'Nice design', 'comment' => 'Beautiful design and comfortable to wear.', 'status' => 'approved'],
            ['rating' => 5, 'title' => 'Best purchase', 'comment' => 'Amazing quality, will buy again.', 'status' => 'approved'],
            ['rating' => 2, 'title' => 'Disappointed', 'comment' => 'Color doesn\'t match the photo.', 'status' => 'approved'],
            ['rating' => 4, 'title' => 'Great fit', 'comment' => 'Fits perfectly, material feels premium.', 'status' => 'approved'],
            ['rating' => 5, 'title' => 'Love it!', 'comment' => 'My new favorite item. So comfortable.', 'status' => 'approved'],
            ['rating' => 3, 'title' => 'Average', 'comment' => 'Nothing special but does the job.', 'status' => 'pending'],
        ];

        foreach ($products as $product) {
            $reviewCount = rand(1, 3);
            $selectedReviews = collect($reviews)->random($reviewCount);

            foreach ($selectedReviews as $reviewData) {
                Review::create(array_merge($reviewData, [
                    'product_id' => $product->id,
                    'user_id' => $users->random()->id,
                    'is_verified_purchase' => rand(0, 1),
                ]));
            }
        }
    }

    private function seedStockMovements(): void
    {
        $inventories = Inventory::all();
        $types = ['in', 'out', 'adjustment', 'transfer', 'return', 'damaged'];
        $referenceIds = [1001, 1002, 1003, 1004, 1005, 1006, 1007];

        for ($i = 0; $i < 50; $i++) {
            $inventory = $inventories->random();
            $type = $types[array_rand($types)];
            $quantity = rand(1, 50);
            $quantityBefore = $inventory->available;
            
            // Calculate quantity after based on type
            if (in_array($type, ['in', 'return'])) {
                $quantityAfter = $quantityBefore + $quantity;
            } elseif (in_array($type, ['out', 'damaged'])) {
                $quantityAfter = max(0, $quantityBefore - $quantity);
            } else {
                $quantityAfter = $quantityBefore + (rand(0, 1) ? $quantity : -$quantity);
                $quantityAfter = max(0, $quantityAfter);
            }

            StockMovement::create([
                'inventory_id' => $inventory->id,
                'type' => $type,
                'quantity' => $quantity,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_type' => $type === 'in' ? 'purchase_order' : ($type === 'out' ? 'sales_order' : 'adjustment'),
                'reference_id' => $referenceIds[array_rand($referenceIds)],
                'notes' => $type === 'adjustment' ? 'Cycle count adjustment' : null,
                'user_id' => 1,
            ]);
        }
    }
}