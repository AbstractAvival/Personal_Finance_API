<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class DependencyConflictException extends Exception
{
    protected $message = "The provided data or solicited operation conflicts with current status.";
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
