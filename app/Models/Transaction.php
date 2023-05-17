<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $fillable = [
        'reference',  
        'customer_email', 
        'customer_phone',
        'amount',
        'callback_url',
            'plan_code',
            'access_code',
            'gateway_response',
            'plan',
            'paid_at',
            'authorization_url',
            'authorization_code',
            'status',
            'user_id',
            
    ];
}
