<?php

namespace Tests\Feature;

use App\Jobs\SendOrderStatusNotificationJob;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test_token', ['orders:read', 'orders:write'])->plainTextToken;
    }

    /** @test */
    public function an_authenticated_user_can_create_an_order()
    {
        $expectedCode = 'ORD-NEW-1';
        $expectedAmount = 150.75; 

        $orderData = [
            'code' => $expectedCode, 
            'amount_decimal' => $expectedAmount,
            'status' => 'placed',
            'placed_at' => now()->format('Y-m-d H:i:s'), 
        ];

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->postJson('/api/v1/orders', $orderData);


        $response->assertStatus(201)
            ->assertJsonStructure([
                'message', 
                'order' => ['id', 'code', 'amount', 'currentStatus', 'user_id']
            ]);

        $response->assertJsonFragment([
            'message' => 'Order placed successfully.',
            'user_id' => $this->user->id
        ]);

        $response->assertJsonPath('order.amount', $expectedAmount);
        $response->assertJsonPath('order.currentStatus', 'placed');


        $actualCode = $response->json('order.code');

        $this->assertDatabaseHas('orders', [
            'code' => $actualCode, 
            'user_id' => $this->user->id,
            'amount_decimal' => $expectedAmount, 
        ]);
    }

    /** @test */
    public function updating_order_status_dispatches_notification_job()
    {
        
        Queue::fake();


        $order = Order::factory()->for($this->user)->create([
            'status' => 'placed',
            'amount_decimal' => 50.00,
        ]);

   
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->patchJson("/api/v1/orders/{$order->id}", [
            'status' => 'shipped',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('order.currentStatus', 'shipped'); 

        Queue::assertPushed(SendOrderStatusNotificationJob::class, function ($job) use ($order) {
            return $job->order->id === $order->id && $job->order->status === 'shipped';
        });
    }

    /** @test */
    
    public function orders_index_can_filter_by_multiple_statuses_via_pipeline()
    {
        
        $order1 = Order::factory()->for($this->user)->create(['status' => 'placed']);
        $order2 = Order::factory()->for($this->user)->create(['status' => 'shipped']); // Should be excluded
        $order3 = Order::factory()->for($this->user)->create(['status' => 'processing']);
        $otherUserOrder = Order::factory()->create(['status' => 'placed']); // Should be excluded (wrong user)

        
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/v1/orders?status=placed,processing');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data') 
            ->assertJsonFragment(['code' => $order1->code])
            ->assertJsonFragment(['code' => $order3->code])
            ->assertJsonMissing(['code' => $order2->code])
            ->assertJsonMissing(['code' => $otherUserOrder->code]);
    }
    /** @test */
    public function orders_index_can_filter_by_amount_range_via_pipeline()
    {
        
        $order1 = Order::factory()->for($this->user)->create(['amount_decimal' => 50.00]);
        $order2 = Order::factory()->for($this->user)->create(['amount_decimal' => 150.00]); // Should be excluded
        $order3 = Order::factory()->for($this->user)->create(['amount_decimal' => 99.99]);
        $otherUserOrder = Order::factory()->create(['amount_decimal' => 75.00]); // Should be excluded (wrong user)

       
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/v1/orders?min_amount=40.00&max_amount=100.00');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data') // Expect order1 and order3
            ->assertJsonFragment(['code' => $order1->code])
            ->assertJsonFragment(['code' => $order3->code])
            ->assertJsonMissing(['code' => $order2->code])
            ->assertJsonMissing(['code' => $otherUserOrder->code]);
    }

    /** @test */
    public function orders_index_can_search_by_code_via_pipeline()
    {
      
        $order1 = Order::factory()->for($this->user)->create(['code' => 'ORD-RED-2025']);
        $order2 = Order::factory()->for($this->user)->create(['code' => 'ORD-BLUE-2025']); // Should be excluded
        $order3 = Order::factory()->for($this->user)->create(['code' => 'ORD-RED-9999']);
        $otherUserOrder = Order::factory()->create(['code' => 'ORD-RED-1111']); // Should be excluded (wrong user)

        
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/v1/orders?q=RED');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['code' => $order1->code])
            ->assertJsonFragment(['code' => $order3->code])
            ->assertJsonMissing(['code' => $order2->code])
            ->assertJsonMissing(['code' => $otherUserOrder->code]);
    }

    /** @test */
    public function orders_index_can_filter_by_placed_at_date_range_via_pipeline()
    {
        $dateA = now()->subDays(10);
        $dateB = now()->subDays(30); 
        $dateC = now()->subHours(12); 
        
        
        $order1 = Order::factory()->for($this->user)->create(['placed_at' => $dateA]); 
        $order2 = Order::factory()->for($this->user)->create(['placed_at' => $dateB]); 
        $order3 = Order::factory()->for($this->user)->create(['placed_at' => $dateC]);
        $otherUserOrder = Order::factory()->create(['placed_at' => $dateA]); 

      
        $dateFrom = now()->subDays(20)->format('Y-m-d');
        $dateTo = now()->subDays(5)->format('Y-m-d'); 

        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/v1/orders?date_from={$dateFrom}&date_to={$dateTo}");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') 
            ->assertJsonFragment(['code' => $order1->code])
            ->assertJsonMissing(['code' => $order2->code]) 
            ->assertJsonMissing(['code' => $order3->code]) 
            ->assertJsonMissing(['code' => $otherUserOrder->code]);
    }

    /** @test */
    public function orders_index_can_sort_by_placed_at_descending_and_amount_ascending()
    {
        
        
        $orderA_latest = Order::factory()->for($this->user)->create([
            'placed_at' => now()->subHours(1), 
            'amount_decimal' => 10.00
        ]);
        
        $orderB_middle = Order::factory()->for($this->user)->create([
            'placed_at' => now()->subHours(2), 
            'amount_decimal' => 50.00
        ]);
        
        $orderC_oldest = Order::factory()->for($this->user)->create([
            'placed_at' => now()->subHours(3), 
            'amount_decimal' => 100.00
        ]);

       
        $response_date = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/v1/orders?sort=-placed_at'); 

        $response_date->assertStatus(200)
            ->assertJsonPath('data.0.code', $orderA_latest->code)
            ->assertJsonPath('data.1.code', $orderB_middle->code)
            ->assertJsonPath('data.2.code', $orderC_oldest->code);


        $response_amount = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson('/api/v1/orders?sort=amount_decimal');

        $response_amount->assertStatus(200)
            ->assertJsonPath('data.0.code', $orderA_latest->code) 
            ->assertJsonPath('data.1.code', $orderB_middle->code) 
            ->assertJsonPath('data.2.code', $orderC_oldest->code); 
    }

    /** @test */
    public function orders_index_can_apply_all_filters_simultaneously()
    {
        
        $targetStatus = 'placed';
        $targetCodePart = 'MATCH';
        $minAmount = 50.00;
        $maxAmount = 150.00;
        $dateFrom = now()->subDays(10)->format('Y-m-d');
        $dateTo = now()->subDays(2)->format('Y-m-d');
        $insideDate = now()->subDays(5);

    
        $order1_included = Order::factory()->for($this->user)->create([
            'status' => $targetStatus,
            'code' => 'ORD-MATCH-XYZ',
            'amount_decimal' => 75.00,
            'placed_at' => $insideDate,
        ]);

        
        $order2_wrong_status = Order::factory()->for($this->user)->create([
            'status' => 'shipped',
            'code' => 'ORD-MATCH-ABC',
            'amount_decimal' => 80.00,
            'placed_at' => $insideDate,
        ]);

        
        $order3_wrong_amount = Order::factory()->for($this->user)->create([
            'status' => $targetStatus,
            'code' => 'ORD-MATCH-DEF',
            'amount_decimal' => 200.00, 
            'placed_at' => $insideDate,
        ]);

        
        $order4_wrong_date_code = Order::factory()->for($this->user)->create([
            'status' => $targetStatus,
            'code' => 'ORD-OTHER-GHI', 
            'amount_decimal' => 90.00,
            'placed_at' => now()->subDays(20), 
        ]);

        
        $order5_wrong_user = Order::factory()->create([
            'status' => $targetStatus,
            'code' => 'ORD-MATCH-JKL',
            'amount_decimal' => 85.00,
            'placed_at' => $insideDate,
        ]);


       
        $response = $this->withHeaders([
            'Authorization' => "Bearer {$this->token}",
        ])->getJson("/api/v1/orders?status={$targetStatus}&min_amount={$minAmount}&max_amount={$maxAmount}&q={$targetCodePart}&date_from={$dateFrom}&date_to={$dateTo}");

     
        
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data') 
            ->assertJsonFragment(['code' => $order1_included->code])
            ->assertJsonMissing(['code' => $order2_wrong_status->code])
            ->assertJsonMissing(['code' => $order3_wrong_amount->code])
            ->assertJsonMissing(['code' => $order4_wrong_date_code->code])
            ->assertJsonMissing(['code' => $order5_wrong_user->code]);
    }
}