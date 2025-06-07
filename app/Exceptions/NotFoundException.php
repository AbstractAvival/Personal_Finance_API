<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;

class NotFoundException extends Exception
{
    protected $message = "The requested record could not be found.";
    protected $code = 404;

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
