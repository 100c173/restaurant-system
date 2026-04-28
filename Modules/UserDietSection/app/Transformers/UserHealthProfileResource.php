<?php

namespace Modules\UserDietSection\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserHealthProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'gender' => $this->gender,
            'birth_date' => $this->birth_date ,

            'height_cm' => $this->height_cm,
            'weight_kg' => $this->weight_kg,

            'activity_level' => $this->activity_level,
            'goal' => $this->goal,

            'created_at' => $this->created_at,
        ];
    }
}
