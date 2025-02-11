<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        try {
            // Log the incoming request data
            \Log::info('Product creation request:', [
                'request_data' => $request->all(),
                'has_files' => $request->hasFile('images'),
                'files' => $request->file('images')
            ]);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string',
                'description' => 'required|string',
                'datetime' => 'required|date',
                'images.*' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            \Log::info('Validation passed, creating product');

            $product = Product::create([
                'name' => $validated['name'],
                'category' => $validated['category'],
                'description' => $validated['description'],
                'datetime' => $validated['datetime']
            ]);

            \Log::info('Product created:', ['product_id' => $product->id]);

            if ($request->hasFile('images')) {
                \Log::info('Processing images');
                foreach ($request->file('images') as $index => $image) {
                    \Log::info('Processing image ' . ($index + 1), [
                        'original_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getMimeType(),
                        'size' => $image->getSize()
                    ]);

                    $path = $image->store('products', 'public');
                    
                    if (!$path) {
                        \Log::error('Failed to store image ' . ($index + 1));
                        throw new \Exception('Failed to upload image ' . ($index + 1));
                    }

                    \Log::info('Image stored successfully', ['path' => $path]);
                    
                    $product->images()->create(['path' => $path]);
                }
            }

            \Log::info('Product creation completed successfully');

            return response()->json($product, 201);
        } catch (\Exception $e) {
            \Log::error('Product creation error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error creating product',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        try {
            $query = Product::query();

            if ($request->search) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', "%{$request->search}%")
                      ->orWhere('description', 'like', "%{$request->search}%");
                });
            }

            if ($request->category) {
                $query->where('category', $request->category);
            }

            $products = $query->paginate(10);

            return response()->json([
                'message' => 'Products retrieved successfully',
                'products' => $products->load('images')
            ]);
        } catch (\Exception $e) {
            \Log::error('Product fetch error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error fetching products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            \Log::info('Product update request:', [
                'id' => $id,
                'request_data' => $request->all(),
                'has_files' => $request->hasFile('images'),
                'files' => $request->file('images')
            ]);

            $product = Product::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'category' => 'sometimes|required|string',
                'description' => 'sometimes|required|string',
                'datetime' => 'sometimes|required|date',
                'images.*' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
                'existing_images' => 'sometimes|array'
            ]);

            // Handle datetime in both ISO and custom format
            if (isset($validated['datetime'])) {
                try {
                    $validated['datetime'] = Carbon::parse($request->datetime)->format('Y-m-d H:i:s');
                } catch (\Exception $e) {
                    return response()->json([
                        'message' => 'Invalid datetime format',
                        'error' => 'Please provide datetime in ISO format (e.g., 2025-02-11T17:08:00Z)'
                    ], 422);
                }
            }

            // Update basic product info
            $product->update($validated);

            // Handle images
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    \Log::info('Processing new image', [
                        'original_name' => $image->getClientOriginalName(),
                        'mime_type' => $image->getMimeType(),
                        'size' => $image->getSize()
                    ]);

                    $path = $image->store('products', 'public');
                    $product->images()->create(['path' => $path]);
                }
            }

            // Handle existing images
            if ($request->has('existing_images')) {
                $existingImagePaths = collect($request->existing_images)->pluck('path')->toArray();
                
                // Delete images that are no longer in the existing_images array
                $product->images()
                       ->whereNotIn('path', $existingImagePaths)
                       ->get()
                       ->each(function ($image) {
                           Storage::disk('public')->delete($image->path);
                           $image->delete();
                       });
            }

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product->load('images')
            ]);

        } catch (\Exception $e) {
            \Log::error('Product update error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error updating product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            \Log::info('Product delete request:', ['id' => $id]);

            $product = Product::findOrFail($id);

            // Delete associated images from storage
            foreach ($product->images as $image) {
                \Log::info('Deleting image:', ['path' => $image->path]);
                
                try {
                    Storage::disk('public')->delete($image->path);
                } catch (\Exception $e) {
                    \Log::warning('Failed to delete image file: ' . $e->getMessage(), [
                        'path' => $image->path,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Delete the product (this will also delete related images due to cascade)
            $product->delete();

            \Log::info('Product deleted successfully', ['id' => $id]);

            return response()->json([
                'message' => 'Product deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('Product not found:', ['id' => $id]);
            return response()->json([
                'message' => 'Product not found',
                'error' => 'The requested product does not exist'
            ], 404);

        } catch (\Exception $e) {
            \Log::error('Error deleting product: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Error deleting product',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}