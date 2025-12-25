<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User as Users;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    function signup(Request $request){
       $fields = $request->validate([
        'name'=>'required|string',
        'email'=>'required|email|unique:users,email',
        'password'=>'required|string|confirmed'
       ]);
       $user = Users::create($fields);
    $token  = $user->createToken('AUTH-TOKEN');
    return response([
        'message'=>'User registered successfully',
        'user'=>$user,
        'token'=>$token->plainTextToken,
    ] ,201);

    }
    function signin(Request $request){
        $fields = $request->validate([
            'email'=>'required|email',
            'password'=>'required|string'
           ]);
           $user = Users::where('email', $request->email)->first();

              if(!$user || !Hash::check($request->password, $user->password)){
                return response([
                 'message'=>'Bad creds'
                ], 401);
              }
              $token  = $user->createToken('AUTH-TOKEN');
                $response = [
                    'message'=>'User logged in successfully',
                    'user'=>$user,
                    'token'=>$token->plainTextToken,
                ];
                return response($response, 201);
            }
    function signout(Request $request) {
    $user = $request->user();

    // Check if user exists and has a current token

    if ($user && $user->currentAccessToken()) {
        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }

    return response()->json(['message' => 'Not authenticated or token missing'], 401);
}
}
