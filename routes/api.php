<?php

use App\Http\Controllers\UserController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller( UserController::class )->prefix( "users" )->group(
    function () {
        Route::get( "", "index" )->name( "users.index" );
        Route::get( "/{id}", "show" )->name( "users.show" );
        Route::delete( "/{id}", "delete" )->name( "users.delete" );
        Route::post( "", "store" )->name( "users.store" );
        Route::patch( "/{id}", "update" )->name( "users.update" );
    }
);
