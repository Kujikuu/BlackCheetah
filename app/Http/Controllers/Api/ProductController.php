<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Franchise;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of products for the authenticated user's franchise.
     */
    public function index(Request $request, $franchise_id = null): JsonResponse
    {
        try {
            $user = auth()->user();

            // Use franchise from route parameter if provided, otherwise use user's franchise
            if ($franchise_id) {
                $franchise = Franchise::findOrFail($franchise_id);
            } else {
                $franchise = $user->franchise;
            }

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user.',
                ], 404);
            }

            // Ensure user has access to this franchise
            if ($franchise->franchisor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to franchise.',
                ], 403);
            }

            $query = $franchise->products()->latest();

            // Apply filters
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }

            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            if ($request->has('search') && $request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%'.$request->search.'%')
                        ->orWhere('description', 'like', '%'.$request->search.'%')
                        ->orWhere('sku', 'like', '%'.$request->search.'%');
                });
            }

            if ($request->has('low_stock') && $request->boolean('low_stock')) {
                $query->whereRaw('stock <= minimum_stock');
            }

            $products = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $products,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request, $franchise_id = null): JsonResponse
    {
        try {
            $user = auth()->user();

            // Use franchise from route parameter if provided, otherwise use user's franchise
            if ($franchise_id) {
                $franchise = Franchise::findOrFail($franchise_id);
            } else {
                $franchise = $user->franchise;
            }

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user.',
                ], 404);
            }

            // Ensure user has access to this franchise
            if ($franchise->franchisor_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to franchise.',
                ], 403);
            }

            $productData = $request->validated();
            $productData['franchise_id'] = $franchise->id;

            // Handle image upload
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time().'_'.Str::slug($productData['name']).'.'.$image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products/'.$franchise->id, $imageName, 'public');
                $productData['image'] = $imagePath;
            }

            $product = Product::create($productData);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully.',
                'data' => $product,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified product.
     */
    public function show($franchise_id, $product_id): JsonResponse
    {
        try {
            $user = auth()->user();

            // Resolve franchise and product from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $product = Product::findOrFail($product_id);

            // Check if user has access to the franchise
            if ($user->franchise?->id !== $franchise->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Verify the product belongs to the franchise
            if ($product->franchise_id !== $franchise->id) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            return response()->json($product);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, $franchise_id, $product_id): JsonResponse
    {
        try {
            $user = auth()->user();

            // Resolve franchise and product from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $product = Product::findOrFail($product_id);

            // Check if user has access to the franchise
            if ($user->franchise?->id !== $franchise->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Verify the product belongs to the franchise
            if ($product->franchise_id !== $franchise->id) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            $productData = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $image = $request->file('image');
                $imageName = time().'_'.Str::slug($productData['name'] ?? $product->name).'.'.$image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products/'.$product->franchise_id, $imageName, 'public');
                $productData['image'] = $imagePath;
            }

            $product->update($productData);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => $product->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     */
    public function destroy($franchise_id, $product_id): JsonResponse
    {
        try {
            $user = auth()->user();

            // Resolve franchise and product from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $product = Product::findOrFail($product_id);

            // Check if user has access to the franchise
            if ($user->franchise?->id !== $franchise->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Verify the product belongs to the franchise
            if ($product->franchise_id !== $franchise->id) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            // Delete product image if exists
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            // Delete the product record
            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete product.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get product categories for the authenticated user's franchise.
     */
    public function categories(): JsonResponse
    {
        try {
            $user = auth()->user();
            $franchise = $user->franchise;

            if (! $franchise) {
                return response()->json([
                    'success' => false,
                    'message' => 'No franchise found for this user.',
                ], 404);
            }

            $categories = $franchise->products()
                ->select('category')
                ->distinct()
                ->whereNotNull('category')
                ->pluck('category');

            return response()->json([
                'success' => true,
                'data' => $categories,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update product stock.
     */
    public function updateStock(Request $request, Product $product): JsonResponse
    {
        try {
            $user = auth()->user();

            // Check if the product belongs to the user's franchise
            if ($product->franchise_id !== $user->franchise?->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Product not found.',
                ], 404);
            }

            $validatedData = $request->validate([
                'stock' => ['required', 'integer', 'min:0'],
                'operation' => ['required', 'string', 'in:set,add,subtract'],
            ]);

            switch ($validatedData['operation']) {
                case 'set':
                    $product->stock = $validatedData['stock'];
                    break;
                case 'add':
                    $product->stock += $validatedData['stock'];
                    break;
                case 'subtract':
                    $product->stock = max(0, $product->stock - $validatedData['stock']);
                    break;
            }

            $product->save();

            return response()->json([
                'success' => true,
                'message' => 'Stock updated successfully.',
                'data' => $product->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
