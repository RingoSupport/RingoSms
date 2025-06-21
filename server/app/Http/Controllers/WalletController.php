<?php

namespace App\Http\Controllers;

use App\Services\WalletService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService) {
        $this->walletService = $walletService;
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/

    public function debit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'wallet_id' => 'nullable|integer|exists:wallets,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
                'status' => 'error'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $params = $request->only(['amount', 'user_id', 'description', 'wallet_id']);
            return $this->walletService->debit(
                $params['amount'],
                $params['user_id'],
                $params['description'],
                $params['wallet_id'] ?? 1
            );
        } catch (\Throwable $th) {
            Log::error('Debit failed', [
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'error' => $th->getMessage()
            ]);

            return response()->json([
                'message' => "An error occurred. Please try again later",
                'status' => 'error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/

    public function credit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'wallet_id' => 'nullable|integer|exists:wallets,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
                'status' => 'error'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $params = $request->only(['amount', 'user_id', 'description', 'wallet_id']);

            Log::info('Credit wallet transaction initiated', [
                'user_id' => $params['user_id'],
                'amount' => $params['amount']
            ]);

            return $this->walletService->credit(
                $params['amount'],
                $params['user_id'],
                $params['description'],
                $params['wallet_id'] ?? 1
            );
        } catch (\Throwable $th) {
            Log::error('Credit failed', [
                'user_id' => $request->user_id,
                'amount' => $request->amount,
                'error' => $th->getMessage()
            ]);

            return response()->json([
                'message' => "An error occurred. Please try again later",
                'status' => 'error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/

    public function fetchWalletBalance(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'wallet_id' => 'nullable|integer|exists:wallet,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
                'status' => 'error'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            Log::info('Fetching wallet balance', [
                'wallet_id' => $request->wallet_id ?? 1
            ]);

            return $this->walletService->getWalletBalance(
                $request->wallet_id ?? 1
            );
        } catch (\Throwable $th) {
            Log::error('Failed to fetch wallet balance', [
                'wallet_id' => $request->wallet_id ?? 1,
                'error' => $th->getMessage()
            ]);

            return response()->json([
                'message' => "An error occurred. Please try again later",
                'status' => 'error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * undocumented function summary
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/

    public function fetchWalletHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors(),
                'message' => 'Validation failed',
                'status' => 'error'
            ], Response::HTTP_UNPROCESSABLE_ENTITY); // 422
        }

        try {
            Log::info('Fetching wallet history', [
                'user_id' => $request->user_id
            ]);

            return $this->walletService->getWalletHistory(
                $request->user_id
            );
        } catch (\Throwable $th) {
            Log::error('Failed to fetch wallet history', [
                'user_id' => $request->user_id,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'message' => "An error occurred while fetching wallet history",
                'status' => 'error'
            ], Response::HTTP_INTERNAL_SERVER_ERROR); // 500
        }
    }
}
