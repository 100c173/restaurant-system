<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = ['email' , 'otp_code' , 'otp_expires_at'];
}
