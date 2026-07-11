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
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderFlowE2eTest extends TestCase
{
    private const VARIANT_NOTE = "Size: M\nColor: Black\nQuantity: 1";

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
            'product_note' => self::VARIANT_NOTE,
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_link']);
    }

    public function test_rejects_shein_host_bypass_domains(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/orders', [
            'product_link' => 'https://shein.com.evil.com/product/123',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_link']);
    }

    public function test_accepts_orders_without_variant_note(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/orders', [
            'product_link' => 'https://www.shein.com/test-product.html',
        ])
            ->assertCreated()
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.product_note', null);
    }

    public function test_customer_can_update_variant_note_before_payment(): void
    {
        Sanctum::actingAs($this->user);

        $order = app(OrderService::class)->create(
            $this->user,
            'https://www.shein.com/test-product.html',
        );

        $this->putJson("/api/orders/{$order->id}/variant", [
            'product_note' => self::VARIANT_NOTE,
        ])
            ->assertOk()
            ->assertJsonPath('data.product_note', self::VARIANT_NOTE);

        $this->assertSame(self::VARIANT_NOTE, $order->fresh()->product_note);
    }

    public function test_empty_variant_update_does_not_clear_existing_note(): void
    {
        Sanctum::actingAs($this->user);

        $order = app(OrderService::class)->create(
            $this->user,
            'https://www.shein.com/test-product.html',
            self::VARIANT_NOTE,
        );

        $this->putJson("/api/orders/{$order->id}/variant", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['product_note']);

        $this->assertSame(self::VARIANT_NOTE, $order->fresh()->product_note);
    }

    public function test_variant_update_rejected_after_payment_sent(): void
    {
        Sanctum::actingAs($this->user);

        $order = app(OrderService::class)->create(
            $this->user,
            'https://www.shein.com/test-product.html',
            self::VARIANT_NOTE,
        );

        app(OrderService::class)->applyQuote($order, 25.00, 10, $this->admin);
        $order->refresh();

        $this->postJson("/api/orders/{$order->id}/payment-sent", ['method' => 'zaad'])
            ->assertOk();

        $this->putJson("/api/orders/{$order->id}/variant", [
            'product_note' => "Size: L\nColor: Red\nQuantity: 2",
        ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Variant details can no longer be changed.');

        $this->assertSame(self::VARIANT_NOTE, $order->fresh()->product_note);
    }

    public function test_batch_order_accepts_empty_notes_array(): void
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/orders/batch', [
            'links' => [
                'https://www.shein.com/item-1.html',
                'https://www.amazon.com/dp/B12345678',
            ],
            'notes' => [],
        ])->assertCreated()
            ->assertJsonCount(2, 'data');
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
            $this->postJson('/api/orders', [
                'product_link' => $link,
                'product_note' => self::VARIANT_NOTE,
            ])
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

        $this->postJson('/api/orders', [
            'product_link' => $shareText,
            'product_note' => self::VARIANT_NOTE,
        ])
            ->assertCreated()
            ->assertJsonPath('data.product_link', 'https://onelink.shein.com/42/5vdzzrjumi9v');
    }

    public function test_complete_order_lifecycle_end_to_end(): void
    {
        Sanctum::actingAs($this->user);

        $create = $this->postJson('/api/orders', [
            'product_link' => 'https://www.shein.com/test-product.html',
            'product_note' => 'Size: M\nColor: Black\nQuantity: 1',
        ])->assertCreated();

        $orderId = $create->json('data.id');
        $order = Order::findOrFail($orderId);

        $this->assertSame(OrderStatus::Submitted, $order->status);
        $this->assertSame('Size: M\nColor: Black\nQuantity: 1', $order->product_note);

        $orders = app(OrderService::class);
        $orders->applyQuote($order, 25.00, 10, $this->admin);

        $order->refresh();
        $this->assertSame(OrderStatus::Quoted, $order->status);
        $this->assertSame('27.50', $order->total_amount);
        $this->assertNull($order->shipping_fee);

        $this->postJson("/api/orders/{$orderId}/payment-sent", ['method' => 'zaad'])
            ->assertOk()
            ->assertJsonPath('data.status', 'payment_pending');

        $order->refresh();
        $orders->confirmPayment($order, $this->admin);
        $orders->advanceTracking($order->fresh(), $this->admin);
        $orders->advanceTracking($order->fresh(), $this->admin);
        $orders->markDelivered($order->fresh(), 15.00, $this->admin);

        $order->refresh();
        $this->assertSame(OrderStatus::Delivered, $order->status);
        $this->assertSame('15.00', $order->shipping_fee);

        $this->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'delivered')
            ->assertJsonPath('data.final_total', '$42.50')
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
            'notes' => [
                "Size: M\nColor: Black\nQuantity: 1",
                "Size: L\nColor: Blue\nQuantity: 2",
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
            self::VARIANT_NOTE,
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
            self::VARIANT_NOTE,
        );

        $this->getJson("/api/orders/{$otherOrder->id}")->assertForbidden();
    }

    public function test_notifications_can_be_marked_read_individually_and_in_bulk(): void
    {
        Sanctum::actingAs($this->user);

        $first = AppNotification::create([
            'user_id' => $this->user->id,
            'title' => 'Quote ready',
            'body' => 'Your order has a quote.',
            'type' => 'quote',
        ]);

        $second = AppNotification::create([
            'user_id' => $this->user->id,
            'title' => 'Shipped',
            'body' => 'Your order is on the way.',
            'type' => 'shipped',
        ]);

        $this->postJson("/api/notifications/{$first->id}/read")
            ->assertOk()
            ->assertJsonFragment(['id' => $first->id]);

        $this->assertNotNull($first->fresh()->read_at);
        $this->assertNull($second->fresh()->read_at);

        $this->postJson('/api/notifications/read-all')->assertOk();

        $this->assertNotNull($second->fresh()->read_at);
    }

    public function test_order_detail_fetches_product_metadata_from_link(): void
    {
        Http::fake([
            '*' => Http::response('<html><head>
                <meta property="og:title" content="Floral Summer Dress" />
                <meta property="og:description" content="Light cotton midi dress." />
                <meta property="og:image" content="https://img.shein.com/image-1.jpg" />
                <meta property="og:image" content="https://img.shein.com/image-2.jpg" />
            </head></html>', 200),
        ]);

        Sanctum::actingAs($this->user);

        $order = app(OrderService::class)->create(
            $this->user,
            'https://www.shein.com/Floral-Summer-Dress-p-123.html',
            'Size: M',
        );

        $this->getJson("/api/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.product_title', 'Floral Summer Dress')
            ->assertJsonPath('data.product_description', 'Light cotton midi dress.')
            ->assertJsonPath('data.product_platform', 'shein')
            ->assertJsonPath('data.product_metadata_status', 'complete')
            ->assertJsonCount(2, 'data.product_images');
    }

    public function test_customer_cannot_mark_another_users_notification_read(): void
    {
        Sanctum::actingAs($this->user);

        $otherUser = User::factory()->create();
        $notification = AppNotification::create([
            'user_id' => $otherUser->id,
            'title' => 'Private',
            'body' => 'Not yours',
            'type' => 'quote',
        ]);

        $this->postJson("/api/notifications/{$notification->id}/read")->assertNotFound();
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
