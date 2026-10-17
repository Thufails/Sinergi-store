<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_order_creation_records_its_initial_status(): void
    {
        $category = Category::query()->create([
            'name' => 'Buku',
            'slug' => 'buku',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id,
            'name' => 'Buku Bisnis',
            'slug' => 'buku-bisnis',
            'price' => 155000,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->post(route('store.products.order', $product), [
            'customer_name' => 'Rina',
            'customer_phone' => '081234567890',
            'quantity' => 1,
        ]);

        $response->assertStatus(302);
        $this->assertStringContainsString('wa.me', (string) $response->headers->get('Location'));
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => Order::query()->firstOrFail()->id,
            'from_status' => null,
            'to_status' => Order::STATUS_PENDING_CONFIRMATION,
            'changed_by' => null,
            'actor_name' => 'Pelanggan',
        ]);
    }

    public function test_admin_status_change_is_recorded_and_shown_in_order_details(): void
    {
        $admin = User::factory()->create(['name' => 'Admin Toko']);
        $order = Order::query()->create([
            'order_number' => 'SS-20261017-TEST0001',
            'customer_name' => 'Rina',
            'customer_phone' => '081234567890',
            'status' => Order::STATUS_PENDING_CONFIRMATION,
            'total_amount' => 155000,
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.orders.status', $order), [
            'status' => Order::STATUS_CANCELLED,
        ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'from_status' => Order::STATUS_PENDING_CONFIRMATION,
            'to_status' => Order::STATUS_CANCELLED,
            'changed_by' => $admin->id,
            'actor_name' => 'Admin Toko',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Riwayat pesanan')
            ->assertSee('Menunggu konfirmasi')
            ->assertSee('Dibatalkan')
            ->assertSee('Admin Toko');
    }
}
