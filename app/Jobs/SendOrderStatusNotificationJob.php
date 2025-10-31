<?php   
namespace App\Jobs;

use App\Models\Order;
use App\Services\FcmService;
use App\Models\DeviceToken;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderStatusNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(FcmService $fcm): void
    {
        $user = $this->order->user;
        $tokens = $user->deviceTokens;

        foreach ($tokens as $deviceToken) {
            $notification = [
                'title' => "Order {$this->order->code} is {$this->order->status}",
                'body' => "Amount {$this->order->amount_decimal} - Updated at {$this->order->updated_at->toDateTimeString()}",
            ];

            $data = [
                'order_id' => (string) $this->order->id,
                'status' => (string) $this->order->status,
                'code' => (string) $this->order->code,
            ];

            $result = $fcm->sendToToken($deviceToken->token, $notification, $data);

            $fcm->logNotification(
                $user,
                $this->order,
                $deviceToken,
                $result['payload'],
                $result,
                $result['success']
            );
        }
    }
}
