<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;
use App\Http\Requests\ContactRequest;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    // Home page
    public function index()
    {
        $auctions = Auction::active()
            ->latestFirst()
            ->take(3)
            ->with(['user', 'category', 'watchlists' => function($q) {
                if (auth()->check()) {
                    $q->where('user_id', auth()->id());
                } else {
                    $q->whereRaw('1 = 0');
                }
            }])
            ->get();
            
        $categories = Category::where('is_active', true)->get();
        $testimonials = \App\Models\Testimonial::where('is_active', true)->get();

        return view('website.index', compact('auctions', 'categories', 'testimonials'));
    }

    // Dashboard redirect
    public function dashboard()
    {
        if (auth()->user()->role === 'admin' || auth()->user()->role === 'super admin') {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect()->route('user.dashboard');
    }

    // About page
    public function about()
    {
        return view('website.about');
    }

    // Contact page
    public function contact()
    {
        return view('website.contact');
    }

    /**
     * Store contact form submission
     */
    public function contactStore(ContactRequest $request)
    {
        // Check if user is logged in
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Please login first to send us a message.')
                ->withInput();
        }

        \App\Models\Contact::create([
            'name' => $request->name,
            'email' => auth()->user()->email, // Use logged-in user's email
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'unread'
        ]);

        return back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }
}
