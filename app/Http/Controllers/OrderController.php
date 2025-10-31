<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Pipeline\Pipeline;
use App\Http\Filters\OrdersIndexPipeline\OrderStatusFilter;
use App\Http\Filters\OrdersIndexPipeline\OrderAmountFilter;
use App\Http\Filters\OrdersIndexPipeline\OrderCodeSearchFilter;
use App\Http\Filters\OrdersIndexPipeline\OrderDateFilter;
use App\Http\Filters\OrdersIndexPipeline\OrderSortByDateOrAmount;
use App\Jobs\SendOrderStatusNotificationJob;



class OrderController extends Controller
{
    /**
     * @OA\Get(
     * path="/v1/orders",
     * tags={"Orders"},
     * security={{"bearerAuth": {}}},
     * summary="List all orders",
     * description="Fetches a paginated list of orders. Ability scope required: `orders:read`.",
     * @OA\Response(
     * response=200,
     * description="List of orders retrieved successfully",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Order")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthorized"),
     * @OA\Response(response=403, description="Forbidden (Missing ability: orders:read)"),
     * )
     */
    public function index()
    {
        $baseQuery = Order::query()->where('user_id', auth('sanctum')->id());
                            
        // Define the chain of filters to be applied
        $filters = [
            OrderStatusFilter::class,      
            OrderAmountFilter::class,      
            OrderCodeSearchFilter::class,  
            OrderDateFilter::class,   
            OrderSortByDateOrAmount::class,        
        ];


        $ordersQuery = app(Pipeline::class)
            ->send($baseQuery)
            ->through($filters)
            ->thenReturn();
            
        $orders= $ordersQuery->get();    
        
        return OrderResource::collection($orders);
    }
    /**
     * @OA\Post(
     * path="/v1/orders/{order}/notify",
     * tags={"Notifications"},
     * security={{"bearerAuth": {}}},
     * summary="Manually trigger notification",
     * description="Dispatches a job to send status change notifications. Ability scope required: `notify:send`.",
     * @OA\Parameter(
     * name="order",
     * in="path",
     * required=true,
     * @OA\Schema(type="integer", example=1),
     * description="ID of the order to notify"
     * ),
     * @OA\Response(
     * response=202,
     * description="Notification job successfully enqueued",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Notification job enqueued.")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthorized"),
     * @OA\Response(response=403, description="Forbidden (Missing ability: notify:send)"),
     * @OA\Response(response=404, description="Order not found"),
     * )
     */
    public function store(StoreOrderRequest $request)
    {
        $validated = $request->validated();

        $order = Order::create(array_merge($validated, [
            'user_id' => auth('sanctum')->id(),
            'placed_at' => now(),
        ]));

        return response()->json([
            'message' => 'Order placed successfully.',
            'order' => new OrderResource($order)
        ], 201); 
    }
    /**
     * @OA\Get(
     * path="/v1/orders/{order}",
     * tags={"Orders"},
     * security={{"bearerAuth": {"orders:read"}}},
     * summary="Get a single order",
     * description="Retrieves a specific order by ID. Requires 'orders:read' ability.",
     * @OA\Parameter(
     * name="order",
     * in="path",
     * required=true,
     * @OA\Schema(type="integer", example=101)
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(ref="#/components/schemas/Order")
     * ),
     * @OA\Response(response=401, description="Unauthorized"),
     * @OA\Response(response=403, description="Forbidden (Missing 'orders:read' ability)"),
     * @OA\Response(response=404, description="Order not found")
     * )
     */
    public function show($id)
    {
        //show order by id
        $order = Order::findOrFail($id);
        
        //return order
        return response()->json(new OrderResource($order));
    }

    /**
     * @OA\Patch(
     * path="/v1/orders/{order}/status",
     * tags={"Orders"},
     * security={{"bearerAuth": {"orders:write"}}},
     * summary="Update order status",
     * description="Updates the status of an existing order. Requires 'orders:write' ability.",
     * @OA\Parameter(
     * name="order",
     * in="path",
     * required=true,
     * @OA\Schema(type="integer", example=101)
     * ),
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"status"},
     * @OA\Property(
     * property="status",
     * type="string",
     * enum={"processing", "completed", "cancelled"},
     * example="completed"
     * )
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Status updated successfully",
     * @OA\JsonContent(ref="#/components/schemas/Order")
     * ),
     * @OA\Response(response=401, description="Unauthorized"),
     * @OA\Response(response=403, description="Forbidden (Missing 'orders:write' ability)"),
     * @OA\Response(response=404, description="Order not found"),
     * @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {

        //update order status
        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        // 4. PATCH /api/v1/orders/{id} — On status change, enqueue a job
        if ($oldStatus !==  $request->status) {
            SendOrderStatusNotificationJob::dispatch($order);
        }

        //return response with updated order
        return response()->json([
            'message' => 'Order status updated successfully.',
            'order' => new OrderResource($order)
        ]);
    }
    //notify order status
    public function notify(Order $order)
    {
         
        // Re-dispatching the job is the easiest way to manually trigger.
        SendOrderStatusNotificationJob::dispatch($order);

        return response()->json(['message' => 'Notification job enqueued.'], 202);
    }
    
}
