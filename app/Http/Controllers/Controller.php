<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

abstract class Controller
{
    public function register(RegisterRequest $request)
    {
        $user=User::create([
            'name' =>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password)
        ]);


        $token=$user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' =>'User registered successfully',
            'user'=>$user,
            'token'=>$token
        ],201);
    }

   public function login(LoginRequest $request)
   {
    $user=User::where('email',$request->email)->first();

    if(!$user || !Hash::check($request->password,$user->password))
        {
            return response()->json([
                'message'=>'The email or password is incorrect.'
            ],401);
        }
 
    $token=$user->createToken('auth_token')->plainTextToken;

     return response()->json([
        'message'=>'Login success',
        'user'=>$user,
        'token'=>$token
     ]);

   }

}
