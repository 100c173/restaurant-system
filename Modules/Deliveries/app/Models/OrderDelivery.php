<?php

namespace Modules\Deliveries\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Orders\Models\Order;

// use Modules\Deliveries\Database\Factories\OrderDeliveryFactory;

class OrderDelivery extends Model
{
    use HasFactory;
    protected $table = 'order_deliveries';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'delivery_id',
        'status',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'delivery_fee',
    ];

    public function order():BelongsTo{
        return $this->belongsTo(Order::class);
    }
    public function delivery():BelongsTo{
        return $this->belongsTo(Delivery::class,'delivery_id');
    }

}
