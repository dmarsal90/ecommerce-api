<?php

namespace Tests\Feature\Api;

use App\Models\Banner;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_banners_only_include_active_current_and_filter_by_position(): void
    {
        Banner::create(['title' => 'Hero', 'slug' => 'hero', 'position' => 'hero', 'is_active' => true]);
        Banner::create(['title' => 'Side', 'slug' => 'side', 'position' => 'sidebar', 'is_active' => true]);
        Banner::create(['title' => 'Off', 'slug' => 'off', 'position' => 'hero', 'is_active' => false]);
        Banner::create(['title' => 'Expired', 'slug' => 'expired', 'position' => 'hero', 'is_active' => true, 'end_date' => now()->subDay()]);

        $this->getJson('/api/banners')->assertOk()->assertJsonCount(2, 'data');
        $this->getJson('/api/banners?position=hero')->assertOk()
            ->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Hero');
    }

    public function test_menus_return_nested_active_items(): void
    {
        $menu = Menu::create(['name' => 'Main', 'slug' => 'main', 'location' => 'header', 'is_active' => true]);
        $parent = MenuItem::create(['menu_id' => $menu->id, 'label' => 'Shop', 'url' => '/shop', 'is_active' => true]);
        MenuItem::create(['menu_id' => $menu->id, 'parent_id' => $parent->id, 'label' => 'Shoes', 'url' => '/shoes', 'is_active' => true]);
        MenuItem::create(['menu_id' => $menu->id, 'parent_id' => $parent->id, 'label' => 'Hidden', 'url' => '/h', 'is_active' => false]);

        $this->getJson('/api/menus')->assertOk()
            ->assertJsonPath('data.0.items.0.label', 'Shop')
            ->assertJsonCount(1, 'data.0.items.0.children')
            ->assertJsonPath('data.0.items.0.children.0.label', 'Shoes');
    }

    public function test_pages_only_expose_published_active_pages(): void
    {
        Page::create(['title' => 'About', 'slug' => 'about', 'content' => 'Hi', 'is_active' => true, 'published_at' => now()->subDay()]);
        Page::create(['title' => 'Draft', 'slug' => 'draft', 'content' => 'x', 'is_active' => false]);

        $this->getJson('/api/pages')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/pages/about')->assertOk()->assertJsonPath('data.content', 'Hi');
        $this->getJson('/api/pages/draft')->assertNotFound();
    }

    public function test_config_exposes_shop_settings(): void
    {
        $this->getJson('/api/config')->assertOk()
            ->assertJsonStructure(['data' => ['name', 'currency', 'tax_rate', 'free_shipping_threshold', 'shipping_cost']]);
    }

    public function test_banner_images_are_urls_clients_can_load(): void
    {
        Banner::create(['title' => 'Hero', 'slug' => 'hero', 'position' => 'hero', 'is_active' => true, 'image' => 'demo/hero.jpg', 'mobile_image' => 'https://cdn.example/m.jpg']);
        Banner::create(['title' => 'Plain', 'slug' => 'plain', 'position' => 'hero', 'is_active' => true]);

        $banners = $this->getJson('/api/banners?position=hero')->assertOk()->json('data');

        $this->assertStringEndsWith('/storage/demo/hero.jpg', $banners[0]['image']);
        $this->assertSame('https://cdn.example/m.jpg', $banners[0]['mobile_image']);
        $this->assertNull($banners[1]['image']);
    }
}
