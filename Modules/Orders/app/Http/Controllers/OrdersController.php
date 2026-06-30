<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Orders\Http\Requests\CheckoutRequest;
use Modules\Orders\Http\Requests\ConfirmeRequest;
use Modules\Orders\Services\OrderService;

class OrdersController extends Controller
{
    public function __construct(protected OrderService $service)
    {
    }
    public function store(ConfirmeRequest $request)
    {
        $data = $request->validated();

        $order = $this->service->confirme($data);

        $data = [
            'order_id' => $order->id,
            'reference_number' => $order->reference_number,
            'tenant_id' => $order->tenant_id,
            'status' => $order->status,
            'total' => $order->total,
            'placed_at' => $order->placed_at,
        ];
        return self::success($data, 'تم تأكيد طلبك بنجاح');
    }

    public function checkout(CheckoutRequest $request)
    {
        $this->service->checkout($request);
        return self::success('Payment process completed successfully');

    }

    public function deliveryCost($reference_number)
    {

        $delivery_cost = $this->service->getDeliverCost($reference_number);
        return self::success([
            'delivery_cost' => $delivery_cost,
        ]);
    }

    public function cancelOrder($reference_number)
    {
        $this->service->cancelOrder($reference_number);
        return self::success("order $reference_number cancelled successful");
    }


}
