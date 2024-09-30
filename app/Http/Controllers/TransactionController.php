<?php

namespace App\Http\Controllers;

use App\Models\SubHistory;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\TransactionSchedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = TransactionResource::collection(Transaction::all());
        return response()->json([
            'status' => true,
            'message' => 'Transactions retrieved successfully',
            'transactions' => $transactions
        ],200);
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
    public function store(TransactionRequest $request)
    {
        try {

            DB::beginTransaction();
         $request->validated($request->all());
         $amountInNaira = $request->amount;
         $amount = $request->amount * 100;
            //for OGData
            $og_meta = $request->og_meta ?? null; 

         if(isset($request->plan_code) && !empty($request->plan_code)){
         $body = [
            'amount' => $amount , 'email' =>$request->customer_email,
            'plan_code' => $request->plan_code, 
            'reference'=>$request->reference, 
            'callback_url' => route('paystack.callback')  //$request->callback_url
        ];
         }
         else{
            $body = [
                'amount' => $amount , 'email' =>$request->customer_email,
                'reference'=>$request->reference, 
                'callback_url' => route('paystack.callback') // $request->callback_url
            ];
         }

         //save transaction
        $transaction = Transaction::create([
                    'user_id' => 1, //Auth::user()->id,
                    'customer_email' => $request->customer_email,
                    'customer_phone' => $request->customer_phone,
                    'amount' => $amountInNaira,
                    'reference' => $request->reference,
                    'plan_code' => $request->plan_code,
                    'callback_url' => $request->callback_url,
                    'frequency' => $request->frequency,
                    'description' => $request->description
            ]);


        //save transaction
        DB::commit();

        //send request to paystack
         $url = env('PAYSTACK_TRANSACTION_URL',"https://api.paystack.co/transaction/initialize");
         $token = env('PAYSTACK_KEY');

         $response = Http::withToken($token)->withHeaders(['content-type' => 'application/json'])
                                            ->post($url,$body);

        //Log the response
        Log::error("Paystack Response: ".$response);

        if($response->ok()){
          $response = $response->json();
          $authURL = $response['data']['authorization_url'];

          //if authorization url is not set
          if (!isset($authURL) || empty($authURL)) {
            return response()->json([
                'status' => false,
                'message' => 'An error occured',
                'gateway_response' => $response
            ]);
          }

          //save transaction
          $transaction->update([
            'authorization_url' => $authURL,
            'access_code' => $response['data']['access_code'],
          ]);

          //for OGData
                if(isset($og_meta)){
                //add subscription id to og_meta
                $og_meta['transaction_id'] = $transaction->id;
                SubHistory::create($og_meta);
            }
      

        $created_transaction = new TransactionResource($transaction);

        return response()->json([
            'status' => true,
            'message' => 'Authorization URL created',
            'transaction' => $created_transaction
        ], 200);
        
    }else{
        return response()->json([
            'status' => false,
            'message' => 'An error occured',
            'gateway_response' => $response->json()
        ], 400);
    }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occured',
                'gateway_response' => $e->getMessage()
            ], 400);
        }

    }

    /**
     * Display the specified resource.
     */
    public function verify($reference)
    {
        // $url = env('PAYSTACK_VERIFY_URL',"https://api.paystack.co/transaction/verify/");
        // $url = $url.$reference;
        // $token = env('PAYSTACK_KEY');
        // $response = Http::withToken($token)->withHeaders(['content-type' => 'application/json'])
        //                                    ->get($url);
        // Log::info("Paystack Verify Response: ".$response);

        $transaction = Transaction::where('reference', $reference)->first();
        if (!$transaction) {
            return response()->json([
                'status' => false,
                'message' => 'Transaction not found',
            ], 404);
        }

        return response()->json([
              'status' => true,
              'message' => 'Transaction Retrieved',
              'transaction' => new TransactionResource($transaction)
          ], 200);
  
    }

    public function callback(Request $request)
    {
        try{
        $reference = $request->input('reference') ?? $request->input('ref');
        $url = env('PAYSTACK_VERIFY_URL', "https://api.paystack.co/transaction/verify/");
        $url = $url . $reference;
        $token = env('PAYSTACK_KEY');
        $sub_id = null;

        $response = Http::withToken($token)->withHeaders(['content-type' => 'application/json'])
        ->get($url);

        Log::error("Paystack Verify Response: " . $response);

        if ($response->ok()) {
            $response = $response->json();

            //fetch transaction and update transaction
            $transaction = Transaction::where('reference', $reference)->first();
            $user = $transaction->user;

            //if transaction has frequency
            if ($transaction->frequency) {
                $sub_id = $this->newSubscription($transaction, $response);
            }

            //update transaction
            $transaction->update([
                    'authorization_code' => $response['data']['authorization']['authorization_code'] ?? null,
                    'gateway_response' => $response['data']['gateway_response'],
                    'status' => $response['data']['status'],
                    'paid_at' => $response['data']['paid_at'] ?? now(),
                    'subscription_id' => $sub_id
            ]);

            

            //redirect to callback url
            $url = $transaction->callback_url ?? $user->callback_url;
            return redirect($url);

        } else {
            return response()->json([
                'status' => false,
                'message' => 'An error occured',
                'gateway_response' => $response->json()
            ], 400);
        }

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'An error occured',
            'gateway_response' => $e->getMessage()
        ], 400);
    }
    
    }

    protected function newSubscription($transaction, $response)
    {
        $noOfDays = $this->getScheduleDays($transaction->frequency);
        $startsAt = now()->addDays($noOfDays);
        $customer_phone = $response['data']['customer']['phone'] ?? null;
        $schedule = TransactionSchedule::create([
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'amount' => $transaction->amount,
            'starts_at' => $startsAt,
            'next_payment_date' => $startsAt,
            'frequency' => $transaction->frequency,
            'description' => $transaction->description,
            'authorization_code' => $response['data']['authorization']['authorization_code'] ?? null,
            'bin' => $response['data']['authorization']['bin'] ?? null,
            'last_four' => $response['data']['authorization']['last4'] ?? null,
            'exp_month' => $response['data']['authorization']['exp_month'] ?? null,
            'exp_year' => $response['data']['authorization']['exp_year'] ?? null,
            'channel' => $response['data']['authorization']['channel'] ?? null,
            'card_type' => $response['data']['authorization']['card_type'] ?? null,
            'bank' => $response['data']['authorization']['bank'] ?? null,
            'country_code' => $response['data']['authorization']['country_code'] ?? null,
            'brand' => $response['data']['authorization']['brand'] ?? null,
            'reusable' => $response['data']['authorization']['reusable'] ?? null,
            'signature' => $response['data']['authorization']['signature'] ?? null,
            'account_name' => $response['data']['authorization']['account_name'] ?? null,
            'customer_id' => $response['data']['customer']['id'] ?? null,
            'customer_email' => $response['data']['customer']['email'] ?? null,
            'customer_first_name' => $response['data']['customer']['first_name'] ?? null,
            'customer_last_name' => $response['data']['customer']['last_name'] ?? null,
            'customer_code' => $response['data']['customer']['customer_code'] ?? null,
            'customer_phone' => $transaction->customer_phone ?? $customer_phone,        
        ]);

        //update subscription history
        $subHistory = SubHistory::where('transaction_id', $transaction->id)->first();
        if ($subHistory) {
            $subHistory->update(['subscription_id' => $schedule->id]);
        }

        return $schedule->id;

        
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

    public function paystackWebhook()
    {
        // Retrieve the request's body
        $input = @file_get_contents("php://input");
        $secretKey = env('PAYSTACK_KEY');
        // define('PAYSTACK_SECRET_KEY','env');

        //log in file
        $filePath = storage_path('app/paystacklog.txt');
        // // Write the data to the file
        $dataToWrite = date("Y-m-d h:i:sa");
        $dataToWrite .= $input;
        File::append($filePath, $dataToWrite);
        File::append($filePath, "\n");

        http_response_code(200);

        // validate event do all at once to avoid timing attack
        // if($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] !== hash_hmac('sha512', $input, $secretKey))
        //    exit();
        if (!isset($_SERVER['HTTP_X_PAYSTACK_SIGNATURE']) || !hash_equals($_SERVER['HTTP_X_PAYSTACK_SIGNATURE'], hash_hmac('sha512', $input, $secretKey))) {
            Log::error('Invalid paystack signature ' . $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] . ' - ' . hash_hmac('sha512', $input, $secretKey));
            exit();
        }


        // parse event (which is json string) as object
        $input = json_decode($input, true);

        Log::error("Incoming paystack webhook :", $input);

        // Do something - that will not take long - with $event
        $event = $input['event'];


        if ($event == "charge.success") {

            http_response_code(200);
        } elseif ($event == "transfer.success" || $event == "transfer.failed") {

            http_response_code(200);
        } elseif ($event == "invoice.update") {
            //subscription update
        }

        http_response_code(200);
    }

}
