<?php

namespace Modules\Orders\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Deliveries\Models\Delivery;
use Modules\Deliveries\Models\OrderDelivery;
use Modules\Restaurants\Models\Restaurant;

// use Modules\Orders\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "customer_id",
        "restaurant_id",
        "order_number",
        "total_amount",
        "status",
        "payment_status",
        "payment_method",
        "delivery_address",
        "customer_notes",
        "preparation_time",
        "delivery_fee",
        "tax_amount",
        "cancel_reason",
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
    
    public function deliveryAssignment()
    {
        return $this->hasOne(OrderDelivery::class);
    }
}
