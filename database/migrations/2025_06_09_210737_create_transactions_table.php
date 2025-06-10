<?php

use App\Models\Category;
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
        Schema::create( "transactions", function ( Blueprint $table ) {
            $table->id();
            $table->float( "amount", 2 );
            $table->string( "category", 10 )->foreignIdFor( Category::class );
            $table->string( "description", 200 );
            $table->timestamp( "date_of_transaction" );
            $table->string( "type", 30 );
            $table->string( "user_id", 30 )->foreignIdFor( User::class );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists( "transactions" );
    }
};
