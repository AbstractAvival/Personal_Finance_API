<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\DependencyConflictException;
use App\Exceptions\DuplicateRecordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\DeleteCategoryRequest;
use App\Http\Requests\Category\IndexCategoryRequest;
use App\Http\Requests\Category\ShowCategoryRequest;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryCollection;
use App\Http\Resources\Category\CategoryResource;
use App\Repositories\CategoryRepository;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository
    ) {
    }

    public function delete( DeleteCategoryRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $deleted = $this->categoryRepository->delete( $validated[ "code" ] );
        return $deleted ? $this->responseDeletedSuccess()
            : $this->responseNotFound();
    }

    public function index( IndexCategoryRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->categoryRepository->list( 
            $validated[ "user_id" ],
            $validated[ "columns" ] ?? [ "*" ],
            $request::getPaginateParams() 
        );
        return $this->successResponse(
            ( new CategoryCollection( $data ) )->response()
        );
    }

    public function show( ShowCategoryRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->categoryRepository->get(
            $validated[ "code" ], 
            $validated[ "columns" ] ?? [ "*" ]
        );
        return $data ? $this->successResponse(
            ( new CategoryResource( $data ) )->response()
        ) : $this->responseNotFound();
    }

    public function store( StoreCategoryRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        try {
            $data = $this->categoryRepository->create( $validated );
        } catch( DependencyConflictException $exception ) {
            return $this->responseDependencyConflict( $exception->getMessage() );
        } catch( DuplicateRecordException $exception ) {
            return $this->responseDuplicate( $exception->getMessage() );
        }

        return $this->responseCreated(
            new CategoryResource( $data )
        );
    }

    public function update( UpdateCategoryRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        try {
            $data = $this->categoryRepository->update( $validated );
            return $data ? $this->responseUpdatedSuccess(
                ( new CategoryResource( $data ) )->response()
            )
                : $this->responseNotFound();
        } catch( NotFoundException $exception ) {
            return $this->responseNotFound( $exception->getMessage() );
        }
    }
}