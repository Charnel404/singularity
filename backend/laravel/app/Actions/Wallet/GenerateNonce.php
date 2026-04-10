<?php

namespace App\Actions\Wallet;

use App\Models\WalletNonce;
use Illuminate\Support\Str;

class GenerateNonce
{
    public function handle(string $walletAddress): string
    {
        WalletNonce::where('wallet_address', Str::lower($walletAddress))->delete();

        $nonce = Str::random(40);

        WalletNonce::create([
            'wallet_address' => Str::lower($walletAddress),
            'nonce' => $nonce,
            'expires_at' => now()->addMinutes(5),
        ]);

        return $nonce;
    }
}
