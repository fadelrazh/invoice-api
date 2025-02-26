<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="API autentikasi pengguna"
 * )
 *
 * @OA\Components(
 *     @OA\Schema(
 *         schema="RegisterRequest",
 *         type="object",
 *         required={"name", "email", "password"},
 *         @OA\Property(property="name", type="string", example="Fadel Razsiah"),
 *         @OA\Property(property="email", type="string", format="email", example="fadelrazsiah@example.com"),
 *         @OA\Property(property="password", type="string", example="password123")
 *     ),
 *     @OA\Schema(
 *         schema="LoginRequest",
 *         type="object",
 *         required={"email", "password"},
 *         @OA\Property(property="email", type="string", format="email", example="fadelrazsiah@example.com"),
 *         @OA\Property(property="password", type="string", example="password123")
 *     ),
 *     @OA\Schema(
 *         schema="AuthResponse",
 *         type="object",
 *         @OA\Property(property="token", type="string", example="1|aLongGeneratedSanctumToken...")
 *     ),
 *     @OA\Schema(
 *         schema="Product",
 *         type="object",
 *         required={"name", "price"},
 *         @OA\Property(property="name", type="string", example="Jersey Futsal"),
 *         @OA\Property(property="price", type="number", example=150000)
 *     ),
 *     @OA\Schema(
 *         schema="CreateProductRequest",
 *         type="object",
 *         required={"name", "price", "stock"},
 *         @OA\Property(property="name", type="string", example="Jersey Futsal"),
 *         @OA\Property(property="price", type="number", format="float", example=150000),
 *         @OA\Property(property="stock", type="integer", example=50),
 *         @OA\Property(property="image_url", type="string", format="url", example="https://example.com/images/jersey.jpg")
 *     ),
 *     @OA\Schema(
 *         schema="UpdateProductRequest",
 *         type="object",
 *         @OA\Property(property="name", type="string", example="Jersey Futsal Pro Edition"),
 *         @OA\Property(property="price", type="number", format="float", example=175000),
 *         @OA\Property(property="stock", type="integer", example=30),
 *         @OA\Property(property="image_url", type="string", format="url", example="https://example.com/images/jersey-pro.jpg")
 *     ),
 *     @OA\Schema(
 *         schema="ProductDetailResponse",
 *         type="object",
 *         @OA\Property(property="success", type="boolean", example=true),
 *         @OA\Property(property="message", type="string", example="Product details retrieved successfully"),
 *         @OA\Property(property="data", ref="#/components/schemas/Product")
 *     ),
 *     @OA\Schema(
 *         schema="DeleteProductResponse",
 *         type="object",
 *         @OA\Property(property="message", type="string", example="Product successfully deleted")
 *     ),
 *     @OA\Schema(
 *         schema="Invoice",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="order_id", type="integer", example=2),
 *         @OA\Property(property="amount", type="number", format="float", example=100000),
 *         @OA\Property(property="invoice_date", type="string", format="date", example="2025-02-26"),
 *         @OA\Property(property="created_at", type="string", format="datetime", example="2025-02-26T12:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="datetime", example="2025-02-26T12:00:00Z")
 *     ),
 *     @OA\Schema(
 *         schema="Order",
 *         type="object",
 *         @OA\Property(property="id", type="integer", example=1),
 *         @OA\Property(property="customer_name", type="string", example="John Doe"),
 *         @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "cancelled"}, example="pending"),
 *         @OA\Property(property="total_price", type="number", format="float", example=50000),
 *         @OA\Property(property="created_at", type="string", format="datetime", example="2025-02-26T12:00:00Z"),
 *         @OA\Property(property="updated_at", type="string", format="datetime", example="2025-02-26T12:00:00Z")
 *     )
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *      path="/api/register",
     *      operationId="registerUser",
     *      tags={"Auth"},
     *      summary="Mendaftarkan pengguna baru",
     *      description="Endpoint untuk mendaftarkan akun baru menggunakan Sanctum",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/RegisterRequest")
     *      ),
     *      @OA\Response(response=201, description="Registrasi berhasil", @OA\JsonContent(ref="#/components/schemas/AuthResponse")),
     *      @OA\Response(response=400, description="Validasi gagal")
     * )
     */
    public function register(Request $request) {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'token' => $user->createToken('api_token')->plainTextToken
        ], 201);
    }

    /**
     * @OA\Post(
     *      path="/api/login",
     *      operationId="loginUser",
     *      tags={"Auth"},
     *      summary="Login pengguna",
     *      description="Endpoint untuk login dan mendapatkan token menggunakan Sanctum",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(ref="#/components/schemas/LoginRequest")
     *      ),
     *      @OA\Response(response=200, description="Login berhasil", @OA\JsonContent(ref="#/components/schemas/AuthResponse")),
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function login(Request $request) {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return response()->json([
            'token' => $user->createToken('api_token')->plainTextToken
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/logout",
     *      operationId="logoutUser",
     *      tags={"Auth"},
     *      summary="Logout pengguna",
     *      description="Menghapus token pengguna untuk logout",
     *      security={{"bearerAuth": {}}},
     *      @OA\Response(response=200, description="Logout berhasil"),
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout(Request $request) {
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }
}