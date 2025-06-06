<?php

use App\Http\Controllers\User\UserController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller( UserController::class )->prefix( "users" )->group(
    function () {
        Route::get( "", "index" )->name( "users.index" );
    }
);
