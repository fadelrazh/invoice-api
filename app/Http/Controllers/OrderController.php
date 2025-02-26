<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API untuk mengelola pesanan"
 * )
 */
class OrderController extends Controller
{
    /**
     * 
     * @OA\Post(
     *     path="/api/orders",
     *     summary="Membuat order baru",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="customer_name", type="string", example="John Doe"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer", example=1),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Order berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Order berhasil dibuat"),
     *             @OA\Property(property="order", type="object", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Stock tidak cukup untuk produk atau validasi gagal"),
     *     @OA\Response(response=500, description="Terjadi kesalahan saat membuat order")
     * )
     */
    public function createOrder(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $total_price = 0;

            $order = Order::create([
                'customer_name' => $request->customer_name,
                'status' => 'pending',
                'total_price' => 0
            ]);

            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    return response()->json(['error' => 'Stock tidak cukup untuk produk: ' . $product->name], 400);
                }

                $subtotal = $product->price * $item['quantity'];
                $total_price += $subtotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'subtotal' => $subtotal
                ]);

                $product->decrement('stock', $item['quantity']);
            }

            $order->update(['total_price' => $total_price]);

            DB::commit();
            return response()->json(['message' => 'Order berhasil dibuat', 'order' => $order], 201);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Terjadi kesalahan saat membuat order', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * 
     * @OA\Put(
     *     path="/api/orders/{id}/status",
     *     summary="Memperbarui status order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID Order yang akan diperbarui statusnya",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", enum={"pending", "processing", "completed", "cancelled"}, example="completed")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status order berhasil diperbarui",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Status order berhasil diperbarui"),
     *             @OA\Property(property="order", type="object", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Order tidak ditemukan"),
     *     @OA\Response(response=400, description="Status order tidak valid")
     * )
     */
    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return response()->json(['message' => 'Status order berhasil diperbarui', 'order' => $order]);
    }

    /**
     * 
     * @OA\Post(
     *     path="/api/orders/{id}/payment",
     *     summary="Memproses pembayaran order",
     *     tags={"Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID Order yang akan diproses pembayarannya",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pembayaran berhasil diproses",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Pembayaran berhasil diproses"),
     *             @OA\Property(property="order", type="object", ref="#/components/schemas/Order")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Order sudah diproses sebelumnya"),
     *     @OA\Response(response=404, description="Order tidak ditemukan")
     * )
     */
    public function processPayment($id)
    {
        $order = Order::findOrFail($id);

        if ($order->status !== 'pending') {
            return response()->json(['error' => 'Order sudah diproses sebelumnya'], 400);
        }

        $order->update(['status' => 'completed']);

        return response()->json(['message' => 'Pembayaran berhasil diproses', 'order' => $order]);
    }
}