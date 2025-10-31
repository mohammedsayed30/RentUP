<?php
namespace App\Services;

use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;
use App\Models\NotificationLog;
use App\Models\DeviceToken;
use Carbon\Carbon;

class FcmService
{
    protected string $endpoint;
    protected string $serviceAccountPath;
    protected GoogleClient $googleClient;

    public function __construct()
    {
        $this->endpoint = config('services.fcm.endpoint');
        $this->serviceAccountPath = base_path(config('services.fcm.service_account_path'));

        $this->googleClient = new GoogleClient();
        $this->googleClient->setAuthConfig($this->serviceAccountPath);
        $this->googleClient->addScope('https://www.googleapis.com/auth/firebase.messaging');
    }

    protected function getAccessToken(): string
    {
        $token = $this->googleClient->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }

    public function sendToToken(string $token, array $notification, array $data = []): array
    {
        $payload = [
            'message' => [
                'token' => $token,
                'notification' => $notification,
                'data' => $data,
            ],
        ];

        $accessToken = $this->getAccessToken();

        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->post($this->endpoint, $payload);

        return [
            'success' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json(),
            'payload' => $payload,
        ];
    }

    public function logNotification($user, $order, $deviceToken, array $payload, array $response, bool $success)
    {
        NotificationLog::create([
            'user_id' => $user->id,
            'order_id' => $order->id,
            'device_token_id' => $deviceToken?->id,
            'payload' => $payload,
            'response' => $response,
            'status' => $success ? 'success' : 'failed',
            'sent_at' => now(),
        ]);

        // handle invalid tokens
        if (!$success && str_contains(json_encode($response['body']), 'error')) {
            $deviceToken?->delete();
        }
    }
}
