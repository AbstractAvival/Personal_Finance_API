<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class PasswordDecryptionException extends Exception
{
    protected $message = "The given value for password could not be parsed.";
    protected $code = 422;

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
