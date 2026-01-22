<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordResetToken extends Model
{
    protected $fillable = ['email', 'token', 'expires_at'];
    protected $primaryKey = 'email'; 
    public $incrementing = false;   
    protected $keyType = 'string'; 
    public $timestamps = false;

}
