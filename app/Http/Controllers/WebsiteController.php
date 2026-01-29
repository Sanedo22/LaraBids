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
            ->with(['user', 'category'])
            ->get();
            
        $categories = Category::where('is_active', true)->get();

        return view('website.index', compact('auctions', 'categories'));
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

    // Store contact
    public function contactStore(ContactRequest $request)
    {
        // TODO: Implement contact form logic (send email, save to database, etc.)
        
        return back()->with('success', 'Thank you for contacting us! We will get back to you soon.');
    }
}
