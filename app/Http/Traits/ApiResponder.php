<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponder
{
    protected function successResponse(
        array|JsonResponse|JsonResource|null $data = null,
        int $code = Response::HTTP_OK,
        string $message = ''
    ): JsonResponse {
        $success = [
            'status' => true,
        ];
        if( !empty( $message ) ) {
            $success[ 'message' ] = $message;
        }
        if( is_object( $data )
            && class_basename( $data ) === 'JsonResponse'
        ) {
            $data = $data->getData( true );
        }
        // check if in class_parents has JsonResource
        // class_parents return array! Check if any of the elements has the string JsonResource
        if( is_object( $data ) && is_array( class_parents( $data ) ) ) {
            foreach( class_parents( $data ) as $parent ) {
                if( str_contains( $parent, 'JsonResource' ) ) {
                    $data = $data->response()->getData( true );
                    break;
                }
            }
        }

        if( !isset( $data ) ) {
            return response()->json( $success, $code );
        }

        return response()->json(
            isset( $data[ "data" ] )
                ?
                array_merge( $success, $data )
                : array_merge(
                    $success,
                    [ "data" => $data ]
                ),
            $code
        );
    }

    protected function errorResponse(
        int $code = Response::HTTP_BAD_REQUEST,
        ?string $message = null,
        ?array $errors = null
    ): JsonResponse {
        $errors = json_decode( json_encode( $errors ), true );

        switch( $code ) {
            case Response::HTTP_UNPROCESSABLE_ENTITY:
                $message = $message ?? __( 'app.invalid_data' );
                break;
            case Response::HTTP_CONFLICT:
                $message = $message ?? __( 'app.dependency_conflict' );
                break;
            case Response::HTTP_UNAUTHORIZED:
                $message = $message ?? __( 'auth.failed' );
                break;
            case Response::HTTP_FORBIDDEN:
                $message = $message ?? __( 'auth.forbidden' );
                break;
            case Response::HTTP_NOT_FOUND:
                $message = $message ?? __( 'app.no_record_found' );
                break;
            case Response::HTTP_BAD_REQUEST:
                $message = $message ?? __( 'app.error' );
                break;
        }

        return response()->json( [
            'status' => false,
            'message' => ( $result = json_decode( $message, true ) ) ? $result : $message,
            'errors' => $errors,
        ], $code);
    }

    protected function responseInvalidData( ?string $message = null ): JsonResponse 
    {
        return $this->errorResponse( Response::HTTP_UNPROCESSABLE_ENTITY, $message );
    }

    protected function responseDependencyConflict( ?string $message = null ): JsonResponse 
    {
        return $this->errorResponse( Response::HTTP_CONFLICT, $message );
    }

    protected function responseCreated( $result ): JsonResponse
    {
        return $this->successResponse( $result, Response::HTTP_CREATED, __( 'app.record_created' ) );
    }

    protected function responseUpdatedSuccess( $result ): JsonResponse
    {
        return $this->successResponse( $result, Response::HTTP_OK, __( 'app.record_updated' ) );
    }

    protected function responseUpdatedError( ?string $message = null ): JsonResponse 
    {
        return $this->errorResponse( Response::HTTP_BAD_REQUEST, $message ?? __( 'app.record_not_updated' ) );
    }

    protected function responseDuplicate( ?string $message = null ): JsonResponse
    {
        return $this->errorResponse( Response::HTTP_CONFLICT, $message ?? __( 'app.duplicate_record' ) );
    }

    protected function responseUnauthorized( ?string $message = null ): JsonResponse 
    {
        return $this->errorResponse( Response::HTTP_UNAUTHORIZED, $message ?? __( 'auth.failed' ) );
    }

    protected function responseForbidden( ?string $message = null ): JsonResponse 
    {
        return $this->errorResponse( Response::HTTP_FORBIDDEN, $message ?? __( 'auth.forbidden' ) );
    }

    protected function responseDeletedSuccess(): JsonResponse
    {
        return $this->successResponse( null, Response::HTTP_OK, __( 'app.record_deleted' ) );
    }

    protected function responseDeletedError( ?string $message = null ): JsonResponse 
    {
        return $this->errorResponse( Response::HTTP_BAD_REQUEST, $message ?? __( 'app.record_not_deleted' ) );
    }

    protected function responseNotFound( ?string $message = null ): JsonResponse
    {
        return $this->errorResponse( Response::HTTP_NOT_FOUND, $message ?? __( 'app.no_record_found' ) );
    }

    protected function validateNotFoundArray( $result )
    {
        if( empty( $result ) ) {
            $this->errorResponse( Response::HTTP_NOT_FOUND, __( 'app.no_records_found' ) );
        }
        return $result;
    }

    protected function validateNotFound( $result )
    {
        if( empty( $result ) ) {
            $this->errorResponse( Response::HTTP_NOT_FOUND, __( 'app.no_record_found' ) );
        }
        return $result;
    }
}