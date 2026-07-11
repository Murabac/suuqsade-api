<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private OrderStatusService $statusService,
        private SettingsService $settings,
    ) {}

    public function create(User $user, string $productLink, ?string $productNote = null, ?string $batchId = null): Order
    {
        $order = Order::query()->create([
            'user_id' => $user->id,
            'product_link' => $productLink,
            'product_note' => $productNote,
            'batch_id' => $batchId,
            'status' => OrderStatus::Submitted,
            'delivery_address' => $user->delivery_address,
        ]);

        $order->statusHistory()->create([
            'status' => OrderStatus::Submitted,
        ]);

        return $order;
    }

    public function createBatch(User $user, array $links, array $notes = []): array
    {
        $batchId = (string) Str::uuid();
        $orders = [];

        foreach ($links as $index => $link) {
            $orders[] = $this->create(
                $user,
                $link,
                $notes[$index] ?? null,
                $batchId,
            );
        }

        return $orders;
    }

    public function updateProductNote(Order $order, ?string $productNote): Order
    {
        if (! in_array($order->status, [OrderStatus::Submitted, OrderStatus::Quoted], true)) {
            throw new \InvalidArgumentException('Variant details can no longer be changed.');
        }

        $order->update(['product_note' => $productNote]);

        return $order->fresh();
    }

    public function markPaymentSent(Order $order, string $method): Order
    {
        if ($order->status !== OrderStatus::Quoted) {
            throw new \InvalidArgumentException('Payment can only be sent for quoted orders.');
        }

        Payment::query()->create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'method' => $method,
        ]);

        return $this->statusService->transition($order, OrderStatus::PaymentPending);
    }

    public function applyQuote(Order $order, float $itemCost, float $serviceFeePct, ?\App\Models\Admin $admin = null): Order
    {
        $serviceFee = round($itemCost * ($serviceFeePct / 100), 2);
        $total = round($itemCost + $serviceFee, 2);

        return $this->statusService->transition($order, OrderStatus::Quoted, $admin, [
            'item_cost' => $itemCost,
            'service_fee_pct' => $serviceFeePct,
            'shipping_fee' => null,
            'total_amount' => $total,
        ]);
    }

    public function confirmPayment(Order $order, \App\Models\Admin $admin): Order
    {
        $order->payments()->latest()->first()?->update([
            'confirmed_by' => $admin->id,
            'confirmed_at' => now(),
        ]);

        return $this->statusService->transition($order, OrderStatus::PaymentConfirmed, $admin);
    }

    public function advanceTracking(Order $order, \App\Models\Admin $admin): Order
    {
        $next = match ($order->status) {
            OrderStatus::PaymentConfirmed => OrderStatus::Ordered,
            OrderStatus::Ordered => OrderStatus::Shipped,
            default => throw new \InvalidArgumentException('Order cannot be advanced.'),
        };

        return $this->statusService->transition($order, $next, $admin);
    }

    public function markDelivered(Order $order, float $shippingFee, \App\Models\Admin $admin): Order
    {
        if ($order->status !== OrderStatus::Shipped) {
            throw new \InvalidArgumentException('Only shipped orders can be marked delivered.');
        }

        return $this->statusService->transition($order, OrderStatus::Delivered, $admin, [
            'shipping_fee' => $shippingFee,
        ]);
    }

    public function cancelOrder(Order $order, \App\Models\Admin $admin): Order
    {
        if (! in_array($order->status, [
            OrderStatus::Submitted,
            OrderStatus::Quoted,
            OrderStatus::PaymentPending,
        ], true)) {
            throw new \InvalidArgumentException('This order can no longer be cancelled.');
        }

        return $this->statusService->transition($order, OrderStatus::Cancelled, $admin);
    }

    public function defaultServiceFeePct(): float
    {
        return (float) $this->settings->get('default_service_fee_pct', '10');
    }

    public function defaultShippingFee(): float
    {
        return (float) $this->settings->get('default_shipping_fee', '15.00');
    }
}
