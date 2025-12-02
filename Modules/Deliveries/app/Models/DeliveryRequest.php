<?php

namespace Modules\Deliveries\Models;

use App\Models\User;
use Carbon\Carbon;
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
        'name',
        'email',
        'phone_number',
        'national_id',
        'password',
        'otp_code',
        'otp_expires_at',
        'email_verified_at',
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
