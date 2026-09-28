<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NilaiController;

Route::apiResource('nilai', NilaiController::class);