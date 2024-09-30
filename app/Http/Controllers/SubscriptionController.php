<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Str;
use App\Models\Subscription;
use Illuminate\Http\Request;
use App\Models\TransactionSchedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\SubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Jobs\WebHookSender;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       // return Subscription::all();
       $subscriptions = SubscriptionResource::collection(Subscription::all());
        return response()->json([
            'status' => true,
        'message' => 'Subscriptions retrieved successfully',
        'subscriptions' => $subscriptions
    ], 200);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SubscriptionRequest $request)
    {
        $subscription = Subscription::create([
            'user_id' => 1,
            'plan_code' => $request->plan_code,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
        ]);
       
        
        $created_sub = new SubscriptionResource($subscription);

        return response()->json([
            'status' => true,
            'message' => 'Subscription created successfully',
            'subscription' => $created_sub
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($subscription_code)
    {
        $subscription = SubscriptionResource::collection(Subscription::where('subscription_code',$subscription_code)->get());
        return response()->json([
            'status' => true,
        'message' => 'Subscription retrieved successfully',
        'subscription' => $subscription
    ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Subscription $subscription)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription)
    {
        //
    }

    public function initiateCharge(Request $request){
        try{
        $schedule = TransactionSchedule::find(1);
        if ($schedule) {
            $url = env('PAYSTACK_CHARGE_URL', "https://api.paystack.co/transaction/charge_authorization");
            //charge
            $token = env('PAYSTACK_KEY');
            $reference = $schedule->id."-".Str::random(8). mt_rand(10000, 99999);

            $body = [
                'authorization_code' => $schedule->authorization_code,
                'amount' => $schedule->amount * 100,
                'email' => $schedule->customer_email,
                'reference' => $reference,
            ];

            //Log the request
            Log::info("Paystack Charge Request for " . $schedule->customer_email . ": " . json_encode($body, JSON_PRETTY_PRINT));

            $response = Http::withToken($token)->withHeaders(['content-type' => 'application/json'])
            ->post($url, $body);

            //Log the response
            Log::info("Paystack Response: " . $response);

            if ($response->ok()) {
                $response = $response->json();
                
                    //insert transaction
                    $transaction = Transaction::create([
                        'user_id' => $schedule->user_id,
                        'customer_email' => $schedule->customer_email,
                        'customer_phone' => $schedule->customer_phone,
                        'amount' => $schedule->amount,
                        'reference' => $reference,
                        'frequency' => $schedule->frequency,
                        'description' => $schedule->description,
                        'authorization_code' =>$schedule->authorization_code,
                        'gateway_response' => $response['data']['gateway_response'],
                        'status' => $response['data']['status'],
                        'paid_at' => $response['data']['transaction_date'] ?? now(),
                        'subscription_id' => $schedule->id
                    ]);

                //update count and next date
                $schedule->count = $schedule->count + 1;
                $schedule->last_payment_date = now();
                $schedule->next_payment_date = now()->addDays($this->getScheduleDays($schedule->frequency));
                $schedule->save();

                //notify user
                $user = $schedule->user;
                // $user->notify(new \App\Notifications\NewSubscription($transaction));

                //send webhook
                WebHookSender::dispatch($transaction);

                return response()->json([
                    'status' => true,
                    'message' => 'Charge created successfully',
                    'response' => $response
                ], 201);

            }else{

                return response()->json([
                    'status' => false,
                    'message' => 'Charge not created',
                    'response' => $response->json()
                ], 404);
            }

            
           
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Subscription schedule not found',
            ], 404);

        }


        }catch(\Exception $e){
            Log::error("Error during charge: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
        
    }

    protected function getScheduleDays($frequency)
    {
        switch ($frequency) {
            case 'daily':
                return 1;
            case 'weekly':
                return 7;
            case 'monthly':
                return 30;
            case 'yearly':
                return 365;
            default:
                return 7;
        }
    }
}
