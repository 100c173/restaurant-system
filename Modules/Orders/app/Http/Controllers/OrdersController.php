<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Orders\Services\OrderService;

class OrdersController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function checkout()
    {
        $this->service->checkout();

        return response()->json([
            "message" => "Your order is created successfully",
        ]);
    }
}
