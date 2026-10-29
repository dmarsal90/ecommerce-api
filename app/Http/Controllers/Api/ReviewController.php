<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\ReviewResource;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $reviews = $product->reviews()->with('user')->latest()->paginate(10);

        return ReviewResource::collection($reviews)->response();
    }

    public function store(Request $request, string $slug): JsonResponse
    {
        $product = Product::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $user = $request->user();

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'title' => ['nullable', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        if (Review::where('product_id', $product->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You have already reviewed this product.'], 422);
        }

        $review = Review::create($data + [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'is_verified_purchase' => Order::where('user_id', $user->id)
                ->where('payment_status', 'paid')
                ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
                ->exists(),
        ]);

        return (new ReviewResource($review->load('user')))->response()->setStatusCode(201);
    }
}
