<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller( CategoryController::class )->prefix( "categories" )->group(
    function () {
        Route::get( "", "index" )->name( "categories.index" );
        Route::get( "/{code}", "show" )->name( "categories.show" );
        Route::delete( "/{code}", "delete" )->name( "categories.delete" );
        Route::post( "", "store" )->name( "categories.store" );
        Route::patch( "/{code}", "update" )->name( "categories.update" );
    }
);

Route::controller( TransactionController::class )->prefix( "transactions" )->group(
    function () {
        Route::get( "", "index" )->name( "transactions.index" );
        Route::get( "/{id}", "show" )->name( "transactions.show" );
        Route::delete( "/{id}", "delete" )->name( "transactions.delete" );
        Route::post( "", "store" )->name( "transactions.store" );
        Route::patch( "/{id}", "update" )->name( "transactions.update" );
    }
);

Route::controller( UserController::class )->prefix( "users" )->group(
    function () {
        Route::get( "", "index" )->name( "users.index" );
        Route::get( "/{id}", "show" )->name( "users.show" );
        Route::delete( "/{id}", "delete" )->name( "users.delete" );
        Route::post( "", "store" )->name( "users.store" );
        Route::patch( "/{id}", "update" )->name( "users.update" );
    }
);