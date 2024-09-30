<?php

namespace App\Jobs;

use App\Models\SubHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class WebHookSender implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $transaction;

    /**
     * Create a new job instance.
     */
    public function __construct($transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $transaction = $this->transaction;
        $subscription_id = $transaction->subscription_id;

        //check sub history
        $sub_history = SubHistory::where('subscription_id', $subscription_id)->first();

        if ($sub_history) {
            $sub_history->update([
                'count' => $sub_history->count + 1,
            ]);
            //convert to array
            $sub_history = $sub_history->toArray();
            // dd($sub_history);
            //send to OGData
            try {
                Http::post('https://ogdata.com.ng/api/subscription/index.php', $sub_history);
            } catch (\Exception $e) {
                Log::error("Subscription Job Error: " . $e->getMessage());
            }
            
        }else{
            //send Webhook
        //     $url = $transaction->webhook_url;
        // $response = Http::post($url, $transaction);

        // Log::info("Webhook Response: " . $response);
             
        }
        
        
    }
}
