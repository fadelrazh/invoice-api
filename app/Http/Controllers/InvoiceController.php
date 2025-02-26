<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Invoices",
 *     description="API untuk mengelola invoice"
 * )
 */
class InvoiceController extends Controller
{
    /**
     * 
     * @OA\Post(
     *     path="/api/invoices/{order_id}",
     *     summary="Generate invoice dari order yang telah selesai",
     *     tags={"Invoices"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="order_id",
     *         in="path",
     *         required=true,
     *         description="ID Order yang akan dibuatkan invoice",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="discount", type="number", format="float", example=10.5),
     *             @OA\Property(property="custom_price", type="number", format="float", example=150000),
     *             @OA\Property(property="invoice_date", type="string", format="date", example="2025-02-26")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invoice berhasil dibuat"),
     *             @OA\Property(property="invoice", type="object", ref="#/components/schemas/Invoice")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Order belum selesai atau total harga negatif")
     * )
     */
    public function generateInvoice($order_id, Request $request)
    {
        $request->validate([
            'discount' => 'nullable|numeric|min:0',
            'custom_price' => 'nullable|numeric|min:0', 
            'invoice_date' => 'nullable|date' 
        ]);

        $order = Order::findOrFail($order_id);

        if ($order->status !== 'completed') {
            return response()->json(['error' => 'Order belum selesai, tidak bisa dibuat invoice'], 400);
        }

        $final_price = $request->custom_price ?? ($order->total_price - ($request->discount ?? 0));

        if ($final_price < 0) {
            return response()->json(['error' => 'Total harga tidak boleh negatif'], 400);
        }

        $invoice = Invoice::create([
            'order_id' => $order->id,
            'amount' => $final_price,
            'invoice_date' => $request->invoice_date ?? now()
        ]);

        return response()->json(['message' => 'Invoice berhasil dibuat', 'invoice' => $invoice]);
    }

    /**
     * 
     * @OA\Get(
     *     path="/api/invoices",
     *     summary="Ambil daftar semua invoice",
     *     tags={"Invoices"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Daftar invoice berhasil diambil",
     *         @OA\JsonContent(
     *             @OA\Property(property="invoices", type="array", @OA\Items(ref="#/components/schemas/Invoice"))
     *         )
     *     )
     * )
     */
    public function listInvoices()
    {
        $invoices = Invoice::with('order')->get();
        return response()->json(['invoices' => $invoices]);
    }
}