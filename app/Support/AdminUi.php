<?php

namespace App\Support;

use App\Enums\OrderStatus;
use App\Models\Order;

class AdminUi
{
    public static function orderRef(Order $order): string
    {
        $year = $order->created_at?->format('Y') ?? date('Y');

        return 'SQ-'.$year.'-'.str_pad((string) $order->id, 3, '0', STR_PAD_LEFT);
    }

    public static function truncateUrl(string $url, int $max = 52): string
    {
        $parsed = parse_url($url);

        if (! $parsed || empty($parsed['host'])) {
            return strlen($url) > $max ? substr($url, 0, $max).'…' : $url;
        }

        $host = preg_replace('/^www\./', '', $parsed['host']);
        $path = $parsed['path'] ?? '';
        $display = $host.$path;

        return strlen($display) > $max ? substr($display, 0, $max).'…' : $display;
    }

    public static function statusLabel(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Submitted => 'New',
            OrderStatus::Quoted => 'Quoted',
            OrderStatus::PaymentPending => 'Awaiting Payment',
            OrderStatus::PaymentConfirmed => 'Payment Confirmed',
            OrderStatus::Ordered => 'Ordered',
            OrderStatus::Shipped => 'Shipped',
            OrderStatus::Delivered => 'Delivered',
            OrderStatus::Cancelled => 'Cancelled',
        };
    }

    public static function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'zaad' => 'ZAAD',
            'edahab' => 'eDahab',
            default => '—',
        };
    }

    public static function paymentMethodBadgeClass(?string $method): string
    {
        return match ($method) {
            'zaad' => 'badge-zaad',
            'edahab' => 'badge-edahab',
            default => 'badge-quoted',
        };
    }

    public static function statusBadgeClass(OrderStatus $status): string
    {
        return match ($status) {
            OrderStatus::Submitted => 'badge-new',
            OrderStatus::Quoted => 'badge-quoted',
            OrderStatus::PaymentPending => 'badge-payment-pending',
            OrderStatus::PaymentConfirmed => 'badge-payment-confirmed',
            OrderStatus::Ordered => 'badge-ordered',
            OrderStatus::Shipped => 'badge-shipped',
            OrderStatus::Delivered => 'badge-delivered',
            OrderStatus::Cancelled => 'badge-delivered',
        };
    }
}
