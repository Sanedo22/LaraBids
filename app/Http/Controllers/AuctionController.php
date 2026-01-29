<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    /**
     * Display a listing of auctions
     */
    public function index(Request $request)
    {
        $query = Auction::query()->where('status', 'active');

        // Search filter
        if ($request->has('q')) {
            $search = $request->input('q');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('category', function($catQ) use ($search) {
                      $catQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Category filter
        if ($request->has('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('category', function($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Price range filter
        if ($request->filled('min_price')) {
            $query->where('current_price', '>=', $request->input('min_price'));
        }
        if ($request->filled('max_price')) {
            $query->where('current_price', '<=', $request->input('max_price'));
        }

        // Sorting
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('current_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('current_price', 'desc');
                break;
            case 'ending_soon':
                $query->orderBy('end_time', 'asc');
                break;
            default:
                $query->latest();
                break;
        }

        // Paginate results
        $auctions = $query->with(['user', 'category'])
            ->paginate(9)
            ->withQueryString();

        // Get active categories for the filter bar
        $categories = Category::where('is_active', true)->get();

        return view('website.auctions.index', compact('auctions', 'categories'));
    }

    /**
     * Display the specified auction
     */
    public function show($id)
    {
        $auction = Auction::with(['user', 'category'])->findOrFail($id);
        return view('website.auctions.show', compact('auction'));
    }

    /**
     * Show the form for creating a new auction
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('website.auctions.create', compact('categories'));
    }

    /**
     * Store a newly created auction in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'starting_price' => ['required', 'numeric', 'min:0.01', 'max:999999999'],
            'start_time' => ['required', 'date'],
            'end_time' => ['required', 'date', 'after:start_time'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'document' => ['nullable', 'file', 'mimes:pdf,jpg,png,jpeg,doc,docx', 'max:5120'],
            'specifications' => ['nullable', 'array'],
        ], [
            'title.required' => 'Item title is required.',
            'title.min' => 'Title must be at least 3 characters.',
            'title.max' => 'Title must not exceed 255 characters.',
            'category_id.required' => 'Please select a category.',
            'category_id.exists' => 'Selected category is invalid.',
            'description.required' => 'Description is required.',
            'description.min' => 'Description must be at least 20 characters.',
            'description.max' => 'Description must not exceed 5000 characters.',
            'starting_price.required' => 'Starting price is required.',
            'starting_price.numeric' => 'Starting price must be a valid number.',
            'starting_price.min' => 'Starting price must be at least $0.01.',
            'starting_price.max' => 'Starting price is too high.',
            'start_time.required' => 'Auction start date and time is required.',
            'start_time.date' => 'Please enter a valid start date and time.',
            'end_time.required' => 'Auction end date and time is required.',
            'end_time.date' => 'Please enter a valid end date and time.',
            'end_time.after' => 'End time must be after the start time.',
            'image.image' => 'The file must be an image.',
            'image.mimes' => 'Image must be a JPEG, PNG, JPG, or GIF file.',
            'image.max' => 'Image size must not exceed 2MB.',
            'document.file' => 'The uploaded file is invalid.',
            'document.mimes' => 'Document must be a PDF, JPG, PNG, DOC, or DOCX file.',
            'document.max' => 'Document size must not exceed 5MB.',
        ]);

        $auction = new Auction();
        $auction->user_id = auth()->id();
        $auction->category_id = $request->category_id;
        $auction->title = $request->title;
        $auction->description = $request->description;
        $auction->starting_price = $request->starting_price;
        $auction->current_price = $request->starting_price;
        $auction->start_time = $request->start_time;
        $auction->end_time = $request->end_time;
        $auction->status = 'active';
        $auction->specifications = $request->specifications;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('auctions', 'public');
            $auction->image = $path;
        }

        if ($request->hasFile('document')) {
            $path = $request->file('document')->store('auctions/documents', 'public');
            $auction->document = $path;
        }

        $auction->save();

        return redirect()->route('auctions.show', $auction->id)
            ->with('success', 'Auction created successfully!');
    }



    /**
     * Search auctions
     */
    public function search(Request $request)
    {
        return $this->index($request);
    }
}

