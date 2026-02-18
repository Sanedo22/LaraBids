<?php

namespace App\Http\Controllers\Api\Website;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Contact;
use App\Models\User;
use App\Models\Testimonial;
use App\Http\Resources\AuctionResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\TestimonialResource;
use App\Http\Requests\ContactRequest;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    /**
     * Get home page data
     * Returns latest 3 active auctions with user, category, and images
     */
    public function index()
    {
        $auctions = Auction::active()
            ->latestFirst()
            ->take(3)
            ->with(['user', 'category', 'images'])
            ->get();

        $testimonials = Testimonial::where('is_active', true)->take(5)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'auctions' => AuctionResource::collection($auctions),
                'testimonials' => TestimonialResource::collection($testimonials)
            ]
        ]);
    }

    /**
     * Store contact form submission
     */
    public function contactStore(ContactRequest $request)
    {
        $contact = Contact::create([
            'name' => $request->name,
            'email' => auth()->check() ? auth()->user()->email : $request->email,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'unread'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us! We will get back to you soon.',
            'data' => $contact
        ], 201);
    }

    /**
     * Get public seller profile and their active auctions
     */
    public function sellerProfile($id)
    {
        $seller = User::with(['auctions' => function ($query) {
            $query->active()->latest();
        }])->findOrFail($id);

        $stats = [
            'total_auctions' => $seller->auctions()->count(),
            'active_auctions' => $seller->auctions()->active()->count(),
            'completed_auctions' => $seller->auctions()->where('status', 'closed')->count(),
            'member_since' => $seller->created_at->format('M Y'),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'seller' => new UserResource($seller),
                'stats' => $stats,
                'auctions' => AuctionResource::collection($seller->auctions)
            ]
        ]);
    }
}
