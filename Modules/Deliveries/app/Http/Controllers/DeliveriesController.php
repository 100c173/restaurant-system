<?php

namespace Modules\Deliveries\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Deliveries\Http\Requests\DeliveryRequest;
use Modules\Deliveries\Services\DeliveryService;

class DeliveriesController extends Controller
{
    public function __construct(private DeliveryService $service)
    {
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
