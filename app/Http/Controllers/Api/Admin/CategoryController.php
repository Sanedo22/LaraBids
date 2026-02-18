<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Str;
use App\Http\Resources\CategoryResource;

class CategoryController extends Controller
{
    // All Categories
    public function index(Request $request)
    {
        $query = Category::withTrashed()
            ->with(['parent'])
            ->withCount('auctions')
            ->latest();
        if ($request->has('search')) {
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                    
                    if (is_numeric($search)) {
                        $q->orWhere('id', $search);
                    }
                });
            }
        }

        $categories = $query->get();

        return response()->json([
            'status' => true,
            'data'   => CategoryResource::collection($categories)
        ]);
    }

    // reate Category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|min:2|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'icon'      => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $category = Category::create([
            'name'      => trim($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'slug'      => Str::slug($validated['name']),
            'icon'      => $validated['icon'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Category Created Successfully',
            'data'    => new CategoryResource($category)
        ], 201);
    }

    // Show Single Category
    public function show($id)
    {
        $category = Category::withTrashed()
            ->with('parent')
            ->withCount('auctions')
            ->find($id);

        if (!$category) {
            return response()->json([
                'status'  => false,
                'message' => 'Category not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => new CategoryResource($category)
        ]);
    }

    //  Update Category
    public function update(Request $request, $id)
    {
        $category = Category::withTrashed()->find($id);

        if (!$category) {
            return response()->json([
                'status'  => false,
                'message' => 'Category not found'
            ], 404);
        }

        $validated = $request->validate([
            'name'      => 'required|string|min:2|max:255|unique:categories,name,' . $category->id,
            'parent_id' => 'nullable|exists:categories,id',
            'icon'      => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        $category->update([
            'name'      => trim($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'slug'      => Str::slug($validated['name']),
            'icon'      => $validated['icon'] ?? null,
            'is_active' => $validated['is_active'] ?? $category->is_active,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Category Updated Successfully',
            'data'    => new CategoryResource($category)
        ]);
    }

    // Soft Delete
    public function destroy($id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'status'  => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Category moved to trash'
        ]);
    }

    // Restore
    public function restore($id)
    {
        $category = Category::withTrashed()->find($id);

        if (!$category) {
            return response()->json([
                'status'  => false,
                'message' => 'Category not found'
            ], 404);
        }

        $category->restore();

        return response()->json([
            'status'  => true,
            'message' => 'Category restored successfully'
        ]);
    }

    //  Force Delete
    public function forceDelete($id)
    {
        $category = Category::withTrashed()->find($id);

        if (!$category) {
            return response()->json([
                'status'  => false,
                'message' => 'Category not found'
            ], 404);
        }

        if ($category->auctions()->exists()) {
            return response()->json([
                'status'  => false,
                'message' => 'Cannot permanently delete: Category has auctions'
            ], 422);
        }

        $category->forceDelete();

        return response()->json([
            'status'  => true,
            'message' => 'Category permanently deleted'
        ]);
    }
}
