<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Livewire\Admin\DashboardOverview;
use App\Livewire\Admin\IncomingQueue;
use App\Livewire\Admin\PaymentConfirmationQueue;
use App\Livewire\Admin\QuoteBuilder;
use App\Models\Admin;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Database\Seeders\AdminSeeder;
use Database\Seeders\SettingsSeeder;
use Livewire\Livewire;
use Tests\TestCase;

class AdminLivewireTest extends TestCase
{
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([SettingsSeeder::class, AdminSeeder::class]);
        $this->admin = Admin::first();
    }

    public function test_dashboard_shows_pipeline_counts(): void
    {
        $user = User::factory()->create();
        app(OrderService::class)->create($user, 'https://www.shein.com/a.html');

        Livewire::actingAs($this->admin, 'admin')
            ->test(DashboardOverview::class)
            ->assertSee('Dashboard')
            ->assertSee('1');
    }

    public function test_incoming_queue_lists_submitted_orders(): void
    {
        $user = User::factory()->create(['name' => 'Faadumo Xasan']);
        $order = app(OrderService::class)->create($user, 'https://www.shein.com/dress.html');

        Livewire::actingAs($this->admin, 'admin')
            ->test(IncomingQueue::class)
            ->assertSee('Faadumo Xasan')
            ->assertSee('Quote')
            ->call('cancel', $order->id)
            ->assertSee('cancelled');

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_quote_builder_sends_quote(): void
    {
        $user = User::factory()->create();
        $order = app(OrderService::class)->create($user, 'https://www.amazon.com/dp/B123.html');

        Livewire::actingAs($this->admin, 'admin')
            ->test(QuoteBuilder::class, ['order' => $order])
            ->set('item_cost', '20')
            ->set('service_fee_pct', '10')
            ->call('submitQuote')
            ->assertRedirect(route('admin.payments'));

        $order->refresh();
        $this->assertSame(OrderStatus::Quoted, $order->status);
        $this->assertSame('22.00', $order->total_amount);

        Livewire::actingAs($this->admin, 'admin')
            ->test(PaymentConfirmationQueue::class)
            ->assertSee('Awaiting customer')
            ->assertSee('22.00');
    }

    public function test_payment_queue_lists_quoted_orders_awaiting_customer(): void
    {
        $user = User::factory()->create(['name' => 'Ayaan Ali']);
        $orders = app(OrderService::class);
        $order = $orders->create($user, 'https://www.shein.com/jacket.html');
        $orders->applyQuote($order, 40, 10, $this->admin);

        Livewire::actingAs($this->admin, 'admin')
            ->test(PaymentConfirmationQueue::class)
            ->assertSet('filter', 'awaiting')
            ->assertSee('Ayaan Ali')
            ->assertSee('44.00')
            ->call('cancel', $order->id)
            ->assertSee('cancelled');

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
    }

    public function test_payment_queue_confirms_payment(): void
    {
        $user = User::factory()->create();
        $orders = app(OrderService::class);
        $order = $orders->create($user, 'https://www.shein.com/shoes.html');
        $orders->applyQuote($order, 30, 10, $this->admin);
        $orders->markPaymentSent($order->fresh(), 'edahab');

        Livewire::actingAs($this->admin, 'admin')
            ->test(PaymentConfirmationQueue::class)
            ->set('filter', 'confirm')
            ->assertSee('eDahab')
            ->call('confirm', $order->id)
            ->assertSee('confirmed');

        $this->assertSame(OrderStatus::PaymentConfirmed, $order->fresh()->status);
    }
}
