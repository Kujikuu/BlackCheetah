<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
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

            // Check if user has access to the franchise (user must be the franchisor)
            if ($franchise->franchisor_id !== $user->id) {
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
    public function update(Request $request, $franchise_id, $product_id): JsonResponse
    {
        try {
            $user = auth()->user();

            // Resolve franchise and product from IDs
            $franchise = Franchise::findOrFail($franchise_id);
            $product = Product::findOrFail($product_id);

            // Check if user has access to the franchise (user must be the franchisor)
            if ($franchise->franchisor_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Verify the product belongs to the franchise
            if ($product->franchise_id !== $franchise->id) {
                return response()->json(['message' => 'Product not found'], 404);
            }

            // Handle both regular form data and FormData
            if ($request->isJson()) {
                // Handle JSON request
                $validatedData = $request->validate([
                    'name' => ['sometimes', 'required', 'string', 'max:255'],
                    'description' => ['nullable', 'string', 'max:1000'],
                    'category' => ['sometimes', 'required', 'string', 'max:100'],
                    'unit_price' => ['sometimes', 'required', 'numeric', 'min:0', 'max:999999.99'],
                    'cost_price' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
                    'stock' => ['sometimes', 'required', 'integer', 'min:0'],
                    'minimum_stock' => ['nullable', 'integer', 'min:0'],
                    'sku' => ['sometimes', 'required', 'string', 'max:100', "unique:products,sku,{$product_id}"],
                    'status' => ['sometimes', 'required', 'string', 'in:active,inactive,discontinued'],
                    'weight' => ['nullable', 'numeric', 'min:0'],
                    'dimensions' => ['nullable', 'array'],
                    'dimensions.length' => ['nullable', 'numeric', 'min:0'],
                    'dimensions.width' => ['nullable', 'numeric', 'min:0'],
                    'dimensions.height' => ['nullable', 'numeric', 'min:0'],
                    'image' => ['nullable', 'image', 'max:5120'], // 5MB max
                    'attributes' => ['nullable', 'array'],
                ]);
            } else {
                // Handle FormData (multipart/form-data)

                // Check if FormData is empty
                if ($request->all() === []) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No data received. Please ensure all required fields are filled.',
                    ], 422);
                }

                $validatedData = [
                    'name' => $request->input('name'),
                    'description' => $request->input('description'),
                    'category' => $request->input('category'),
                    'unit_price' => $request->input('unit_price'),
                    'stock' => $request->input('stock'),
                    'status' => $request->input('status'),
                    'sku' => $request->input('sku'),
                ];

                // Validate fields if they exist in FormData (allow partial updates)
                $rules = [
                    'name' => ['sometimes', 'required', 'string', 'max:255'],
                    'category' => ['sometimes', 'required', 'string', 'max:100'],
                    'unit_price' => ['sometimes', 'required', 'numeric', 'min:0'],
                    'stock' => ['sometimes', 'required', 'integer', 'min:0'],
                    'status' => ['sometimes', 'required', 'string', 'in:active,inactive,discontinued'],
                    'sku' => ['sometimes', 'required', 'string', 'max:100', "unique:products,sku,{$product_id}"],
                ];

                // Only validate fields that are actually present in the request
                $actualRules = [];
                foreach ($rules as $field => $rule) {
                    if ($request->has($field)) {
                        $actualRules[$field] = $rule;
                    }
                }

                if (! empty($actualRules)) {
                    $request->validate($actualRules);
                }
            }

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($product->image && Storage::disk('public')->exists($product->image)) {
                    Storage::disk('public')->delete($product->image);
                }

                $image = $request->file('image');
                $imageName = time().'_'.Str::slug($validatedData['name'] ?? $product->name).'.'.$image->getClientOriginalExtension();
                $imagePath = $image->storeAs('products/'.$product->franchise_id, $imageName, 'public');
                $validatedData['image'] = $imagePath;
            }

            $product->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'data' => $product->fresh(),
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
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

            // Check if user has access to the franchise (user must be the franchisor)
            if ($franchise->franchisor_id !== $user->id) {
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

            // Check if the product belongs to the user's franchise (user must be the franchisor)
            $franchise = Franchise::find($product->franchise_id);
            if (! $franchise || $franchise->franchisor_id !== $user->id) {
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
