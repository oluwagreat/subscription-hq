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
       'reference'=>$this->reference,
       'customer_email'=>$this->customer_email,
       'customer_phone'=>$this->customer_phone,
       'authorization_code'=> $this->authorization_code,
       'authorization_url' => $this->authorization_url,
        'access_code' => $this->access_code,
       'user_id'=> $this->user_id,
       'gateway_response'=> $this->gateway_response,
       'amount'=> $this->amount/100,
       'paid_at'=> $this->paid_at,
       'plan' => Plan::where('plan_code',$this->plan_code)->get(["plan_code","name"]) ,
      // 'plan_code'=> $this->plan_code,
       'callback_url' => $this->callback_url,
       'status'=>$this->status,
       'created_at'=>$this->created_at,
       'updated_at'=>$this->updated_at
        ];
    }
}
