<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class FastApiService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.fastapi.base_url', env('FASTAPI_BASE_URL', 'http://127.0.0.1:8000')), '/');
    }

    /**
     * Check if the FastAPI service is healthy and model is loaded.
     */
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            if ($response->successful()) {
                $data = $response->json();
                return isset($data['status']) && $data['status'] === 'ok';
            }
            return false;
        } catch (Exception $e) {
            Log::warning("FastAPI health check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send leaf image to FastAPI inference endpoint.
     */
    public function predict(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new Exception("Image file not found at path: {$imagePath}");
        }

        try {
            // Send multipart request to FastAPI
            $response = Http::timeout(15)
                ->attach('file', file_get_contents($imagePath), basename($imagePath))
                ->post("{$this->baseUrl}/predict");

            if ($response->successful()) {
                return $response->json();
            }

            $errorData = $response->json();
            $errorMessage = $errorData['error'] ?? $errorData['detail'] ?? 'Unknown error';
            throw new Exception("FastAPI Predict Request Failed (HTTP {$response->status()}): {$errorMessage}");

        } catch (Exception $e) {
            Log::error("FastAPI prediction error: " . $e->getMessage());
            throw new Exception("Gagal terhubung dengan Layanan Klasifikasi FastAPI. Pastikan layanan aktif dan model termuat dengan benar. Detail: " . $e->getMessage());
        }
    }
}
