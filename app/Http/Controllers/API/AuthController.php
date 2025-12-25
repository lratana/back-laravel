<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User as Users;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    function signup(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed'
        ]);
        $user = Users::create($fields);
        $token  = $user->createToken('AUTH-TOKEN');
        return response([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 201);
    }
    function signin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);
        $user = Users::where('email', $request->email)->first();

        if(empty($user)){
            throw ValidationException::withMessages([
                'email' => ['Email does not exist.'],
            ]);

        }
        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password does not match.'],
            ]
            );

        }
        $token  = $user->createToken('AUTH-TOKEN');
        $response = [
            'message' => 'User signed in successfully',
            'user' => $user,
            'token' => $token->plainTextToken,
        ];
        return response($response, 200);
    }
    function signout(Request $request)
    {
       $user = $request->user();
       if($user && ! $user->currentAccessToken()){
        return response([
            'message' => 'No authenticated user found.'
        ], 401);
       }
       //method1: Revoke all tokens
       $user->currentAccessToken()->delete();
       return response([
        'message' => 'User signed out successfully'
       ], 200);
       //mehtod2: Revoke specific token
       // $user->tokens()->where('id', $user->currentAccessToken()->id)->delete();


    }
}
