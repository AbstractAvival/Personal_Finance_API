<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\DependencyConflictException;
use App\Exceptions\DuplicateRecordException;
use App\Exceptions\PasswordDecryptionException;
use App\Http\Requests\User\DeleteUserRequest;
use App\Http\Requests\User\IndexUserRequest;
use App\Http\Requests\User\ShowUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function __construct(
        private readonly UserRepository $userRepository
    ) {    
    }

    public function delete( DeleteUserRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        try {
            $deleted = $this->userRepository->delete( $validated[ "id" ] );
            return $deleted ? $this->responseDeletedSuccess()
                : $this->responseNotFound();
        } catch( DependencyConflictException $exception ) {
            return $this->responseDependencyConflict( $exception->getMessage() );
        }
    }

    public function index( IndexUserRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->userRepository->list( 
            $validated[ "columns" ] ?? [ "*" ],
            $request::getPaginateParams() 
        );
        return $this->successResponse(
            ( new UserCollection( $data ) )->response()
        );
    }

    public function show( ShowUserRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->userRepository->get(
            $validated[ "id" ], 
            $validated[ "columns" ] ?? [ "*" ]
        );
        return $data ? $this->successResponse(
            ( new UserResource( $data ) )->response()
        ) : $this->responseNotFound();
    }

    public function store( StoreUserRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        try {
            $data = $this->userRepository->create( $validated );
        } catch( DuplicateRecordException $exception ) {
            return $this->responseDuplicate( $exception->getMessage() );
        } catch( PasswordDecryptionException $exception ) {
            return $this->responseInvalidData( $exception->getMessage() );
        }

        return $this->responseCreated(
            new UserResource( $data )
        );
    }

    public function update( UpdateUserRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        try {
            $data = $this->userRepository->update( $validated );
            return $data ? $this->responseUpdatedSuccess(
                ( new UserResource( $data ) )->response()
            )
                : $this->responseNotFound();
        } catch( NotFoundException $exception ) {
            return $this->responseNotFound( $exception->getMessage() );
        } catch( PasswordDecryptionException $exception ) {
            return $this->responseInvalidData( $exception->getMessage() );
        }
    }
}
