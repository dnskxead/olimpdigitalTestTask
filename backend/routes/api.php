<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CarController;


Route::get('/cars/availability', [CarController::class, 'index']); //http://localhost/api/cars/availability