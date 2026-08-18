<?php
namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            'order_id'         => $order->id,
            'reference_number' => $order->reference_number,
            'tenant_id'        => $order->tenant_id,
            'status'           => $order->status,
            'total'            => $order->total,
            'placed_at'        => $order->placed_at,
        ];
        return self::success($data, 'تم تأكيد طلبك بنجاح');
    }

    public function checkout(CheckoutRequest $request)
    {
        $this->service->checkout($request);
        return self::success('Payment process completed successfully');

    }

    public function orderCost($reference_number)
    {

        [$teantOrder, $restaurant] = $this->service->getorderCost($reference_number);
        return self::success([
            'order_info' => [
                'restaurant_id'             => $restaurant->id,
                'subtotal'                  => $teantOrder->subtotal,
                'total'                     => $teantOrder->total,
                'delivery_fee'              => $teantOrder->delivery_cost,
                'type'                      => $teantOrder->type,
                'delivery_address'          => $teantOrder->delivery_address,
                'sham_cash_account_barcode' => $restaurant->sham_cach_account_barcode,
                'sham_cach_account_id'      => $restaurant->sham_cach_account_id,
            ],
        ]);
    }

    public function cancelOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference_number' => [
                'required',
                'string',
                'exists:orders,reference_number',
            ],
        ]);

        $validator->validate();

        $referenceNumber = $request->input('reference_number');

        $this->service->cancelOrder($referenceNumber);

        return self::success(
            "order {$referenceNumber} cancelled successfully"
        );
    }

    public function orderDetails($reference_number)
    {
        $order_details = $this->service->getOrderDetails($reference_number);
        return self::success($order_details);
    }

}
