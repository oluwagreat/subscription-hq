<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',  
        'customer_email', 
        'customer_phone',
        'amount',
        'plan_code',
        'plan_name',
        'status',
        'subscription_code',
        'authorization',
        'starts_at',
        'ends_at',
        'next_payment_date'
    ];

    public function plan(){
        return $this->belongsTo(Plan::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
