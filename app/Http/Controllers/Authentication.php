<?php

namespace App\Http\Controllers;


use App\Models\User;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use mysql_xdevapi\Exception;
use function Laravel\Prompts\password;

class Authentication extends Controller
{

    //this function authenticate a user and assigns a token
    public function login(Request $request){
        $formRequest = $request->all();

        $user = User::where('email', $formRequest['email'])->first();

        if ($user && Hash::check($formRequest['password'],$user['password']) ){


            if  ($user->status === 'inactive'){
                return response()->json(['message' => 'User Currently Deactivated '],402);
            }
            auth()->login($user);

            $token = $user->createToken($user['email'])->plainTextToken;

            return response()->json(['message' => 'Login Successful ', 'user' => $user, 'token'=>$token],200);
        }
        else{
            return response()->json(['message'=>'Invalid Registration credential'],401);
        }


    }


    //this registers a user to the database and generates a token and assigns it to the user
    public function register(Request $request){

        try {
            $formRequest = $request->all();

            $hashedPassword = Hash::make($formRequest['password']);

            $formRequest['password'] = $hashedPassword;

            if ($request->hasFile('image')){
                $path = $request->file('image')->store('userImage','public');

                $formRequest['image'] = $path;

            }

            $user = User::create($formRequest);

            if ($user){
                auth()->login($user);

                $token = $user->createToken($formRequest['email'])->plainTextToken;

            }
            else{
                return response()->json(['message'=>'Invalid Registration credential'],401);
            }
            return response()->json(['message' => 'Registration Successful ', 'user' => $user, 'token'=>$token],200);

        }catch (\Exception $exception){
            return response()->json(['message' => $exception->getMessage()]);
        }

    }


    /*this invalidates or deletes the user token from the database*/
    public function logout(Request $request){

        try {
            $user = $request->user();

            $user->currentAccessToken()->delete;

            return response()->json(['message'=> 'You are Logged out'], 200);

        }catch (Exception $exception){
            return  response()->json(['message'=>'Unable to Logout User'],401);
        }

    }


}
