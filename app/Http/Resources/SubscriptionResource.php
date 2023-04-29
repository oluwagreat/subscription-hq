<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
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
                'subscription_code'=> $this->subscription_code,
                'authorization'=> $this->authorization,
                'status'=> $this->status,
                'amount'=> $this->amount/100,
                'plan' => ['plan_code'=> $this->plan_code, 'plan_name'=> $this->plan_name],
                'customer' => ['customer_email'=> $this->customer_email, 'customer_phone'=> $this->customer_phone],
                'user_id'=> $this->user_id,
                'starts_at'=> $this->starts_at,
                'ends_at'=> $this->ends_at,
                'next_payment_date'=> $this->next_payment_date,
                'created_at'=> $this->created_at,
                'updated_at'=> $this->updated_at
        ];
    }
}
