<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="E-Commerce API",
 *      description="Dokumentasi API untuk aplikasi e-commerce"
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="Server utama"
 * )
 *
 * @OA\Tag(
 *     name="Products",
 *     description="API untuk mengelola produk"
 * )
 * 
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except(['index', 'show']);
    }

    /**
     * @OA\Get(
     *      path="/api/products",
     *      operationId="getProducts",
     *      tags={"Products"},
     *      summary="Menampilkan daftar produk",
     *      description="Mengembalikan daftar produk dalam format JSON",
     *      @OA\Response(
     *          response=200,
     *          description="Daftar produk berhasil diambil",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(ref="#/components/schemas/Product")
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/api/products",
     *      operationId="createProduct",
     *      tags={"Products"},
     *      summary="Menambahkan produk baru",
     *      description="Menyimpan produk baru ke database",
     *      security={{ "BearerToken":{} }},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "price"},
     *              @OA\Property(property="name", type="string", example="Jersey Futsal"),
     *              @OA\Property(property="price", type="number", format="double", example=150000)
     *          )
     *      ),
     *      @OA\Response(response=201, description="Produk berhasil ditambahkan"),
     *      @OA\Response(response=400, description="Validasi gagal"),
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $product = Product::create($request->all());

        return response()->json($product, Response::HTTP_CREATED);
    }

    /**
     * @OA\Get(
     *      path="/api/products/{id}",
     *      operationId="getProductById",
     *      tags={"Products"},
     *      summary="Menampilkan detail produk berdasarkan ID",
     *      description="Mengembalikan detail produk dalam format JSON",
     *      @OA\Parameter(
     *          name="id",
     *          description="ID produk",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Detail produk berhasil diambil",
     *          @OA\JsonContent(ref="#/components/schemas/Product")
     *      ),
     *      @OA\Response(response=404, description="Produk tidak ditemukan")
     * )
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        return response()->json($product, Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/api/products/{id}",
     *      operationId="updateProduct",
     *      tags={"Products"},
     *      summary="Memperbarui data produk berdasarkan ID",
     *      description="Mengupdate data produk di database",
     *      security={{ "BearerToken":{} }},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID produk",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"name", "price"},
     *              @OA\Property(property="name", type="string", example="Jersey Futsal Updated"),
     *              @OA\Property(property="price", type="number", format="double", example=175000)
     *          )
     *      ),
     *      @OA\Response(response=200, description="Produk berhasil diperbarui"),
     *      @OA\Response(response=400, description="Validasi gagal"),
     *      @OA\Response(response=404, description="Produk tidak ditemukan"),
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $product->update($request->all());

        return response()->json($product, Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/api/products/{id}",
     *      operationId="deleteProduct",
     *      tags={"Products"},
     *      summary="Menghapus produk berdasarkan ID",
     *      description="Menghapus produk dari database",
     *      security={{ "BearerToken":{} }},
     *      @OA\Parameter(
     *          name="id",
     *          description="ID produk",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(response=200, description="Produk berhasil dihapus"),
     *      @OA\Response(response=404, description="Produk tidak ditemukan"),
     *      @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json(['message' => 'Produk tidak ditemukan'], Response::HTTP_NOT_FOUND);
        }

        $product->delete();

        return response()->json(['message' => 'Produk berhasil dihapus'], Response::HTTP_OK);
    }
}