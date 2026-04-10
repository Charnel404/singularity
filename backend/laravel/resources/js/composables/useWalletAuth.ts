export const useWalletAuth = () => {
    const generateNonce = async (walletAddress: string) => {
        const response = await fetch('/api/wallet/nonce', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ wallet_address: walletAddress }),
        });

        if (!response.ok) {
            throw new Error('Failed to generate nonce');
        }

        return response.json() as Promise<{ nonce: string; message: string }>;
    };

    const verifySignature = async (walletAddress: string, signature: string) => {
        const response = await fetch('/api/wallet/verify', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ wallet_address: walletAddress, signature }),
        });

        if (!response.ok) {
            const error = await response.json();
            throw new Error(error.message || 'Authentication failed');
        }

        return response.json() as Promise<{
            message: string;
            user: {
                id: number;
                name: string;
                email: string;
                wallet_address: string;
            };
            token: string;
        }>;
    };

    return {
        generateNonce,
        verifySignature,
    };
};
