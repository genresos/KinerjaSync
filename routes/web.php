<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KinerjaSyncController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', [KinerjaSyncController::class, 'main']);
