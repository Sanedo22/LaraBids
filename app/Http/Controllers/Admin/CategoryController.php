<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $parent   = $request->get('parent', '');
            $type     = $request->get('type', 'all');    // all | top | sub
            $status   = $request->get('status', 'all'); // all | active | trashed
            $sort     = $request->get('sort', 'latest'); // latest | oldest | name_asc | name_desc

            $data = Category::withTrashed()->with(['parent'])->withCount('auctions');

            // Parent filter
            if ($parent !== '') {
                $data->where('parent_id', $parent);
            }

            // Type filter
            if ($type === 'top') {
                $data->whereNull('parent_id');
            } elseif ($type === 'sub') {
                $data->whereNotNull('parent_id');
            }

            // Status filter
            if ($status === 'active') {
                $data->whereNull('deleted_at');
            } elseif ($status === 'trashed') {
                $data->whereNotNull('deleted_at');
            }

            // Date filtering
            if ($request->filled('start_date')) {
                $data->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $data->whereDate('created_at', '<=', $request->end_date);
            }

            // Sort
            match ($sort) {
                'oldest'    => $data->oldest(),
                'name_asc'  => $data->orderBy('name', 'asc'),
                'name_desc' => $data->orderBy('name', 'desc'),
                default     => $data->latest(),
            };

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('icon', function($row){
                    $icon = $row->icon ?? 'fas fa-tag';
                    return '<i class="'.$icon.' text-primary"></i>';
                })
                ->addColumn('parent', function($row){
                    return $row->parent ? $row->parent->name : '<span class="text-muted">—</span>';
                })
                ->addColumn('count', function($row){
                    return '<span class="badge badge-info badge-pill px-2 py-1">'.$row->auctions_count.' Items</span>';
                })
                ->addColumn('action', function($row){
                    $btn = '<div class="d-flex justify-content-center gap-1">';

                    // View/Edit (only if not deleted)
                    if(!$row->trashed()){
                        $btn .= '<a href="'.route('admin.categories.edit', $row->id).'" class="btn btn-outline-primary btn-sm btn-action" title="Edit"><i class="fas fa-edit"></i></a>';

                        // Soft Delete
                        $btn .= '<button type="button" class="btn btn-outline-danger btn-sm delete-category btn-action" data-id="'.$row->id.'" data-url="'.route('admin.categories.destroy', $row->id).'" title="Move to Trash"><i class="fas fa-trash"></i></button>';
                    } else {
                        // Restore
                        $btn .= '<button type="button" class="btn btn-outline-success btn-sm restore-category btn-action" data-id="'.$row->id.'" data-url="'.route('admin.categories.restore', $row->id).'" title="Restore"><i class="fas fa-trash-restore"></i></button>';

                        // Force Delete (Only if no auctions)
                        if ($row->auctions_count == 0 || $row->auctions()->count() == 0) {
                            $btn .= '<button type="button" class="btn btn-outline-danger btn-sm force-delete-category btn-action" data-id="'.$row->id.'" data-url="'.route('admin.categories.force_delete', $row->id).'" title="Permanent Delete"><i class="fas fa-times"></i></button>';
                        } else {
                            $btn .= '<button type="button" class="btn btn-outline-secondary btn-sm btn-action" disabled title="Cannot delete: Has Auctions"><i class="fas fa-times"></i></button>';
                        }
                    }

                    $btn .= '</div>';
                    return $btn;
                })
                ->rawColumns(['icon', 'parent', 'count', 'action'])
                ->make(true);
        }

        $parentCategories = Category::whereNull('parent_id')->orderBy('name')->get();

        return view('admin.categories.index', [
            'total_categories'  => Category::count(),
            'top_categories'    => Category::whereNull('parent_id')->count(),
            'sub_categories'    => Category::whereNotNull('parent_id')->count(),
            'trashed_categories'=> Category::onlyTrashed()->count(),
            'parentCategories'  => $parentCategories,
        ]);
    }

    public function create()
    {
        $parentCategories = Category::topLevel()->active()->orderBy('name')->get();
        return view('admin.categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|min:2|max:255|unique:categories,name',
            'parent_id' => 'nullable|exists:categories,id',
            'icon'      => 'nullable|string|max:50',
            'is_active' => 'nullable|boolean',
        ]);

        Category::create([
            'name'      => trim($validated['name']),
            'parent_id' => $validated['parent_id'] ?? null,
            'slug'      => Str::slug($validated['name']),
            'icon'      => $validated['icon'] ?? null,
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category created successfully');
    }

    public function edit($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $parentCategories = Category::topLevel()->where('id', '!=', $id)->active()->orderBy('name')->get();
        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::withTrashed()->findOrFail($id);

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
        ]);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        if (request()->ajax()) {
            return response()->json(['success' => 'Category moved to trash']);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Category moved to trash');
    }

    public function restore($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        $category->restore();

        if (request()->ajax()) {
            return response()->json(['success' => 'Category restored successfully']);
        }

        return redirect()->back()->with('success', 'Category restored successfully');
    }

    public function forceDelete($id)
    {
        $category = Category::withTrashed()->findOrFail($id);

        // Check if category has any auctions (even soft deleted ones if needed, but usually we check current relation)
        if ($category->auctions()->exists()) {
             return response()->json(['error' => 'Cannot permanently delete this category because it is assigned to auctions. Please delete the auctions first or keep this category in trash to preserve history.'], 422);
        }

        $category->forceDelete();

        if (request()->ajax()) {
            return response()->json(['success' => 'Category permanently deleted']);
        }

        return redirect()->back()->with('success', 'Category permanently deleted');
    }
}
