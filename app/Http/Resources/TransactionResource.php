<?php

namespace App\Http\Resources;

use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
        'id'=> $this->id,
        'subscription_id'=> $this->subscription_id,
       'reference'=>$this->reference,
       'customer_email'=>$this->customer_email,
       'customer_phone'=>$this->customer_phone,
       'authorization_url' => $this->authorization_url,
       'gateway_response'=> $this->gateway_response,
       'description'=> $this->description,
       'status'=>$this->status,
       'amount'=> $this->amount,
       'paid_at'=> $this->paid_at,
       'frequency' => $this->frequency,
       'created_at'=>$this->created_at,
       'updated_at'=>$this->updated_at
       //    'authorization_code'=> $this->authorization_code,
    //    'access_code' => $this->access_code,
    //    'user_id'=> $this->user_id,
    //    'plan' => Plan::where('plan_code',$this->plan_code)->get(["plan_code","name"]) ,
      // 'plan_code'=> $this->plan_code,
    //    'callback_url' => $this->callback_url,
       
       
        ];
    }
}
