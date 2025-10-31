<?php
namespace App\Http\Controllers;
use App\Models\DeviceToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreNotificationTokenRequest;
use OpenApi\Attributes as OA;
class DeviceTokenController extends Controller
{
    /**
     * @OA\Post(
     * path="/v1/devices",
     * tags={"Devices"},
     * security={{"bearerAuth": {}}},
     * summary="Register a new device token",
     * description="Stores a new Firebase/FCM device token for the authenticated user. Ability scope required: `devices:write`.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"token"},
     * @OA\Property(property="token", type="string", example="fcm_token_12345ABCDEFG"),
     * @OA\Property(property="platform", type="string", enum={"ios", "android"}, example="android")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Device token stored successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Device token registered.")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthorized"),
     * @OA\Response(response=403, description="Forbidden (Missing ability: devices:write)"),
     * @OA\Response(response=422, description="Validation error"),
     * )
     */
    
    public function store(StoreNotificationTokenRequest $request)
    {

        $validated = $request->validated();
        $user = Auth::user();

        // 1. Save/update a device token, deduplicate per user+token
        $deviceToken = DeviceToken::updateOrCreate(
            [
                'user_id' => $user->id,
                'token' => $validated['token'],
            ],
            [
                'platform' => $validated['platform'],
                'last_seen_at' => now(),
                'is_valid' => true, 
            ]
        );

        return response()->json(['message' => 'Device token saved.', 'device' => $deviceToken], 201);
    }

    /**
     * @OA\Delete(
     * path="/v1/devices/{device}",
     * tags={"Devices"},
     * security={{"bearerAuth": {}}},
     * summary="Remove a device token",
     * description="Deletes a specific device token for the authenticated user. Ability scope required: `devices:write`.",
     * @OA\Parameter(
     * name="device",
     * in="path",
     * required=true,
     * @OA\Schema(type="integer", example=5),
     * description="ID of the device token record to delete"
     * ),
     * @OA\Response(
     * response=204,
     * description="Device token deleted successfully"
     * ),
     * @OA\Response(response=401, description="Unauthorized"),
     * @OA\Response(response=403, description="Forbidden (Missing ability: devices:write)"),
     * @OA\Response(response=404, description="Device token not found"),
     * )
     */
    public function destroy(DeviceToken $device)
    {

        if ($device->user_id !== Auth::id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $device->delete();

        return response()->json(['message' => 'Device token removed.'], 200);
    }
}