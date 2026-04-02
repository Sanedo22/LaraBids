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
        $parent   = $request->get('parent', '');
        $type     = $request->get('type', 'all');    // all | top | sub
        $status   = $request->get('status', 'all'); // all | active | trashed
        $sort     = $request->get('sort', 'latest'); // latest | oldest | name_asc | name_desc

        $query = Category::withTrashed()
            ->with(['parent'])
            ->withCount('auctions');

        // Parent filter
        if ($parent !== '') {
            $query->where('parent_id', $parent);
        }

        // Type filter
        if ($type === 'top') {
            $query->whereNull('parent_id');
        } elseif ($type === 'sub') {
            $query->whereNotNull('parent_id');
        }

        // Status filter
        if ($status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($status === 'trashed') {
            $query->whereNotNull('deleted_at');
        }

        // Date filtering
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

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

        // Sort
        match ($sort) {
            'oldest'    => $query->oldest(),
            'name_asc'  => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default     => $query->latest(),
        };

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

    // Bulk Action
    public function bulkAction(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:categories,id',
            'action' => 'required|string|in:delete,restore,force_delete'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $ids = $request->ids;
        $action = $request->action;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            $categories = Category::withTrashed()->whereIn('id', $ids)->get();
            $processedCount = 0;
            $skippedCount = 0;

            foreach ($categories as $category) {
                if ($action === 'delete') {
                    $category->delete();
                    $processedCount++;
                } elseif ($action === 'restore') {
                    $category->restore();
                    $processedCount++;
                } elseif ($action === 'force_delete') {
                    if ($category->auctions()->exists()) {
                        $skippedCount++;
                        continue;
                    }
                    $category->forceDelete();
                    $processedCount++;
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            $actionMessage = match($action) {
                'delete' => 'moved to trash',
                'restore' => 'restored',
                'force_delete' => 'permanently deleted',
                default => 'processed'
            };

            $message = "{$processedCount} categor(ies) have been successfully {$actionMessage}.";
            if ($skippedCount > 0) {
                $message .= " {$skippedCount} categor(ies) were skipped because they have assigned auctions.";
            }

            return response()->json([
                'status' => true,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while performing bulk action.'
            ], 500);
        }
    }
}
