<?php

namespace Modules\Deliveries\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Modules\Deliveries\Http\Requests\DeliveryRequest;
use Modules\Deliveries\Services\DeliveryService;

class DeliveriesController extends Controller
{
    public function __construct(private DeliveryService $service){}

    public function DeliveryRequest(DeliveryRequest $request){
        try{
            $result = $this->service->saveDeliveryRequest($request) ;
            return response()->json([
                "message" => "your request saved successfully" ,
                "data"    => $result ,
            ]);
        }catch(Exception $e){
            return response()->json(["message"=>$e->getMessage()]);
        }
    }
}
