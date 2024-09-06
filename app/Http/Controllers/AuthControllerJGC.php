<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthControllerJGC extends Controller
{
    use HttpResponses;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:users',
            'dob' => 'required',
            'zone' => 'required',
            'category' => 'required|string|max:255',
        ]);
                
        if ($validator->fails()) {
            return $this->error([], 'The input data are invalid', $validator->errors(), 422);
        }

        try{

            DB::beginTransaction();

            $name = strtolower(str_replace(' ', '', $request->name)); 
            $name = substr($name, 0, 9) . rand(10, 99);

        $data = [ 
            'name' => $request->name,
            'password' => bcrypt('password'),
            'email_verified_at' => $request->dob,
            'remember_token' => $request->zone,
            'email' => $name.'@jgc.com',
            'category' => $request->category,
            'dob' => $request->dob,
            'zone' => $request->zone,
            'to_take' => $request->to_take
            ];

        $user = User::create($data);

        // $token = $user->createToken('authToken')->plainTextToken;

        DB::commit();

        // $user->api_token = $token;
        // $user->dob = $request->dob;
        // $user->zone = $request->zone;
        // $user->category = $request->category;
        
        return $this->success(['user' => $user], 'User created successfully', 201);

        }catch(\Exception $e){
            DB::rollBack();
            Log::error("Error during signup. " . $e->getMessage());
            return $this->error([],"An error occured during signup" , $e->getMessage(), 500);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|exists:users,email',
            'password' => 'required',
        ]);
                
        if ($validator->fails()) {
            return $this->error([], 'The input data are empty or invalid', $validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error([], 'The provided credentials are incorrect',[], 401);
        }

        $token = $user->createToken('authToken')->plainTextToken;

        return $this->success(['user' => $user, 'token' => $token], 'User logged in successfully', 200);
        

    }

    public function usersList(){
        //in desc order
       $users = User::orderBy('id', 'desc')->get();
        // $users = User::latest();
        return $this->success(['users' => $users], 'Users retrieved successfully');
    }


}
