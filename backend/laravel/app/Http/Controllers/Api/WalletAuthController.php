<?php

namespace App\Http\Controllers\Api;

use App\Actions\Wallet\GenerateNonce;
use App\Actions\Wallet\VerifyWalletSignature;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WalletAuthController extends Controller
{
    public function __construct(
        private GenerateNonce $generateNonce,
        private VerifyWalletSignature $verifyWalletSignature
    ) {}

    public function generateNonce(Request $request): JsonResponse
    {
        $request->validate([
            'wallet_address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
        ]);

        $nonce = $this->generateNonce->handle($request->string('wallet_address')->value());

        return response()->json([
            'nonce' => $nonce,
            'message' => 'Sign this message to authenticate with your wallet.',
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'wallet_address' => ['required', 'string', 'regex:/^0x[a-fA-F0-9]{40}$/'],
            'signature' => ['required', 'string'],
        ]);

        try {
            $user = $this->verifyWalletSignature->handle(
                $request->string('wallet_address')->value(),
                $request->string('signature')->value()
            );

            $token = $user->createToken('wallet-auth')->plainTextToken;

            return response()->json([
                'message' => 'Authentication successful',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'wallet_address' => $user->wallet_address,
                ],
                'token' => $token,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 401);
        }
    }
}
