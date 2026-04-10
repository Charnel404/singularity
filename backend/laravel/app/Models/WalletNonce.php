<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletNonce extends Model
{
    protected $fillable = [
        'wallet_address',
        'nonce',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }
}
