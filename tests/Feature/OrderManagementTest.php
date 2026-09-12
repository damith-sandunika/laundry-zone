<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->branch = Branch::create([
            'name' => 'Colombo Main Branch',
            'code' => 'CMB',
            'is_active' => true,
        ]);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@365laundry.lk',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'branch_id' => $this->branch->id,
            'is_active' => true,
        ]);
    }

    public function test_authenticated_staff_can_view_orders_list(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('orders.index'));

        $response->assertOk();
    }

    public function test_can_update_order_status(): void
    {
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'phone' => '0771234567',
            'branch_id' => $this->branch->id,
        ]);

        $order = Order::create([
            'order_number' => '1001',
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'status' => 'received',
            'subtotal' => 1000,
            'total' => 1000,
            'created_by' => $this->superAdmin->id,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->post(route('orders.updateStatus', $order->order_number), [
                'status' => 'done',
                'notes' => 'Washing completed',
            ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'done',
        ]);
    }

    public function test_reports_index_page_is_accessible(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('reports.index'));

        $response->assertOk();
    }

    public function test_can_place_pos_delivery_order(): void
    {
        $customer = Customer::create([
            'name' => 'John Customer',
            'phone' => '0779998887',
            'branch_id' => $this->branch->id,
        ]);

        $service = \App\Models\ServiceType::create([
            'name' => 'Shirt Wash',
            'base_price' => 500,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->postJson(route('pos.delivery.store'), [
                'customer_id' => $customer->id,
                'pickup_address' => '123 Main Street',
                'pickup_date' => now()->addDay()->toDateString(),
                'cart' => [
                    [
                        'id' => $service->id,
                        'name' => $service->name,
                        'base_price' => 500,
                        'qty' => 2,
                    ]
                ],
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('delivery_appointments', [
            'customer_id' => $customer->id,
        ]);
    }
}
