<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Submitted = 'submitted';
    case Quoted = 'quoted';
    case PaymentPending = 'payment_pending';
    case PaymentConfirmed = 'payment_confirmed';
    case Ordered = 'ordered';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Submitted => 'Submitted',
            self::Quoted => 'Quoted',
            self::PaymentPending => 'Payment pending',
            self::PaymentConfirmed => 'Payment confirmed',
            self::Ordered => 'Ordered',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
        };
    }
}
