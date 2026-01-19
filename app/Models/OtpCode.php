<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['email' , 'otp_hash' , 'otp_expires_at','verified_at','reset_token'];
    
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
            'otp_hash' => 'hashed',
        ];
    }
}
