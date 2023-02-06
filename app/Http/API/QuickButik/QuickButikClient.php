<?php

declare(strict_types=1);

namespace App\Http\API\QuickButik;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;

final class QuickButikClient
{
    private string $apiKey;
    private string $baseUrl = 'https://api.quickbutik.com/v1/';
    private PendingRequest $pendingRequest;

    public function __construct()
    {
        $this->apiKey = env('QUICKBUTIK_API_KEY');
        $this->pendingRequest = Http::withBasicAuth($this->apiKey, $this->apiKey);
    }

    public function getOrders(): array
    {
        return $this->pendingRequest
            ->get($this->baseUrl . 'orders')
            ->json();
    }

    public function getOrder(int $orderId, bool $includeDetails = true): array
    {
        return current($this->pendingRequest
            ->get($this->baseUrl . 'orders', [
                'order_id'          => $orderId,
                'include_details'   => $includeDetails
            ])
            ->json());
    }
}
