<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Color;
use App\Models\HomepageBlock;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Size;
use App\Models\Tag;
use App\Support\MediaUrl;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// shortcut: explicit field whitelists instead of one Resource class per model; extract Resources if these shapes grow.
class ContentController extends Controller
{
    public function banners(Request $request): JsonResponse
    {
        $banners = Banner::active()->current()->ordered()
            ->when($request->query('position'), fn ($q, $position) => $q->byPosition($position))
            ->get()
            ->map(fn (Banner $banner) => array_merge(
                $banner->only(['id', 'title', 'subtitle', 'description', 'link_url', 'link_text', 'position']),
                ['image' => MediaUrl::public($banner->image), 'mobile_image' => MediaUrl::public($banner->mobile_image)],
            ));

        return response()->json(['data' => $banners]);
    }

    public function homepage(): JsonResponse
    {
        $blocks = HomepageBlock::active()->current()->ordered()->get()
            ->map->only(['id', 'type', 'title', 'settings']);

        return response()->json(['data' => $blocks]);
    }

    public function menus(): JsonResponse
    {
        $menus = Menu::active()->with('items')->get()->map(fn (Menu $menu) => [
            'name' => $menu->name,
            'slug' => $menu->slug,
            'location' => $menu->location,
            'items' => $this->menuItems($menu->items),
        ]);

        return response()->json(['data' => $menus]);
    }

    public function pages(): JsonResponse
    {
        $pages = Page::active()->published()->orderBy('title')->get(['title', 'slug']);

        return response()->json(['data' => $pages]);
    }

    public function page(string $slug): JsonResponse
    {
        $page = Page::active()->published()->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $page->only(['title', 'slug', 'content', 'meta_title', 'meta_description', 'template', 'published_at'])]);
    }

    public function tags(): JsonResponse
    {
        return response()->json(['data' => Tag::active()->ordered()->get(['id', 'name', 'slug', 'color'])]);
    }

    public function colors(): JsonResponse
    {
        return response()->json(['data' => Color::active()->ordered()->get(['id', 'name', 'slug', 'hex', 'image'])]);
    }

    public function sizes(): JsonResponse
    {
        return response()->json(['data' => Size::active()->ordered()->get(['id', 'size_group_id', 'name', 'slug', 'value'])]);
    }

    public function config(): JsonResponse
    {
        return response()->json(['data' => [
            'name' => config('app.name'),
            'currency' => config('shop.currency'),
            'tax_rate' => (float) config('shop.tax_rate'),
            'free_shipping_threshold' => (float) config('shop.free_shipping_threshold'),
            'shipping_cost' => (float) config('shop.shipping_cost'),
        ]]);
    }

    private function menuItems($items): array
    {
        return $items->where('is_active', true)->map(fn ($item) => [
            'id' => $item->id,
            'label' => $item->label,
            'url' => $item->url,
            'open_in_new_tab' => $item->open_in_new_tab,
            'children' => $this->menuItems($item->children),
        ])->values()->all();
    }
}
