<?php

namespace Modules\Deliveries\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\Deliveries\Database\Factories\DeliveryRequestFactory;

class DeliveryRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
        protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone_number',
        'national_id',
        'city',
        'address',
        'personal_photo',
        'id_card_front',
        'id_card_back',
        'driving_license',
        'vehicle_type',
        'vehicle_number',
        'status',
        'experience_note',
        'cancel_reason',
        'admin_note',
    ];

    public function customer():BelongsTo{
        return $this->belongsTo(User::class,'user_id');
    }

}
