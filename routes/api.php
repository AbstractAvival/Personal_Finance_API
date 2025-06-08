<?php

use App\Http\Controllers\UserController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller( UserController::class )->prefix( "users" )->group(
    function () {
        Route::get( "", "index" )->name( "users.index" );
        Route::get( "/{userId}", "show" )->name( "users.show" );
        Route::delete( "/{userId}", "delete" )->name( "users.delete" );
        Route::post( "", "store" )->name( "users.store" );
        Route::patch( "/{userId}", "update" )->name( "users.update" );
    }
);
