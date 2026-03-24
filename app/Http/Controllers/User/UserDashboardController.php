<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Contact;
use Yajra\DataTables\Facades\DataTables;

class UserDashboardController extends Controller
{
    // Dashboard
    public function index()
    {
        $user = auth()->user();

        $stats = [
            'active_bids'      => $user->bids()->count(),
            'total_wins'       => $user->getWonAuctionsCount(),
            'watchlist_count'  => $user->watchlist()->count(),
            'messages_count'   => 0,
        ];

        return view('website.user.dashboard', compact('stats'));
    }

    // My bids
    public function myBids()
    {
        $categories = \App\Models\Category::active()->whereNull('parent_id')->with('children')->get();
        return view('website.user.my-bids', compact('categories'));
    }

    // My bids data for DataTables
    public function myBidsData(\Illuminate\Http\Request $request)
    {
        try {
            $user = auth()->user();
            
            // Get user's bids, unique by auction_id (showing their latest amount per auction)
            $query = \App\Models\Bid::where('user_id', $user->id)
                ->with(['auction.category'])
                ->select('*')
                ->whereIn('id', function($query) use ($user) {
                    $query->selectRaw('MAX(id)')
                        ->from('bids')
                        ->where('user_id', $user->id)
                        ->groupBy('auction_id');
                });

            // Filters
            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    $categoryIds = $category->getAllChildIds();
                    $query->whereHas('auction.category', function($q) use ($categoryIds) {
                        $q->whereIn('id', $categoryIds);
                    });
                }
            }

            if ($request->filled('status')) {
                $status = $request->status;
                $query->whereHas('auction', function($q) use ($status) {
                    if ($status === 'live') {
                        $q->where('status', 'active')
                          ->where('start_time', '<=', now())
                          ->where('end_time', '>', now());
                    } elseif ($status === 'ended') {
                        $q->where('end_time', '<=', now());
                    } elseif ($status !== 'all') {
                        $q->where('status', $status);
                    }
                });
            }

