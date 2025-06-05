<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create( 'users', function ( Blueprint $table ) {
            $table->id();
            $table->float( "current_balance", 2 );
            $table->string( 'email' )->unique();
            $table->timestamp( 'email_verified_at' )->nullable();
            $table->string( 'first_name', 50 );
            $table->string( 'language', 10 );
            $table->string( 'last_name', 50 )->nullable();
            $table->date( "last_login_date" )->nullable();
            $table->date( "last_password_update" )->nullable();
            $table->string( 'password' );
            $table->date( "password_expires_on" )->nullable();
            $table->date( "registration_date" );
            $table->rememberToken();
            $table->string( 'role', 10 );
            $table->string( 'salt', 100 );
            $table->timestamps();
        });

        Schema::create( 'password_reset_tokens', function ( Blueprint $table ) {
            $table->string( 'email' )->primary();
            $table->string( 'token' );
            $table->timestamp( 'created_at' )->nullable();
        });

        Schema::create( 'sessions', function ( Blueprint $table ) {
            $table->string( 'id' )->primary();
            $table->foreignId( 'user_id' )->nullable()->index();
            $table->string( 'ip_address', 45 )->nullable();
            $table->text( 'user_agent' )->nullable();
            $table->longText( 'payload' );
            $table->integer( 'last_activity' )->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists( 'users' );
        Schema::dropIfExists( 'password_reset_tokens' );
        Schema::dropIfExists( 'sessions' );
    }
};
