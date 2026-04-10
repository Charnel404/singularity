<?php

namespace App\Actions\Wallet;

use App\Actions\Teams\CreateTeam;
use App\Models\User;
use App\Models\WalletNonce;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Simplito\Elliptic\Secp256k1;

class VerifyWalletSignature
{
    private Secp256k1 $secp256k1;

    public function __construct(
        private CreateTeam $createTeam
    ) {
        $this->secp256k1 = new Secp256k1;
    }

    public function handle(string $walletAddress, string $signature): User
    {
        $walletAddress = Str::lower($walletAddress);

        $nonce = WalletNonce::where('wallet_address', $walletAddress)->first();

        if (! $nonce || $nonce->isExpired()) {
            throw new \Exception('Invalid or expired nonce. Please request a new signature.');
        }

        $message = "Sign this message to authenticate with your wallet. Nonce: {$nonce->nonce}";
        $recoveredAddress = $this->recoverAddress($message, $signature);

        if (Str::lower($recoveredAddress) !== $walletAddress) {
            throw new \Exception('Signature verification failed.');
        }

        $nonce->delete();

        return $this->authenticateOrRegister($walletAddress);
    }

    private function recoverAddress(string $message, string $signature): string
    {
        $msgHash = $this->hashPersonalMessage($message);

        $sig = $this->parseSignature($signature);
        if (! $sig) {
            throw new \Exception('Invalid signature format.');
        }

        $recoveryParam = $this->getRecoveryParam($msgHash, $sig);

        $point = $this->secp256k1->pointFromSignature(
            $msgHash,
            $sig['r'],
            $sig['s'],
            $recoveryParam
        );

        if (! $point) {
            throw new \Exception('Could not recover public key from signature.');
        }

        $publicKey = $point->encode('array');
        if (count($publicKey) === 65 && $publicKey[0] === 0x04) {
            array_shift($publicKey);
        }

        return '0x'.substr($this->keccak256($publicKey), 24);
    }

    private function parseSignature(string $signature): ?array
    {
        $sig = trim($signature);

        if (str_starts_with($sig, '0x')) {
            $sig = substr($sig, 2);
        }

        if (strlen($sig) !== 130) {
            return null;
        }

        return [
            'r' => '0x'.substr($sig, 0, 64),
            's' => '0x'.substr($sig, 64, 64),
        ];
    }

    private function getRecoveryParam(string $msgHash, array $sig): int
    {
        $publicKey1 = $this->secp256k1->recoverPubKey($msgHash, [
            'r' => $sig['r'],
            's' => $sig['s'],
        ], 0);

        $msgHashInt = gmp_init($msgHash, 16);
        $r = gmp_init($sig['r'], 16);
        $s = gmp_init($sig['s'], 16);

        for ($recId = 0; $recId < 4; $recId++) {
            try {
                $pubKey = $this->secp256k1->recoverPubKey($msgHashInt, [
                    'r' => $r,
                    's' => $s,
                ], $recId);

                $p = $pubKey->encode('array');
                if (count($p) === 65 && $p[0] === 0x04) {
                    array_shift($p);
                }

                $addr1 = '0x'.substr($this->keccak256($p), 24);

                $pubKey1Array = $publicKey1->encode('array');
                if (count($pubKey1Array) === 65 && $pubKey1Array[0] === 0x04) {
                    array_shift($pubKey1Array);
                }
                $addr2 = '0x'.substr($this->keccak256($pubKey1Array), 24);

                if ($addr1 === $addr2) {
                    return $recId;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return 0;
    }

    private function hashPersonalMessage(string $message): string
    {
        $prefix = "\x19Ethereum Signed Message:\n".strlen($message);

        return $this->keccak256($prefix.$message);
    }

    private function keccak256(string $data): string
    {
        return hash('keccak256', $data);
    }

    private function authenticateOrRegister(string $walletAddress): User
    {
        return DB::transaction(function () use ($walletAddress) {
            $user = User::where('wallet_address', $walletAddress)->first();

            if (! $user) {
                $shortAddress = substr($walletAddress, 0, 10);
                $user = User::create([
                    'name' => "Wallet {$shortAddress}",
                    'email' => "wallet_{$walletAddress}@localhost",
                    'password' => null,
                    'wallet_address' => $walletAddress,
                ]);

                $this->createTeam->handle($user, "User's Team", isPersonal: true);
            }

            return $user;
        });
    }
}
