<?php

namespace Modules\Restaurants\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Restaurants\Services\MenuItemService;

class MenuItemController extends Controller
{
    public function __construct(private MenuItemService $service){}

    public function MenuItems(string $id): JsonResponse{
        try{
            $menu = $this->service->getMenuItems($id);
            return response()->json($menu);
        }catch(\Exception $e){
              return response()->json(["error"=> $e->getMessage()]);
        }
    }
}
