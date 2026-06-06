<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Orders\Http\Requests\CheckoutRequest;
use Modules\Orders\Services\OrderService;

class OrdersController extends Controller
{
    public function __construct(protected OrderService $service)
    {
    }
    public function store(CheckoutRequest $request)
    {
        $order = $this->service->checkout($request->validated());
        $data = [
            'order_id' => $order->id,
            'reference_number' => $order->reference_number,
            'status' => $order->status,
            'total' => $order->total,
            'placed_at' => $order->placed_at,
        ];
        return self::success($data, 'تم تأكيد طلبك بنجاح');
    }


}
