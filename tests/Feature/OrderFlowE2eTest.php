<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Admin;
use App\Models\AppNotification;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\SettingsSeeder;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderFlowE2eTest extends TestCase
{
    private User $user;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SettingsSeeder::class, AdminSeeder::class]);
        $this->admin = Admin::first();
        $this->user = $this->authenticateCustomer();
    }

    public function test_rejects_non_shein_amazon_product_links(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/orders', [
            'product_link' => 'https://www.alibaba.com/product/123',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_link']);
    }

    public function test_accepts_shein_and_amazon_links(): void
    {
        Sanctum::actingAs($this->user);

        foreach ([
            'https://www.shein.com/SHEIN-Dress-p-123.html',
            'https://www.amazon.com/dp/B09XK8LMBZ',
            'https://www.amazon.co.uk/dp/B07XTQM3GZ',
            'https://onelink.shein.com/42/5vdzzrjumi9v',
        ] as $link) {
            $this->postJson('/api/orders', ['product_link' => $link])
                ->assertCreated()
                ->assertJsonPath('data.status', 'submitted');
        }
    }

    public function test_accepts_shein_share_text_with_embedded_onelink(): void
    {
        Sanctum::actingAs($this->user);

        $shareText = <<<'TEXT'
        1 Pair Men's Shirt Stays Black
        I discovered amazing products on SHEIN.com, come check them out!
        https://onelink.shein.com/42/5vdzzrjumi9v
        TEXT;

        $this->postJson('/api/orders', ['product_link' => $shareText])
            ->assertCreated()
            ->assertJsonPath('data.product_link', 'https://onelink.shein.com/42/5vdzzrjumi9v');
    }

    public function test_complete_order_lifecycle_end_to_end(): void
    {
        Sanctum::actingAs($this->user);

        $create = $this->postJson('/api/orders', [
            'product_link' => 'https://www.shein.com/test-product.html',
            'product_note' => 'Size M',
        ])->assertCreated();

        $orderId = $create->json('data.id');
        $order = Order::findOrFail($orderId);

        $this->assertSame(OrderStatus::Submitted, $order->status);
        $this->assertSame('Size M', $order->product_note);

        $orders = app(OrderService::class);
        $orders->applyQuote($order, 25.00, 10, 15.00, $this->admin);

        $order->refresh();
        $this->assertSame(OrderStatus::Quoted, $order->status);
        $this->assertSame('42.50', $order->total_amount);

        $this->postJson("/api/orders/{$orderId}/payment-sent", ['method' => 'zaad'])
            ->assertOk()
            ->assertJsonPath('data.status', 'payment_pending');

        $order->refresh();
        $orders->confirmPayment($order, $this->admin);
        $orders->advanceTracking($order->fresh(), $this->admin);
        $orders->advanceTracking($order->fresh(), $this->admin);
        $orders->advanceTracking($order->fresh(), $this->admin);

        $order->refresh();
        $this->assertSame(OrderStatus::Delivered, $order->status);

        $this->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'delivered')
            ->assertJsonCount(7, 'data.status_history');

        $this->assertGreaterThanOrEqual(5, AppNotification::where('user_id', $this->user->id)->count());

        $this->getJson('/api/orders?filter=delivered')
            ->assertOk()
            ->assertJsonPath('data.0.id', $orderId);
    }

    public function test_batch_order_creates_shared_batch_id(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/orders/batch', [
            'links' => [
                'https://www.shein.com/item-1.html',
                'https://www.amazon.com/dp/B12345678',
            ],
        ])->assertCreated();

        $batchId = $response->json('data.0.batch_id');
        $this->assertNotEmpty($batchId);
        $this->assertSame($batchId, $response->json('data.1.batch_id'));
    }

    public function test_payment_sent_rejected_before_quote(): void
    {
        Sanctum::actingAs($this->user);

        $order = app(OrderService::class)->create(
            $this->user,
            'https://www.shein.com/pending.html',
        );

        $this->postJson("/api/orders/{$order->id}/payment-sent", ['method' => 'zaad'])
            ->assertStatus(422);
    }

    public function test_customer_cannot_view_other_users_orders(): void
    {
        Sanctum::actingAs($this->user);

        $otherOrder = app(OrderService::class)->create(
            User::factory()->create(),
            'https://www.shein.com/private.html',
        );

        $this->getJson("/api/orders/{$otherOrder->id}")->assertForbidden();
    }

    private function authenticateCustomer(): User
    {
        $response = $this->postJson('/api/auth/verify-otp', [
            'phone_number' => '252631112233',
            'code' => '123456',
            'name' => 'E2E Customer',
        ])->assertOk();

        return User::findOrFail($response->json('user.id'));
    }
}
