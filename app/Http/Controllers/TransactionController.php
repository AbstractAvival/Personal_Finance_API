<?php

namespace App\Http\Controllers;

use App\Exceptions\NotFoundException;
use App\Exceptions\DependencyConflictException;
use App\Exceptions\DuplicateRecordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\DeleteTransactionRequest;
use App\Http\Requests\Transaction\IndexTransactionRequest;
use App\Http\Requests\Transaction\ShowTransactionRequest;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\Transaction\TransactionCollection;
use App\Http\Resources\Transaction\TransactionResource;
use App\Repositories\TransactionRepository;
use Illuminate\Http\JsonResponse;

class TransactionController extends Controller
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository
    ) {
    }

    public function delete( DeleteTransactionRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $deleted = $this->transactionRepository->delete( $validated[ "id" ] );
        return $deleted ? $this->responseDeletedSuccess()
            : $this->responseNotFound();
    }

    public function index( IndexTransactionRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->transactionRepository->list( 
            $validated[ "user_id" ],
            $validated[ "columns" ] ?? [ "*" ],
            $request::getPaginateParams() 
        );
        return $this->successResponse(
            ( new TransactionCollection( $data ) )->response()
        );
    }

    public function show( ShowTransactionRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        $data = $this->transactionRepository->get(
            $validated[ "id" ], 
            $validated[ "columns" ] ?? [ "*" ]
        );
        return $data ? $this->successResponse(
            ( new TransactionResource( $data ) )->response()
        ) : $this->responseNotFound();
    }

    public function store( StoreTransactionRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        try {
            $data = $this->transactionRepository->create( $validated );
        } catch( DependencyConflictException $exception ) {
            return $this->responseDependencyConflict( $exception->getMessage() );
        } catch( DuplicateRecordException $exception ) {
            return $this->responseDuplicate( $exception->getMessage() );
        }

        return $this->responseCreated(
            new TransactionResource( $data )
        );
    }

    public function update( UpdateTransactionRequest $request ): JsonResponse
    {
        $validated = $request->validated();
        try {
            $data = $this->transactionRepository->update( $validated );
            return $data ? $this->responseUpdatedSuccess(
                ( new TransactionResource( $data ) )->response()
            )
                : $this->responseNotFound();
        } catch( DependencyConflictException $exception ) {
            return $this->responseDependencyConflict( $exception->getMessage() );
        } catch( NotFoundException $exception ) {
            return $this->responseNotFound( $exception->getMessage() );
        }
    }
}