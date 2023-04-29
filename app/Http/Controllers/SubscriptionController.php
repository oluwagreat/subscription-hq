<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubscriptionRequest;
use App\Http\Resources\SubscriptionResource;
use App\Models\Subscription;
use Illuminate\Http\Request;

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
}
