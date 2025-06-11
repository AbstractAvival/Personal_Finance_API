<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\DependencyConflictException;
use App\Exceptions\DuplicateRecordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Role\DeleteRoleRequest;
use App\Http\Requests\Role\IndexRoleRequest;
use App\Http\Requests\Role\ShowRoleRequest;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleResource;
use App\Repositories\RoleRepository;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    public function __construct(
        private readonly RoleRepository $roleRepository
    ) {
    }

    public function delete( DeleteRoleRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $deleted = $this->roleRepository->delete( $validated[ "code" ] );
        return $deleted ? $this->responseDeletedSuccess()
            : $this->responseNotFound();
    }

    public function index( IndexRoleRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->roleRepository->list( 
            $validated[ "columns" ] ?? [ "*" ],
            $request::getPaginateParams() 
        );
        return $this->successResponse(
            ( new RoleCollection( $data ) )->response()
        );
    }

    public function show( ShowRoleRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->roleRepository->get(
            $validated[ "code" ], 
            $validated[ "columns" ] ?? [ "*" ]
        );
        return $data ? $this->successResponse(
            ( new RoleResource( $data ) )->response()
        ) : $this->responseNotFound();
    }

    public function store( StoreRoleRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        try {
            $data = $this->roleRepository->create( $validated );
        } catch( DuplicateRecordException $exception ) {
            return $this->responseDuplicate( $exception->getMessage() );
        }

        return $this->responseCreated(
            new RoleResource( $data )
        );
    }

    public function update( UpdateRoleRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        try {
            $data = $this->roleRepository->update( $validated );
            return $data ? $this->responseUpdatedSuccess(
                ( new RoleResource( $data ) )->response()
            )
                : $this->responseNotFound();
        } catch( NotFoundException $exception ) {
            return $this->responseNotFound( $exception->getMessage() );
        }
    }
}