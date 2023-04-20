<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'name', 'description', 'amount','interval'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
