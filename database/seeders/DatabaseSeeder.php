<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core users and permissions
        $this->call([
            PermissionSeeder::class,
            AdminUserSeeder::class,
        ]);

        // Catalog data (categories, brands, products, etc.)
        $this->call(CatalogSeeder::class);

        // Content (banners, pages, menus, homepage blocks)
        $this->call(ContentSeeder::class);

        $this->call(CouponSeeder::class);

        // Orders, customers, inventory, reviews
        $this->call(OrderSeeder::class);

        // Default test user (if not created by AdminUserSeeder)
        User::firstOrCreate([
            'email' => 'test@example.com',
        ], [
            'name' => 'Test User',
            'password' => bcrypt('password'),
        ]);
    }
}
