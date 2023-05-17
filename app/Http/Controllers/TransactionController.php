<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
         $request->validated($request->all());
         $amount = $request->amount * 100;
         if(isset($request->plan_code) && !empty($request->plan_code)){
         $body = ['amount' => $amount , 'email' =>$request->customer_email,'plan_code' => $request->plan_code, 'reference'=>$request->reference, 'callback_url' => $request->callback_url];
         }
         else{
            $body = ['amount' => $amount , 'email' =>$request->customer_email,'reference'=>$request->reference, 'callback_url' => $request->callback_url];
         }

         $url = env('PAYSTACK_TRANSACTION_URL',"https://api.paystack.co/transaction/initialize");
         $token = env('PAYSTACK_KEY');

         $response = Http::withToken($token)->withHeaders(['content-type' => 'application/json'])
                                            ->post($url,$body);

        if($response->ok()){
          $response = $response->json();

        $transaction = Transaction::create([
            'user_id' => 1, //Auth::user()->id,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'amount' => $amount,
            'reference' => $request->reference,
            'authorization_url' => $response['data']['authorization_url'], //'https://checkout.paystack.com/0peioxfhpn',
            'access_code' => $response['data']['access_code'], //Str::random(10),
            'plan_code' => $request->plan_code,
            'callback_url' => $request->callback_url
        ]);

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

    }

    /**
     * Display the specified resource.
     */
    public function verify($reference)
    {
      //  return ["msg" => "There is an issue".$reference];

        $url = env('PAYSTACK_VERIFY_URL',"https://api.paystack.co/transaction/verify/");
        $url = $url.$reference;
        $token = env('PAYSTACK_KEY');

        $response = Http::withToken($token)->withHeaders(['content-type' => 'application/json'])
                                           ->get($url);

        if($response->ok()){
            $response = $response->json();
            DB::table('transactions')
            ->where('reference', $reference)
            ->update([
                'authorization_code' => $response['data']['authorization']['authorization_code'],
                'gateway_response' => $response['data']['gateway_response'],
            ]);
  
  
          return response()->json([
              'status' => true,
              'message' => 'Transaction Retrieved',
              'transaction' => $response
          ], 200);
  
          
      }else{
          return response()->json([
              'status' => false,
              'message' => 'An error occured',
              'gateway_response' => $response->json()
          ], 400);
      }
  
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }
}
