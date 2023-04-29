<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePlanRequest;
use App\Http\Resources\PlanResource;
use App\Models\Plan;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $plans = PlanResource::collection(Plan::all());
        return response()->json([
            'status' => true,
            'message' => 'Plans retrieved successfully',
            'plans' => $plans
        ], 200);

       //return Plan::all();
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
    public function store(StorePlanRequest $request)
    {
      //  $request->validated($request->all());
        $plan = Plan::create([
            'user_id' => 1, //Auth::user()->id,
            'name' => $request->name,
            'amount' => $request->amount * 100,
            'interval' => $request->interval,
            'description' => $request->description,
        ]);
        
        $created_plan = new PlanResource($plan);

        return response()->json([
            'status' => true,
            'message' => 'Plan created successfully',
            'plan' => $created_plan
        ], 201);

        
        
    }

    /**
     * Display the specified resource.
     */
    public function show($plan_code)
    {
        // $plan = Plan::where('id',$plan)->get();
        // return $plan; 

        $plan = PlanResource::collection(Plan::where('plan_code',$plan_code)->get());
        return response()->json([
            'status' => true,
           'message' => 'Plan retrieved',
           'plan' => $plan
       ], 200);
    }

    public function single($plan_code)
    {

        // $plan = Plan::where('plan_code',$plan_code)->get();
        //  return $plan;

     $plan = PlanResource::collection(Plan::where('plan_code',$plan_code)->get());
     return response()->json([
        'status' => true,
        'message' => 'Plan retrieved',
        'plan' => $plan
    ], 200);
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        //
    }
}
