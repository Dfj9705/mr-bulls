<?php

namespace Tests\Feature;

use App\Livewire\Shop\Checkout;
use App\Models\{Category, Product, Promotion, Order, User, Wallet};
use App\Services\{PricingService, CartService, OrderService, WalletService};
use App\Support\GuatemalaLocations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class PromotionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
    }

    private function product(string $name, ?Category $category = null, string $price = '350.00'): Product
    {
        $category ??= Category::create(['name' => 'Gorras', 'slug' => 'gorras', 'is_active' => true]);
        return Product::create(['name' => $name, 'slug' => $name, 'sku' => $name, 'category_id' => $category->id, 'price' => $price, 'stock' => 20, 'is_active' => true]);
    }

    private function promotion(Product $product, array $overrides = []): Promotion
    {
        return Promotion::create(array_merge(['name' => 'Dos por quinientos', 'type' => 'category_bundle', 'category_id' => $product->category_id, 'eligible_price' => '350.00', 'bundle_quantity' => 2, 'bundle_price' => '500.00', 'is_active' => true], $overrides));
    }

    public function test_mixed_models_receive_one_best_promotion_per_group(): void
    {
        $a = $this->product('a');
        $b = $this->product('b', $a->category);
        $this->promotion($a);
        $this->promotion($a, ['name' => 'Menor ahorro', 'bundle_price' => '600.00']);
        session(['cart' => [$a->id => 2, $b->id => 1]]);
        $items = app(CartService::class)->items();
        $this->assertEquals(850, $items->sum('subtotal'));
        $this->assertSame(20000, $items->sum('promotion_discount_cents'));
        $this->assertEquals(['Dos por quinientos'], $items->pluck('promotion_snapshot.name')->unique()->values()->all());
    }

    public function test_different_prices_or_categories_do_not_combine(): void
    {
        $a = $this->product('a');
        $b = $this->product('b', $a->category, '400.00');
        $category = Category::create(['name' => 'Otra', 'slug' => 'otra', 'is_active' => true]);
        $c = $this->product('c', $category);
        $this->promotion($a);
        session(['cart' => [$a->id => 1, $b->id => 1, $c->id => 1]]);
        $this->assertEquals(1100, app(CartService::class)->subtotal());
    }

    public function test_validity_start_inclusive_end_exclusive_and_inactive(): void
    {
        $this->travelTo(now()->startOfSecond());
        $a = $this->product('a');
        $promotion = $this->promotion($a, ['starts_at' => now()->addHour(), 'ends_at' => now()->addHours(2)]);
        session(['cart' => [$a->id => 2]]);
        $this->assertEquals(700, (new PricingService)->quote(app(CartService::class)->items())->sum('subtotal'));
        $this->travel(1)->hours();
        $this->assertEquals(500, (new PricingService)->quote(app(CartService::class)->items())->sum('subtotal'));
        $this->travel(1)->hours();
        $this->assertEquals(700, (new PricingService)->quote(app(CartService::class)->items())->sum('subtotal'));
        $promotion->update(['starts_at' => null, 'ends_at' => null, 'is_active' => false]);
        $this->assertEquals(700, (new PricingService)->quote(app(CartService::class)->items())->sum('subtotal'));
    }

    private function checkout(): \Livewire\Features\SupportTesting\Testable
    {
        $department = array_key_first(GuatemalaLocations::departments());
        $municipality = array_key_first(GuatemalaLocations::municipalitiesFor($department));
        return Livewire::test(Checkout::class)
            ->set('customerName', 'Cliente')->set('customerEmail', 'cliente@example.com')
            ->set('customerPhone', '55555555')->set('shippingRecipient', 'Cliente')
            ->set('shippingPhone', '55555555')->set('department', $department)
            ->set('municipality', $municipality)->set('address', 'Zona 1');
    }

    public function test_checkout_persists_net_lines_wallet_reward_and_idempotent_cancellation(): void
    {
        config(['loyalty.enabled' => true, 'loyalty.start_date' => null]);
        $user = User::factory()->create();
        $this->actingAs($user);
        Wallet::create(['user_id' => $user->id, 'balance' => '100.00', 'pending_reversal' => '0.00']);
        $a = $this->product('a');
        $b = $this->product('b', $a->category);
        $promotion = $this->promotion($a);
        session(['cart' => [$a->id => 1, $b->id => 1]]);
        $this->checkout()->set('useWallet', true)->set('walletAmount', '30.00')->call('createOrder')->assertHasNoErrors();
        $order = Order::firstOrFail();
        $this->assertEquals('500.00', $order->subtotal);
        $this->assertEquals('200.00', $order->promotion_discount);
        $this->assertEquals('470.00', $order->total);
        $this->assertEquals(500, $order->items->sum('subtotal'));
        $this->assertEquals('Dos por quinientos', $order->items->first()->promotion_snapshot['name']);
        $promotion->update(['name' => 'Nuevo nombre', 'is_active' => false]);
        $this->assertEquals('Dos por quinientos', $order->fresh()->items->first()->promotion_snapshot['name']);
        $this->get(route('orders.success', $order->public_token))->assertOk()->assertSee('Dos por quinientos')->assertSee('200.00');
        $this->assertStringContainsString('Dos por quinientos', (new \App\Mail\OrderReceived($order->fresh('items')))->render());
        $order->update(['payment_status' => Order::PAYMENT_PAID]);
        app(WalletService::class)->creditPurchase($order);
        app(WalletService::class)->creditPurchase($order);
        $this->assertEquals('79.40', $user->wallet()->first()->balance);
        app(OrderService::class)->cancel($order);
        app(OrderService::class)->cancel($order);
        $this->assertEquals('100.00', $user->wallet()->first()->balance);
        $this->assertEquals(20, $a->fresh()->stock);
        $this->assertEquals(20, $b->fresh()->stock);
        $this->assertSame(1, $order->walletTransactions()->where('reference', 'purchase_reward:' . $order->id)->count());
    }

    public function test_changed_promotion_blocks_checkout_without_consuming_stock(): void
    {
        $a = $this->product('a');
        $promotion = $this->promotion($a);
        session(['cart' => [$a->id => 2]]);
        $checkout = $this->checkout();
        $promotion->update(['bundle_price' => '550.00']);
        $checkout->call('createOrder')->assertHasErrors('cart');
        $this->assertSame(0, Order::count());
        $this->assertEquals(20, $a->fresh()->stock);
    }

    public function test_storefront_displays_bundle_without_changing_single_unit_price(): void
    {
        $a = $this->product('a');
        $this->promotion($a);
        $this->get('/productos/a')->assertOk()->assertSee('2 por Q500.00')->assertSee('Q350.00');
        $this->get('/productos')->assertOk()->assertSee('2 por Q500.00');
    }

    public function test_filament_admin_can_create_a_promotion_with_additive_permissions(): void
    {
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('admin'));
        $role = \Spatie\Permission\Models\Role::create(['name' => 'Administrador', 'guard_name' => 'web']);
        $this->seed(\Database\Seeders\PromotionPermissionsSeeder::class);
        $admin = User::factory()->create();
        $admin->assignRole($role);
        $this->actingAs($admin);
        $a = $this->product('a');
        Livewire::test(\App\Filament\Resources\PromotionResource\Pages\CreatePromotion::class)
            ->fillForm(['name' => 'Paquete gorras', 'type' => 'category_bundle', 'category_id' => $a->category_id,
                'eligible_price' => 350, 'bundle_quantity' => 2, 'bundle_price' => 500, 'is_active' => true])
            ->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('promotions', ['name' => 'Paquete gorras', 'bundle_price' => 500]);
        $customer = User::factory()->create();
        $this->actingAs($customer);
        $this->assertFalse(\App\Filament\Resources\PromotionResource::canViewAny());
        $this->assertFalse(\App\Filament\Resources\PromotionResource::canCreate());
    }

    public function test_checkout_without_promotion_retains_original_totals(): void
    {
        $a = $this->product('a');
        session(['cart' => [$a->id => 3]]);
        $this->checkout()->call('createOrder')->assertHasNoErrors();
        $order = Order::firstOrFail();
        $this->assertEquals('1050.00', $order->total);
        $this->assertEquals('0.00', $order->promotion_discount);
        $this->assertNull($order->items->first()->promotion_snapshot);
    }

    public function test_expired_promotion_during_checkout_requires_review(): void
    {
        $this->travelTo(now()->startOfSecond());
        $a = $this->product('a');
        $this->promotion($a, ['ends_at' => now()->addMinute()]);
        session(['cart' => [$a->id => 2]]);
        $checkout = $this->checkout();
        $this->travel(1)->minutes();
        $checkout->call('createOrder')->assertHasErrors('cart');
        $this->assertSame(0, Order::count());
    }

    public function test_inactive_category_cannot_generate_a_discount(): void
    {
        $a = $this->product('a');
        $this->promotion($a);
        $a->category->update(['is_active' => false]);
        session(['cart' => [$a->id => 2]]);
        $this->assertEquals(700, app(CartService::class)->subtotal());
    }
}
