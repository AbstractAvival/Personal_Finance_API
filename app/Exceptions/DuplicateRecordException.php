<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DuplicateRecordException extends Exception
{
    protected $message = "A matching record already exists in the database.";
    protected $code = 409;
    
    /**
     * Report the exception.
     */
    public function report(): void
    {
        //
    }

    /**
     * Render the exception as an HTTP response.
     */
    public function render( Request $request )
    {
        return response()->json( [
            "message" => $this->message
        ], $this->code );
    }
}
