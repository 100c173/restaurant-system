<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $fillable = ['email', 'token', 'expires_at'];
    protected $primaryKey = 'email'; 
    protected $incrementing = false;   
    protected $keyType = 'string'; 
    protected $timestamps = false;

}
