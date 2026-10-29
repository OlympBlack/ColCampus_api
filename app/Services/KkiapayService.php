<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KkiapayService
{
    protected $publicKey;
    protected $secretKey;
    protected $privateKey;
    protected $sandbox;
    protected $baseUrl;

    public function __construct()
    {
        $this->publicKey  = config('services.kkiapay.public_key');
        $this->secretKey  = config('services.kkiapay.secret_key');
        $this->privateKey = config('services.kkiapay.private_key');
        $this->sandbox    = config('services.kkiapay.sandbox', true); // ← ajouté

        // URL définie une seule fois ici
        $this->baseUrl = $this->sandbox
            ? 'https://api-sandbox.kkiapay.me'
            : 'https://api.kkiapay.me';
    }

    public function verifyTransaction(string $transactionId)
    {
        $url = "{$this->baseUrl}/api/v1/transactions/{$transactionId}/status";

        try {
            $response = Http::withHeaders([
                'x-private-key' => $this->privateKey,
                'Accept'        => 'application/json',
            ])->get($url);

            Log::info('Kkiapay verify response', [
                'transactionId' => $transactionId,
                'url'           => $url,           // ← pour confirmer l'URL utilisée
                'sandbox'       => $this->sandbox, // ← pour confirmer le mode
                'status'        => $response->status(),
                'body'          => $response->json(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['status']) && $data['status'] === 'SUCCESS') {
                    return $data;
                }
            }

            Log::error('Kkiapay verification failed', [
                'transactionId' => $transactionId,
                'response'      => $response->json(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Kkiapay exception: ' . $e->getMessage());
            return null;
        }
    }
}