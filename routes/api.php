<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;


Route::post('/signup', [AuthController::class, 'signup']); // Register a new user and return token
Route::post('/signin', [AuthController::class, 'signin']); // Authenticate user and return token
Route::post('/signout', [AuthController::class, 'signout'])->middleware('auth:sanctum'); // Invalidate current token (logout)
