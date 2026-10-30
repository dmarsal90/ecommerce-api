<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\HomepageBlock;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding content data...');

        $this->seedBanners();
        $this->seedPages();
        $this->seedMenus();
        $this->seedHomepageBlocks();

        $this->command->info('Content seeding completed!');
    }

    private function seedBanners(): void
    {
        $banners = [
            [
                'title' => 'Spring Sale - Up to 50% Off',
                'subtitle' => 'Refresh your wardrobe with amazing deals',
                'image' => DemoPhoto::path('sweatshirt-1'),
                'link_url' => '/collections/spring-2024',
                'link_text' => 'Shop Now',
                'position' => 'homepage_hero',
                'is_active' => true,
                'sort_order' => 1,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonths(3)->format('Y-m-d'),
            ],
            [
                'title' => 'New Arrivals',
                'subtitle' => 'Check out our latest collection',
                'image' => DemoPhoto::path('jacket-1'),
                'link_url' => '/new-arrivals',
                'link_text' => 'View Collection',
                'position' => 'homepage_hero',
                'is_active' => true,
                'sort_order' => 2,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonths(2)->format('Y-m-d'),
            ],
            [
                'title' => 'Free Shipping on Orders $50+',
                'subtitle' => 'Limited time offer',
                'image' => null,
                'link_url' => null,
                'link_text' => null,
                'position' => 'homepage_section',
                'is_active' => true,
                'sort_order' => 1,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonths(6)->format('Y-m-d'),
            ],
            [
                'title' => 'Core Essentials',
                'subtitle' => 'Pieces you wear on repeat',
                'image' => DemoPhoto::path('tee-1'),
                'link_url' => '/collections/core-essentials',
                'link_text' => 'Shop Essentials',
                'position' => 'category_top',
                'is_active' => true,
                'sort_order' => 1,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonths(4)->format('Y-m-d'),
            ],
            [
                'title' => 'Newsletter Signup',
                'subtitle' => 'Get 10% off your first order',
                'image' => null,
                'link_url' => '/newsletter',
                'link_text' => 'Subscribe',
                'position' => 'product_page',
                'is_active' => true,
                'sort_order' => 1,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
            ],
        ];

        foreach ($banners as $banner) {
            Banner::firstOrCreate(['slug' => Str::slug($banner['title'])], $banner);
        }
    }

    private function seedPages(): void
    {
        $pages = [
            [
                'title' => 'About Us',
                'slug' => 'about-us',
                'content' => '<h2>About Our Store</h2><p>We are a leading online fashion retailer...</p>',
                'meta_title' => 'About Us | Fashion Store',
                'meta_description' => 'Learn more about our fashion store and mission.',
                'template' => 'default',
                'is_active' => true,
            ],
            [
                'title' => 'Contact Us',
                'slug' => 'contact-us',
                'content' => '<h2>Contact Us</h2><p>Get in touch with our team...</p>',
                'meta_title' => 'Contact Us | Fashion Store',
                'meta_description' => 'Contact our customer service team.',
                'template' => 'contact',
                'is_active' => true,
            ],
            [
                'title' => 'Shipping & Returns',
                'slug' => 'shipping-returns',
                'content' => '<h2>Shipping & Returns Policy</h2><p>Free shipping on orders over $50...</p>',
                'meta_title' => 'Shipping & Returns | Fashion Store',
                'meta_description' => 'Our shipping and returns policy.',
                'template' => 'default',
                'is_active' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy-policy',
                'content' => '<h2>Privacy Policy</h2><p>We respect your privacy...</p>',
                'meta_title' => 'Privacy Policy | Fashion Store',
                'meta_description' => 'Our privacy policy.',
                'template' => 'default',
                'is_active' => true,
            ],
            [
                'title' => 'Terms of Service',
                'slug' => 'terms-of-service',
                'content' => '<h2>Terms of Service</h2><p>By using our website, you agree...</p>',
                'meta_title' => 'Terms of Service | Fashion Store',
                'meta_description' => 'Terms and conditions of use.',
                'template' => 'default',
                'is_active' => true,
            ],
            [
                'title' => 'FAQ',
                'slug' => 'faq',
                'content' => '<h2>Frequently Asked Questions</h2><p>Find answers to common questions...</p>',
                'meta_title' => 'FAQ | Fashion Store',
                'meta_description' => 'Frequently asked questions.',
                'template' => 'faq',
                'is_active' => true,
            ],
            [
                'title' => 'Size Guide',
                'slug' => 'size-guide',
                'content' => '<h2>Size Guide</h2><p>Find your perfect fit...</p>',
                'meta_title' => 'Size Guide | Fashion Store',
                'meta_description' => 'Clothing size guide for men and women.',
                'template' => 'size-guide',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(['slug' => $page['slug']], $page);
        }
    }

    private function seedMenus(): void
    {
        // Main navigation menu
        $mainMenu = Menu::firstOrCreate(['slug' => 'main-navigation'], [
            'name' => 'Main Navigation',
            'slug' => 'main-navigation',
            'location' => 'header',
            'is_active' => true,
        ]);

        $mainMenuItems = [
            ['label' => 'Home', 'url' => '/', 'target' => '_self', 'sort_order' => 1],
            ['label' => 'Men', 'url' => '/men', 'target' => '_self', 'sort_order' => 2],
            ['label' => 'Women', 'url' => '/women', 'target' => '_self', 'sort_order' => 3],
            ['label' => 'Kids', 'url' => '/kids', 'target' => '_self', 'sort_order' => 4],
            ['label' => 'Accessories', 'url' => '/accessories', 'target' => '_self', 'sort_order' => 5],
            ['label' => 'Sale', 'url' => '/sale', 'target' => '_self', 'sort_order' => 6],
            ['label' => 'New Arrivals', 'url' => '/new-arrivals', 'target' => '_self', 'sort_order' => 7],
        ];

        foreach ($mainMenuItems as $index => $item) {
            MenuItem::firstOrCreate(
                ['menu_id' => $mainMenu->id, 'sort_order' => $item['sort_order']],
                ['menu_id' => $mainMenu->id] + $item
            );
        }

        // Footer menu
        $footerMenu = Menu::firstOrCreate(['slug' => 'footer-links'], [
            'name' => 'Footer Links',
            'slug' => 'footer-links',
            'location' => 'footer',
            'is_active' => true,
        ]);

        $footerMenuItems = [
            ['label' => 'About Us', 'url' => '/about-us', 'target' => '_self', 'sort_order' => 1],
            ['label' => 'Contact', 'url' => '/contact-us', 'target' => '_self', 'sort_order' => 2],
            ['label' => 'Shipping & Returns', 'url' => '/shipping-returns', 'target' => '_self', 'sort_order' => 3],
            ['label' => 'Privacy Policy', 'url' => '/privacy-policy', 'target' => '_self', 'sort_order' => 4],
            ['label' => 'Terms of Service', 'url' => '/terms-of-service', 'target' => '_self', 'sort_order' => 5],
            ['label' => 'FAQ', 'url' => '/faq', 'target' => '_self', 'sort_order' => 6],
            ['label' => 'Size Guide', 'url' => '/size-guide', 'target' => '_self', 'sort_order' => 7],
        ];

        foreach ($footerMenuItems as $item) {
            MenuItem::firstOrCreate(
                ['menu_id' => $footerMenu->id, 'sort_order' => $item['sort_order']],
                ['menu_id' => $footerMenu->id] + $item
            );
        }

        // Account menu
        $accountMenu = Menu::firstOrCreate(['slug' => 'account-menu'], [
            'name' => 'Account Menu',
            'slug' => 'account-menu',
            'location' => 'account',
            'is_active' => true,
        ]);

        $accountMenuItems = [
            ['label' => 'My Account', 'url' => '/account', 'target' => '_self', 'sort_order' => 1],
            ['label' => 'Orders', 'url' => '/account/orders', 'target' => '_self', 'sort_order' => 2],
            ['label' => 'Addresses', 'url' => '/account/addresses', 'target' => '_self', 'sort_order' => 3],
            ['label' => 'Wishlist', 'url' => '/account/wishlist', 'target' => '_self', 'sort_order' => 4],
            ['label' => 'Settings', 'url' => '/account/settings', 'target' => '_self', 'sort_order' => 5],
        ];

        foreach ($accountMenuItems as $item) {
            MenuItem::firstOrCreate(
                ['menu_id' => $accountMenu->id, 'sort_order' => $item['sort_order']],
                ['menu_id' => $accountMenu->id] + $item
            );
        }
    }

    private function seedHomepageBlocks(): void
    {
        $blocks = [
            [
                'type' => 'hero_slider',
                'title' => 'Hero Slider',
                'settings' => [
                    'slides' => [
                        ['image' => 'hero/slide-1.jpg', 'title' => 'Spring Collection', 'subtitle' => 'Up to 50% off', 'cta_text' => 'Shop Now', 'cta_url' => '/collections/spring-2024'],
                        ['image' => 'hero/slide-2.jpg', 'title' => 'New Arrivals', 'subtitle' => 'Fresh styles weekly', 'cta_text' => 'View All', 'cta_url' => '/new-arrivals'],
                        ['image' => 'hero/slide-3.jpg', 'title' => 'Free Shipping', 'subtitle' => 'On orders $50+', 'cta_text' => 'Learn More', 'cta_url' => '/shipping-returns'],
                    ],
                    'autoplay' => true,
                    'interval' => 5000,
                ],
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'type' => 'featured_categories',
                'title' => 'Shop by Category',
                'settings' => [
                    'categories' => ['men', 'women', 'kids', 'accessories'],
                    'show_images' => true,
                    'columns' => 4,
                ],
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'type' => 'featured_products',
                'title' => 'Featured Products',
                'settings' => [
                    'product_limit' => 8,
                    'show_price' => true,
                    'show_rating' => true,
                    'layout' => 'grid',
                ],
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'type' => 'banner_grid',
                'title' => 'Promotional Banners',
                'settings' => [
                    'banners' => [
                        ['image' => 'promo/promo-1.jpg', 'title' => 'Men\'s Sale', 'url' => '/men?sale=true'],
                        ['image' => 'promo/promo-2.jpg', 'title' => 'Women\'s Sale', 'url' => '/women?sale=true'],
                        ['image' => 'promo/promo-3.jpg', 'title' => 'Accessories', 'url' => '/accessories'],
                    ],
                    'columns' => 3,
                ],
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'type' => 'newsletter_signup',
                'title' => 'Newsletter Signup',
                'settings' => [
                    'title' => 'Join Our Newsletter',
                    'subtitle' => 'Get 10% off your first order',
                    'button_text' => 'Subscribe',
                    'background_color' => '#f5f5f5',
                ],
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'type' => 'brand_showcase',
                'title' => 'Our Brands',
                'settings' => [
                    'brands' => ['sennitwo', 'concrete-club', 'night-shift', 'low-tide', 'alley-cat', 'paper-planes'],
                    'scrollable' => true,
                ],
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'type' => 'instagram_feed',
                'title' => 'Instagram Feed',
                'settings' => [
                    'username' => 'fashionstore',
                    'post_count' => 6,
                    'show_follow_button' => true,
                ],
                'sort_order' => 7,
                'is_active' => true,
            ],
        ];

        foreach ($blocks as $block) {
            HomepageBlock::firstOrCreate(
                ['type' => $block['type'], 'sort_order' => $block['sort_order']],
                $block
            );
        }
    }
}
