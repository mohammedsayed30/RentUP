<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\LoginUserRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use OpenApi\Attributes as OA;

class UserController extends Controller
{
    //define abilities
    public const ABILITIES = [
        'orders:read',
        'orders:write',
        'notify:send',
        'devices:write'
    ];
    
    
    /**
     * @OA\Post(
     * path="/v1/auth/register",
     * tags={"Auth"},
     * summary="Register a new user",
     * description="Creates a new user account and issues a Sanctum API token.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"name", "email", "password"},
     * @OA\Property(property="name", type="string", example="Jane Doe"),
     * @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="secret123")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="User registered successfully",
     * @OA\JsonContent(
     * @OA\Property(property="token", type="string", description="Sanctum API token"),
     * @OA\Property(property="user", ref="#/components/schemas/User")
     * )
     * ),
     * @OA\Response(response=422, description="Validation error")
     * )
     */
    public function register(RegisterUserRequest $request)
    {
       
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), //security purpose
        ]);

        // automatic login after register with orders:read, orders:write abilities
        $token = $user->createToken('AuthToken',self::ABILITIES)->plainTextToken;

        return response()->json([
            'message' => 'User successfully registered.',
            'user' => new UserResource($user),
            'token' => $token,
        ], 201);
    }

    /**
     * @OA\Post(
     * path="/v1/auth/login",
     * tags={"Auth"},
     * summary="Login and get API token",
     * description="Authenticates the user and returns an API token.",
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * required={"email", "password", "device_name"},
     * @OA\Property(property="email", type="string", format="email", example="jane@example.com"),
     * @OA\Property(property="password", type="string", format="password", example="secret123"),
     * @OA\Property(property="device_name", type="string", example="iPhone 15 Pro")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Login successful",
     * @OA\JsonContent(
     * @OA\Property(property="token", type="string", description="Sanctum API token"),
     * @OA\Property(property="user", ref="#/components/schemas/User")
     * )
     * ),
     * @OA\Response(response=401, description="Invalid credentials")
     * )
     */
    public function login(LoginUserRequest $request)
    {
        
        
        $user = User::where('email', $request->email)->first();

        //check user and password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        //generate token
        $token = $user->createToken('AuthToken',self::ABILITIES)->plainTextToken;

        return response()->json([
            'message' => 'User successfully logged in.',
            'user' => new UserResource($user),
            'token' => $token,
        ], 200);
        
    }

    /**
     * @OA\Post(
     * path="/v1/auth/logout",
     * tags={"Auth"},
     * security={{"bearerAuth": {}}},
     * summary="Logout user",
     * description="Revokes the current access token, logging the user out of the current device.",
     * @OA\Response(
     * response=200,
     * description="Logout successful",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="User successfully logged out.")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request)
    {
        // Revoke the token that was used to authenticate the current request...
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User successfully logged out.'
        ], 200);
    }

    /**
     * @OA\Get(
     * path="/v1/auth/me",
     * tags={"Auth"},
     * security={{"bearerAuth": {}}},
     * summary="Get current authenticated user",
     * description="Returns the details of the currently authenticated user based on the provided token.",
     * @OA\Response(
     * response=200,
     * description="Current user details retrieved",
     * @OA\JsonContent(
     * @OA\Property(property="user", ref="#/components/schemas/User")
     * )
     * ),
     * @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function currentUser(Request $request)
    {
        // check authenticated user
        if (!$request->user()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        return response()->json([
            'user' => new UserResource($request->user())
        ], 200);
    }
}
