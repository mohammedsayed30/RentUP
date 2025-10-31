<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 * version="1.0.0",
 * title="RentUp API Documentation",
 * description="OpenAPI 3.x documentation for the RentUp application.",
 * @OA\Contact(
 * email="support@rentup.com"
 * )
 * )
 *
 * @OA\Server(
 * url=L5_SWAGGER_CONST_HOST,
 * description="Primary API Server"
 * )
 *
 * @OA\SecurityScheme(
 * securityScheme="bearerAuth",
 * type="http",
 * scheme="bearer",
 * bearerFormat="Sanctum Token",
 * description="Global bearerAuth security scheme. Use your Sanctum API token."
 * )
 *
 * @OA\Tag(name="Auth", description="User registration, login, logout, and profile management.")
 * @OA\Tag(name="Orders", description="CRUD operations and status updates for orders.")
 * @OA\Tag(name="Devices", description="Managing device tokens for push notifications (FCM).")
 * @OA\Tag(name="Notifications", description="Manually trigger notifications for orders.")
 */
class ApiDocsController extends Controller
{
    // This controller is just for annotations; no methods are required.
}