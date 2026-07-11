<?php

namespace App\Services;

use App\Models\Order;

class ProductMetadataService
{
    public function __construct(private ProductMetadataFetcher $fetcher) {}

    public function needsFetch(Order $order): bool
    {
        return in_array($order->product_metadata_status, ['pending', 'failed'], true);
    }

    public function fetchForOrder(Order $order): Order
    {
        if (! $this->needsFetch($order)) {
            return $order;
        }

        $metadata = $this->fetcher->fetch($order->product_link);
        $hasContent = filled($metadata['title']) || $metadata['images'] !== [];

        $order->update([
            'product_platform' => $metadata['platform'] ?? $this->fetcher->detectPlatform($order->product_link),
            'product_title' => $metadata['title'],
            'product_description' => $metadata['description'],
            'product_images' => $metadata['images'],
            'product_metadata_status' => $hasContent ? 'complete' : 'failed',
        ]);

        return $order->fresh();
    }
}
