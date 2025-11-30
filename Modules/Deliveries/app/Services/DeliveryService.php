<?php

namespace Modules\Deliveries\Services;

use Exception;
use Modules\Deliveries\Events\UserRequestedToBecomeDriver;
use Modules\Deliveries\Models\DeliveryRequest;

class DeliveryService
{
    public function saveDeliveryRequest($request) {

        $data = $request->validated();

        $pathPrefix = 'uploads/delivery/' . $request->user()->id;

        $data['personal_photo']  = $request->file('personal_photo')->store($pathPrefix, 'public');
        $data['id_card_front']   = $request->file('id_card_front')->store($pathPrefix, 'public');
        $data['id_card_back']    = $request->file('id_card_back')->store($pathPrefix, 'public');
        $data['driving_license'] = $request->file('driving_license')->store($pathPrefix, 'public');

        $data['user_id'] = $request->user()->id;


        $request = DeliveryRequest::create($data) ;

        UserRequestedToBecomeDriver::dispatch($request);

        return $request ;

    }

}
