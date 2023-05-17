<?php

namespace App\Http\Resources;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PlanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
                'plan_code' => $this->plan_code,
                'name' => $this->name,
                'description' =>(string)$this->description,
                'amount' => $this->amount/100,
                'interval' => $this->interval,
                'subscriptions' => Subscription::where('plan_code',$this->plan_code)->count(),
                'user_id' => $this->user_id,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
        ];

        
    }
}
