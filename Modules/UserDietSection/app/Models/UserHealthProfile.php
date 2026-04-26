<?php

namespace Modules\UserDietSection\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\UserDietSection\Database\Factories\UserHealthProfileFactory;

class UserHealthProfile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['gender','birth_date','height_cm','weight_kg','activity_level','goal','user_id'];

    protected $casts=[
        'birth_date' => 'date',
    ];

    public function user():BelongsTo{
        return $this->belongsTo(User::class,'user_id');
    }


}
