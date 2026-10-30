<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\RegisterRequest;
use App\Http\Requests\Api\StoreAddressRequest;
use App\Http\Requests\Api\UpdateAddressRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Resources\Api\AddressResource;
use App\Http\Resources\Api\UserResource;
use App\Models\User;
use App\Services\Cart\CartService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Sends the verification email; buying stays blocked until the link is opened.
        event(new Registered($user));

        $token = $user->createToken('api-token')->plainTextToken;
        app(CartService::class)->mergeGuestCartToUser($user->id);

        return response()->json([
            'user' => (new UserResource($user))->resolve(),
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = Auth::user();
        $token = $user->createToken('api-token')->plainTextToken;
        app(CartService::class)->mergeGuestCartToUser($user->id);

        return response()->json([
            'user' => (new UserResource($user))->resolve(),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => (new UserResource($request->user()->load('addresses')))->resolve(),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($request->has('email')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'message' => 'Current password is incorrect',
                    'errors' => ['current_password' => ['The provided password does not match your current password.']],
                ], 422);
            }
        }

        $user->fill($request->only(['name', 'email', 'password']));
        $emailChanged = $user->isDirty('email');
        if ($emailChanged) {
            $user->email_verified_at = null;
        }
        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'user' => (new UserResource($user))->resolve(),
        ]);
    }

    public function addresses(Request $request): JsonResponse
    {
        $user = $request->user();

        $addresses = $user->addresses()
            ->orderByDesc('is_default')
            ->get();

        return AddressResource::collection($addresses)->response();
    }

    public function storeAddress(StoreAddressRequest $request): JsonResponse
    {
        $user = $request->user();

        if ($request->boolean('is_default')) {
            $user->addresses()
                ->where('type', $request->type)
                ->update(['is_default' => false]);
        }

        $address = $user->addresses()->create($request->validated());

        return (new AddressResource($address))->response()->setStatusCode(201);
    }

    public function updateAddress(UpdateAddressRequest $request, int $id): JsonResponse
    {
        $user = $request->user();
        $address = $user->addresses()->findOrFail($id);

        if ($request->boolean('is_default')) {
            $user->addresses()
                ->where('type', $request->type ?? $address->type)
                ->where('id', '!=', $id)
                ->update(['is_default' => false]);
        }

        $address->update($request->validated());

        return (new AddressResource($address))->response();
    }

    public function deleteAddress(int $id): JsonResponse
    {
        $address = Auth::user()->addresses()->findOrFail($id);
        $address->delete();

        return response()->json([
            'message' => 'Address deleted successfully',
        ]);
    }
}
