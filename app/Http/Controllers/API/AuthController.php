<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User as Users;
class AuthController extends Controller
{
    function signup(Request $request){
        $request->validate([
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|min:6|max:10|confirmed',
        ]);
        $user = Users::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        return response()->json(['message' => 'User registered successfully'], 201);
    }
}
