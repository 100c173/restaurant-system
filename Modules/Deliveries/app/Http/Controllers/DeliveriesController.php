<?php

namespace Modules\Deliveries\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Deliveries\Http\Requests\DeliveryRequest;
use Modules\Deliveries\Http\Requests\SendOtpRequest;
use Modules\Deliveries\Http\Requests\VerifyOtpRequest;
use Modules\Deliveries\Services\DeliveryService;

class DeliveriesController extends Controller
{
    public function __construct(private DeliveryService $service)
    {
    }

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $expiresAt = $this->service->sendOtp($request->validated());

        return response()->json([
            'message' => 'OTP sent successfully',
            'expires_at' => $expiresAt->toDateTimeString(),
        ]);

    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $deliveryRequest = $this->service->verifyOtp($request->validated());

        if (!$deliveryRequest) {
            return response()->json(['error' => 'Invalid or expired OTP'], 400);
        }
        return response()->json(['message' => 'OTP verified successfully']);
    }

    public function DeliveryRequest(DeliveryRequest $request): JsonResponse
    {

        $result = $this->service->saveDeliveryRequest($request);
        return response()->json([
            "message" => "your request saved successfully",
            "data" => $result,
        ]);

    }
}