            if ($request->filled('start_date')) {
                $query->whereDate('bids.created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('bids.created_at', '<=', $request->end_date);
            }

            // Search
            if ($request->has('search') && isset($request->search['value'])) {
                $keyword = $request->search['value'];
                if (!empty($keyword)) {
                    $query->whereHas('auction', function($q) use ($keyword) {
                        $q->where('title', 'like', "%{$keyword}%");
                    });
                }
            }

            // Sort
            $sort = $request->input('sort', 'latest');
            match($sort) {
                'price_asc' => $query->orderBy('amount', 'asc'),
                'price_desc' => $query->orderBy('amount', 'desc'),
                default => $query->latest(),
            };

            return datatables()->of($query)
                ->addColumn('item', function($bid) {
                    $auction = $bid->auction;
                    $image = $auction->image ? (str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image)) : 'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120';
                    $title = e($auction->title);
                    if(strlen($title) > 45) {
                        $title = substr($title, 0, 45) . '...';
                    }
                    
                    return '
                        <div class="d-flex align-items-center text-nowrap">
                            <div class="position-relative me-3">
                                <img src="'.$image.'" class="rounded border" width="50" height="50" style="object-fit: cover;" onerror="this.src=\'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120\'">
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark mb-1 d-inline-block" style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($auction->title).'">'.$title.'</span>
                                <span class="text-muted small">ID: #'.str_pad($auction->id, 5, '0', STR_PAD_LEFT).'</span>
                            </div>
                        </div>';
                })
                ->addColumn('my_bid', function($bid) {
                    return '<span class="fw-bold text-primary">₹'.number_format($bid->amount, 2).'</span>';
                })
                ->addColumn('current_price', function($bid) {
                    return '₹'.number_format($bid->auction->current_price, 2);
                })
                ->addColumn('status', function($bid) {
                    $status = $bid->auction->status_label;
                    $bg = match($status) {
                        'Live' => 'success', 'Starting Soon' => 'info', 'Ended' => 'danger',
                        'Pending' => 'warning text-dark', 'Closed' => 'secondary', 'Cancelled' => 'dark', default => 'secondary'
                    };
                    return '<span class="badge rounded-pill bg-'.$bg.'">'.$status.'</span>';
                })
                ->addColumn('time_left', function($bid) {
                    $auction = $bid->auction;
                    if ($auction->status === 'active' && $auction->end_time->isFuture()) {
                        return '<i class="far fa-clock me-1 text-primary"></i> ' . $auction->end_time->diffForHumans(null, true);
                    }
                    return '<span class="text-muted">Ended</span>';
                })
                ->addColumn('action', function($bid) {
                    $url = route('auctions.show', $bid->auction->id);
                    return '<a href="'.$url.'" class="btn btn-outline-primary btn-sm rounded-circle shadow-sm" title="View" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-eye"></i></a>';
                })
                ->rawColumns(['item', 'my_bid', 'current_price', 'status', 'time_left', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // My auctions
    public function myAuctions()
    {
        $categories = \App\Models\Category::active()->whereNull('parent_id')->with('children')->get();
        return view('website.user.my-auctions', compact('categories'));
    }

    // My auctions data for DataTables
    public function myAuctionsData(\Illuminate\Http\Request $request)
    {
        try {
            $query = \App\Models\Auction::where('user_id', auth()->id())
                ->with(['category', 'bids.user']);

            // Filters
            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    $query->whereIn('category_id', $category->getAllChildIds());
                }
            }

            if ($request->filled('status')) {
                $status = $request->status;
                if ($status === 'live') {
                    $query->where('status', 'active')
                          ->where('start_time', '<=', now())
                          ->where('end_time', '>', now());
                } elseif ($status === 'ended') {
                    $query->where('end_time', '<=', now());
                } elseif ($status !== 'all') {
                    $query->where('status', $status);
                }
            }

            if ($request->filled('start_date')) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            // Search
            if ($request->has('search') && isset($request->search['value'])) {
                $keyword = $request->search['value'];
                if (!empty($keyword)) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('title', 'like', "%{$keyword}%")
                          ->orWhere('description', 'like', "%{$keyword}%");
                    });
                }
            }

            // Sort
            $sort = $request->input('sort', 'latest');
            match($sort) {
                'price_asc' => $query->orderBy('current_price', 'asc'),
                'price_desc' => $query->orderBy('current_price', 'desc'),
                'ending_soon' => $query->orderBy('end_time', 'asc'),
                default => $query->latest(),
            };

            return datatables()->of($query)
                ->addColumn('item', function($auction) {
                    $image = $auction->image ? (str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image)) : 'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120';
                    $title = e($auction->title);
                    if(strlen($title) > 45) {
                        $title = substr($title, 0, 45) . '...';
                    }
                    $date = $auction->created_at->format('M d, Y');
                    return '
                        <div class="d-flex align-items-center text-nowrap">
                            <div class="position-relative me-3">
                                <img src="'.$image.'" class="rounded border" width="50" height="50" style="object-fit: cover;" onerror="this.src=\'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120\'">
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark mb-1 d-inline-block" style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($auction->title).'">'.$title.'</span>
                                <span class="text-muted small"><i class="far fa-calendar-alt me-1"></i> Listed on '.$date.'</span>
                            </div>
                        </div>';
                })
                ->editColumn('status', function($auction) {
                    $status = $auction->status_label;
                    $bg = match($status) {
                        'Live' => 'success', 'Starting Soon' => 'info', 'Ended' => 'danger',
                        'Pending' => 'warning text-dark', 'Closed' => 'secondary', 'Cancelled' => 'dark', default => 'secondary'
                    };
                    return '<span class="badge rounded-pill bg-'.$bg.'">'.$status.'</span>';
                })
                ->addColumn('price', function($auction) {
                    return '<span class="fw-bold">₹'.number_format($auction->current_price, 2).'</span>';
                })
                ->addColumn('winner', function($auction) {
                    $highestBid = $auction->highestBid();
                    if ($highestBid && $highestBid->user) {
                        return e($highestBid->user->name);
                    }
                    return '<span class="text-muted italic small">No Bids</span>';
                })
                ->addColumn('bids', function($auction) {
                    return '<span class="badge bg-light text-dark border">'.$auction->bids->count().'</span>';
                })
                ->addColumn('action', function($auction) {
                    $isWithin24Hours = $auction->created_at && $auction->created_at->diffInHours(now()) <= 24;
                    $canEdit = $auction->end_time->isFuture() && (
                        $auction->status === 'active' || 
                        ($auction->status === 'pending' && $isWithin24Hours)
                    );
                    
                    $viewUrl = route('auctions.show', $auction->id);
                    $editUrl = route('auctions.edit', $auction->id);
                    
                    $html = '<a href="'.$viewUrl.'" class="btn btn-outline-info btn-sm rounded-circle shadow-sm me-1" title="View" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-eye"></i></a>';
                    if($canEdit) {
                        $html .= '<a href="'.$editUrl.'" class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-1" title="Edit" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-edit"></i></a>';
                    }
                    $html .= '<button type="button" onclick="confirmDelete('.$auction->id.')" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm" title="Delete" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-trash"></i></button>';
                    
                    return '<div class="text-nowrap">'.$html.'</div>';
                })
                ->rawColumns(['item', 'status', 'price', 'winner', 'bids', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Winning items
    public function winningItems()
    {
        $categories = \App\Models\Category::active()->whereNull('parent_id')->with('children')->get();
        return view('website.user.winning-items', compact('categories'));
    }

    // Winning items data for DataTables
    public function winningItemsData(\Illuminate\Http\Request $request)
    {
        try {
            $user = auth()->user();
            
            $query = \App\Models\Auction::where('winner_id', $user->id)
                ->with(['category', 'user']);

            // Filters
            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    $query->whereIn('category_id', $category->getAllChildIds());
                }
            }

            if ($request->filled('start_date')) {
                $query->whereDate('end_time', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $query->whereDate('end_time', '<=', $request->end_date);
            }

            // Search
            if ($request->has('search') && isset($request->search['value'])) {
                $keyword = $request->search['value'];
                if (!empty($keyword)) {
                    $query->where('title', 'like', "%{$keyword}%");
                }
            }

            // Sort
            $sort = $request->input('sort', 'latest');
            match($sort) {
                'price_asc' => $query->orderBy('current_price', 'asc'),
                'price_desc' => $query->orderBy('current_price', 'desc'),
                default => $query->latest('end_time'),
            };

            return datatables()->of($query)
                ->addColumn('item', function($auction) {
                    $image = $auction->image ? (str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image)) : 'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120';
                    $title = e($auction->title);
                    if(strlen($title) > 45) {
                        $title = substr($title, 0, 45) . '...';
                    }
                    return '
                        <div class="d-flex align-items-center text-nowrap">
                            <div class="position-relative me-3">
                                <img src="'.$image.'" class="rounded border" width="50" height="50" style="object-fit: cover;" onerror="this.src=\'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120\'">
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark mb-1 d-inline-block" style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($auction->title).'">'.$title.'</span>
                                <span class="text-muted small">ID: #'.str_pad($auction->id, 5, '0', STR_PAD_LEFT).'</span>
                            </div>
                        </div>';
                })
                ->addColumn('winning_bid', function($auction) {
                    return '<span class="fw-bold text-success">₹'.number_format($auction->current_price, 2).'</span>';
                })
                ->addColumn('won_date', function($auction) {
                    return '<span class="text-muted small">'.$auction->end_time->format('M d, Y').'</span>';
                })
                ->addColumn('payment_status', function($auction) {
                    $payment = \App\Models\Payment::where('auction_id', $auction->id)->where('status', 'success')->first();
                    if ($payment) {
                        return '<span class="badge rounded-pill px-3" style="background-color: #e2ede5; color: #3e6d4d; border: 1px solid #c3d9c9; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Paid</span>';
                    }
                    return '<span class="badge rounded-pill px-3" style="background-color: #fff4e5; color: #a35200; border: 1px solid #ffcc80; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Unpaid</span>';
                })
                ->addColumn('action', function($auction) {
                    $viewUrl = route('auctions.show', $auction->id);
                    $payment = \App\Models\Payment::where('auction_id', $auction->id)->where('status', 'success')->first();
                    
                    $html = '<a href="'.$viewUrl.'" class="btn btn-outline-primary btn-sm rounded-circle shadow-sm me-2" title="View" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-eye"></i></a>';
                    
                    if (!$payment) {
                        $payUrl = route('payment.payu.summary', $auction->id);
                        $html .= '<a href="'.$payUrl.'" class="btn btn-sm px-2 rounded-pill shadow-sm fw-bold text-white mb-0" 
                                    style="background: linear-gradient(135deg, #a88b77 0%, #7d6355 100%); border: none; font-size: 0.65rem; padding: 4px 12px; transition: transform 0.2s; text-transform: uppercase; letter-spacing: 0.02em;" 
                                    onmouseover="this.style.transform=\'scale(1.05)\'" 
                                    onmouseout="this.style.transform=\'scale(1)\'"
                                    title="Pay Now">
                                    <i class="fas fa-credit-card me-1" style="font-size: 0.6rem;"></i> Pay Now
                                  </a>';
                    }
                    
                    return '<div class="d-flex align-items-center">'.$html.'</div>';
                })
                ->rawColumns(['item', 'winning_bid', 'won_date', 'payment_status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Watchlist
    public function watchlist()
    {
        $categories = \App\Models\Category::active()->whereNull('parent_id')->with('children')->get();
        return view('website.user.watchlist', compact('categories'));
    }

    // Watchlist data for DataTables
    public function watchlistData(\Illuminate\Http\Request $request)
    {
        try {
            $query = auth()->user()
                ->watchlist()
                ->with(['auction.category', 'auction.user']);

            // Filters
            if ($request->filled('category')) {
                $category = \App\Models\Category::where('slug', $request->category)->first();
                if ($category) {
                    $categoryIds = $category->getAllChildIds();
                    $query->whereHas('auction.category', function($q) use ($categoryIds) {
                        $q->whereIn('id', $categoryIds);
                    });
                }
            }

            // Search
            if ($request->has('search') && isset($request->search['value'])) {
                $keyword = $request->search['value'];
                if (!empty($keyword)) {
                    $query->whereHas('auction', function($q) use ($keyword) {
                        $q->where('title', 'like', "%{$keyword}%");
                    });
                }
            }

            // Sort
            $sort = $request->input('sort', 'latest');
            if ($sort === 'price_asc' || $sort === 'price_desc') {
                $query->join('auctions', 'watchlists.auction_id', '=', 'auctions.id')
                      ->select('watchlists.*')
                      ->orderBy('auctions.current_price', $sort === 'price_asc' ? 'asc' : 'desc');
            } else {
                $query->latest('watchlists.created_at');
            }

            return datatables()->of($query)
                ->addColumn('item', function($item) {
                    $auction = $item->auction;
                    $image = $auction->image ? (str_starts_with($auction->image, 'http') ? $auction->image : asset('storage/' . $auction->image)) : 'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120';
                    $title = e($auction->title);
                    if(strlen($title) > 45) {
                        $title = substr($title, 0, 45) . '...';
                    }
                    $seller = e($auction->user->name ?? 'Unknown');
                    return '
                        <div class="d-flex align-items-center text-nowrap">
                            <div class="position-relative me-3">
                                <img src="'.$image.'" class="rounded border" width="50" height="50" style="object-fit: cover;" onerror="this.src=\'https://images.unsplash.com/photo-1523275335684-21481017106d?auto=format&fit=crop&w=120\'">
                            </div>
                            <div class="d-flex flex-column">
                                <span class="fw-bold text-dark mb-1 d-inline-block" style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="'.e($auction->title).'">'.$title.'</span>
                                <span class="text-muted small"><i class="fas fa-user me-1"></i> '.$seller.'</span>
                            </div>
                        </div>';
                })
                ->addColumn('category', function($item) {
                    return '<span class="badge bg-light text-dark border">'.($item->auction->category->name ?? 'N/A').'</span>';
                })
                ->addColumn('price', function($item) {
                    return '<span class="fw-bold">₹'.number_format($item->auction->current_price, 2).'</span>';
                })
                ->addColumn('end_time', function($item) {
                    return '<span class="text-muted small">'.$item->auction->end_time->format('M d, Y H:i').'</span>';
                })
                ->addColumn('action', function($item) {
                    $viewUrl = route('auctions.show', $item->auction_id);
                    $toggleUrl = route('user.watchlist.toggle', $item->auction_id);
                    $csrf = csrf_field();
                    
                    return '<div class="text-nowrap">
                                <a href="'.$viewUrl.'" class="btn btn-outline-info btn-sm rounded-circle shadow-sm me-1" title="View" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;"><i class="fas fa-eye"></i></a>
                                <form action="'.$toggleUrl.'" method="POST" class="d-inline">
                                    '.$csrf.'
                                    <button type="submit" class="btn btn-outline-danger btn-sm rounded-circle shadow-sm" title="Remove" style="width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>';
                })
                ->rawColumns(['item', 'category', 'price', 'end_time', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Show single message (contact)
    public function showMessage($id)
    {
        $contact = Contact::withTrashed()
            ->where('email', auth()->user()->email)
            ->findOrFail($id);

        return view('website.user.message_show', compact('contact'));
    }

    // Profile
    public function profile()
    {
        return view('website.user.profile');
    }
}
