<?php

namespace App\Services;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Wallet;
use App\Models\WalletHistory;
use Illuminate\Support\Str;

class WalletService
{
    /**
     * Create a new class instance.
     */
    public function __construct() {}
    const MINIMUM_BALANCE = 1.00;

    /**
     * Debit amount from user's wallet
     *
     * Undocumented function long description
     *
     * @param Type $var Description
     * @return type
     * @throws conditon
     **/

    public function debit(float $amount, int $userId, string $description, int $walletId = 1): JsonResponse
    {

        return DB::transaction(function () use ($amount, $userId, $description, $walletId) {
            $wallet = Wallet::lockForUpdate()->find($walletId);

            if (!$wallet || $wallet->balance < ($amount + self::MINIMUM_BALANCE)) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Insufficient balance'
                ], 400);
            }

            $wallet->decrement('balance', $amount);

            WalletHistory::create([
                'user_id' => $userId,
                'amount' => $amount,
                'balance_after' => $wallet->balance - $amount,
                'type' => 'DEBIT',
                'reference_id' => Str::uuid()->toString(),
                'description' => $description,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Debit successful',
                'new_balance' => $wallet->balance
            ]);
        });
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

    public function credit(float $amount, int $userId, string $description, int $walletId = 1): JsonResponse {
        return DB::transaction(function () use ($amount, $userId, $description, $walletId) {
            if ($amount <= 0) {
                return response()->json([
                    'status' => 400,
                    'message' => 'Amount must be greater than zero'
                ], 400);
            }

            $wallet = Wallet::lockForUpdate()->find($walletId);

            if (!$wallet) {
                return response()->json([
                    'status' => 404,
                    'message' => 'Wallet not found'
                ], 404);
            }

            $wallet->increment('balance', $amount);

            WalletHistory::create([
                'user_id' => $userId,
                'amount' => $amount,
                'balance_after' => $wallet->balance,
                'type' => 'CREDIT',
                'reference_id' => Str::uuid()->toString(),
                'description' => $description,
            ]);

            return response()->json([
                'status' => 200,
                'message' => 'Credit successful',
                'new_balance' => $wallet->balance
            ]);
        });
    }



    public function getWalletBalance(int $walletId = 1): JsonResponse
    {
        $wallet = Wallet::find($walletId);

        if (!$wallet) {
            return response()->json([
                'status' => 404,
                'message' => 'Wallet not found'
            ], 404);
        }

        return response()->json([
            'status' => 200,
            'message' => 'Successful',
            'balance' => $wallet->balance,
        ]);
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
    public function getWalletHistory(int $userId): JsonResponse
    {
        $history = WalletHistory::where('user_id', $userId)
            ->latest()
            ->paginate(15); // Reduced from 1000 to a more reasonable default

        return response()->json([
            'status' => 200,
            'message' => 'Success',
            'data' => [
                'history' => $history->items(),
                'pagination' => [
                    'total' => $history->total(),
                    'per_page' => $history->perPage(),
                    'current_page' => $history->currentPage(),
                    'last_page' => $history->lastPage(),
                ]
            ]
        ]);
    }
}
