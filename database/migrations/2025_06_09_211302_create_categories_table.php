<?php

use App\Models\User;
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
        Schema::create( "categories", function ( Blueprint $table ) {
            $table->string( "code" )->primary();
            $table->string( "name", 30 );
            $table->string( "type", 30 );
            $table->string( "user_id", 30 )->foreignIdFor( User::class );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists( "categories" );
    }
};
