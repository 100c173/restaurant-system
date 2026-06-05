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
        $this->service->checkout($request->validated());
        return self::success();
    }


}
