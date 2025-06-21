<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletHistory extends Model
{
    use HasFactory;
    protected $table = "wallet_history";
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_after',
        'reference_id',
        'description',
        'status'
    ];
}
