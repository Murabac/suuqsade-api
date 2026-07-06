<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\AppNotification;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderStatusService
{
    public function __construct(
        private FcmService $fcm,
    ) {}

    public function transition(Order $order, OrderStatus $to, ?Admin $admin = null, array $attributes = []): Order
    {
        $this->assertTransitionAllowed($order->status, $to);

        return DB::transaction(function () use ($order, $to, $admin, $attributes) {
            $order->update(array_merge(['status' => $to], $attributes));

            $order->statusHistory()->create([
                'status' => $to,
                'changed_by' => $admin?->id,
            ]);

            $this->notifyUser($order, $to);

            return $order->fresh();
        });
    }

    private function assertTransitionAllowed(OrderStatus $from, OrderStatus $to): void
    {
        $allowed = match ($from) {
            OrderStatus::Submitted => [OrderStatus::Quoted, OrderStatus::Cancelled],
            OrderStatus::Quoted => [OrderStatus::PaymentPending, OrderStatus::Cancelled],
            OrderStatus::PaymentPending => [OrderStatus::PaymentConfirmed, OrderStatus::Cancelled],
            OrderStatus::PaymentConfirmed => [OrderStatus::Ordered],
            OrderStatus::Ordered => [OrderStatus::Shipped],
            OrderStatus::Shipped => [OrderStatus::Delivered],
            OrderStatus::Delivered, OrderStatus::Cancelled => [],
        };

        if (! in_array($to, $allowed, true)) {
            throw new InvalidArgumentException("Cannot transition from {$from->value} to {$to->value}.");
        }
    }

    private function notifyUser(Order $order, OrderStatus $status): void
    {
        $title = 'Order update';
        $body = "Your order #{$order->id} is now: {$status->label()}.";

        AppNotification::query()->create([
            'user_id' => $order->user_id,
            'title' => $title,
            'body' => $body,
            'type' => 'order_status',
            'data' => [
                'order_id' => $order->id,
                'status' => $status->value,
            ],
        ]);

        $this->fcm->sendToUser($order->user, $title, $body, [
            'order_id' => (string) $order->id,
            'status' => $status->value,
        ]);
    }
}
