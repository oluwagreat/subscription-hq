<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use Illuminate\Http\Request;

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

        $transaction = Transaction::create([
            'user_id' => 1, //Auth::user()->id,
            'customer_email' => $request->customer_email,
            'customer_phone' => $request->customer_phone,
            'amount' => $request->amount * 100,
            'reference' => $request->reference,
            'authorization_url' => 'https://checkout.paystack.com/0peioxfhpn',
            'access_code' => 'access_code'
        ]);

        $created_transaction = new TransactionResource($transaction);

        return response()->json([
            'status' => true,
            'message' => 'Authorization URL created',
            'transaction' => $created_transaction
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
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
