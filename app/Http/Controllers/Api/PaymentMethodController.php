<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePaymentMethodRequest;
use App\Http\Resources\Api\PaymentMethodResource;
use App\Services\Payment\PaymentMethodService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PaymentMethodController extends Controller
{
    public function __construct(private PaymentMethodService $service) {}

    public function index(Request $request): JsonResponse
    {
        $methods = $request->user()->paymentMethods()->orderByDesc('is_default')->latest()->get();

        return PaymentMethodResource::collection($methods)->response();
    }

    public function setupIntent(Request $request): JsonResponse
    {
        return response()->json(['client_secret' => $this->service->setupIntent($request->user())['client_secret']]);
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        try {
            $method = $this->service->store($request->user(), $request->validated('payment_method_id'));
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return (new PaymentMethodResource($method))->response()->setStatusCode(201);
    }

    public function setDefault(Request $request, int $id): JsonResponse
    {
        $method = $request->user()->paymentMethods()->findOrFail($id);

        return (new PaymentMethodResource($this->service->setDefault($request->user(), $method)))->response();
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->service->destroy($request->user(), $request->user()->paymentMethods()->findOrFail($id));

        return response()->json(['message' => 'Payment method removed']);
    }
}
