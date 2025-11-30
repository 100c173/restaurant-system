<?php

namespace Modules\Deliveries\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Orders\Models\Order;

// use Modules\Deliveries\Database\Factories\DeliveryFactory;

class Delivery extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'phone_number',
        'city',
        'vehicle_type',
        'vehicle_number',
        'is_available',
        'rating',
        'completed_orders',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries()
    {
        return $this->hasMany(OrderDelivery::class);
    }
}
