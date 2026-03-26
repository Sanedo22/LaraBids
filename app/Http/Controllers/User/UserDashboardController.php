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

        // Redirect to KYC if not approved
        if (!$user->isKycApproved()) {
            return redirect()->route('user.kyc.form');
        }

        // Recent Active Bids (Unique auctions user bid on)
        $recent_bids = \App\Models\Bid::where('user_id', $user->id)
            ->with(['auction' => function($q) {
                $q->with('category')->withCount('bids');
            }])
            ->select('*')
            ->whereIn('id', function($query) use ($user) {
                $query->selectRaw('MAX(id)')
                    ->from('bids')
                    ->where('user_id', $user->id)
                    ->groupBy('auction_id');
            })
            ->latest()
            ->take(4)
            ->get();

        // Recently Won Items
        $recent_wins = \App\Models\Auction::where('winner_id', $user->id)
            ->latest('end_time')
            ->take(3)
            ->get();

        // Recent Notifications
        $recent_notifications = $user->notifications()->latest()->take(5)->get();

        $stats['messages_count'] = \App\Models\Contact::where('email', $user->email)->count();

        return view('website.user.dashboard', compact('stats', 'recent_bids', 'recent_wins', 'recent_notifications'));
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
                    return '<div class="d-flex justify-content-center">
                                <a href="'.$url.'" class="btn btn-outline-primary btn-sm btn-action shadow-sm" title="View"><i class="fas fa-eye"></i></a>
                            </div>';
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
                    if ($auction->is_resubmitted && $auction->status === 'pending') {
                        $status = 'Re-submitted';
                    }
                    
                    // Check if payment is successful
                    $payment = \App\Models\Payment::where('auction_id', $auction->id)->where('status', 'success')->first();
                    if ($payment) {
                        $status = 'Paid';
                    }

                    $bg = match($status) {
                        'Live' => 'success', 'Starting Soon' => 'info', 'Expired' => 'danger',
                        'Pending' => 'warning text-dark', 'Re-submitted' => 'primary', 'Closed' => 'secondary', 'Cancelled' => 'dark', 'Paid' => 'success', default => 'secondary'
                    };
                    return '<span class="badge rounded-pill bg-'.$bg.'">'.$status.'</span>';
                })
                ->addColumn('price', function($auction) {
                    $price = $auction->current_price;
                    $html = '<div class="d-flex flex-column">';
                    $html .= '<span class="fw-bold text-dark">₹'.number_format($price, 2).'</span>';
                    
                    // Only show fee deduction if auction is finished and there's a winner
                    if (($auction->status === 'closed' || ($auction->end_time && $auction->end_time->isPast())) && $auction->highestBid()) {
                        $payment = \App\Models\Payment::where('auction_id', $auction->id)->where('status', 'success')->first();
                        $fee = $payment ? $payment->commission_amount : ($price * 0.05);
                        $html .= '<span class="text-muted mt-1" style="font-size: 0.75rem;">Platform Fee: -₹'.number_format($fee, 2).'</span>';
                    }
                    
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('winner', function($auction) {
                    $winner = $auction->winner; // Use the relationship
                    
                    if ($winner) {
                        $name = e($winner->name);
                        $phone = $winner->phone;
                        $email = $winner->email;
                        
                        $html = '<div class="d-flex flex-column">';
                        $html .= '<span class="fw-bold text-dark mb-1">' . $name;
                        if ($winner->unpaid_strikes_count > 0) {
                            $html .= ' <span class="badge bg-danger ms-1" title="Unpaid Item Strikes" style="font-size: 0.6rem;"><i class="fas fa-exclamation-triangle"></i> ' . $winner->unpaid_strikes_count . ' Strikes</span>';
                        }
                        $html .= '</span>';
                        $html .= '<div class="d-flex gap-2">';
                        
                        // WhatsApp Link (Pre-filled message)
                        if ($phone) {
                            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
                            if (strlen($cleanPhone) == 10) $cleanPhone = '91' . $cleanPhone;
                            
                            $waMsg = urlencode("Hello " . $winner->name . ", I'm the seller of the auction \"" . $auction->title . "\" on LaraBids. Congratulations on winning!");
                            $html .= '<a href="https://wa.me/' . $cleanPhone . '?text=' . $waMsg . '" target="_blank" class="text-success" title="WhatsApp Winner"><i class="fab fa-whatsapp"></i></a>';
                            $html .= '<a href="tel:' . $phone . '" class="text-primary" title="Call Winner"><i class="fas fa-phone-alt" style="font-size: 0.75rem;"></i></a>';
                        }
                        
                        // Email Link
                        $html .= '<a href="mailto:' . $email . '" class="text-info" title="Email Winner"><i class="far fa-envelope" style="font-size: 0.75rem;"></i></a>';
                        
                        $html .= '</div></div>';
                        return $html;
                    }
                    
                    if ($auction->status === 'active' && $auction->end_time->isFuture()) {
                        $highestBid = $auction->highestBid();
                        if ($highestBid) {
                            return '<span class="text-primary small fw-bold">Current: ' . e($highestBid->user->name) . '</span>';
                        }
                        return '<span class="text-muted italic small">No Bids Yet</span>';
                    }

                    return '<span class="text-muted italic small">No Winner</span>';
                })
                ->addColumn('bids', function($auction) {
                    return '<span class="badge bg-light text-dark border">'.$auction->bids->count().'</span>';
                })
                ->addColumn('action', function($auction) {
                    $isWithin24Hours = $auction->created_at && $auction->created_at->diffInHours(now()) <= 24;
                    $canEdit = $auction->end_time->isFuture() && 
                        $auction->bids->count() === 0 && (
                        $auction->status === 'active' || 
                        ($auction->status === 'pending' && $isWithin24Hours)
                    );
                    
                    $viewUrl = route('auctions.show', $auction->id);
                    $editUrl = route('auctions.edit', $auction->id);
                    
                    $html = '<div class="d-flex justify-content-center gap-1">';
                    $html .= '<a href="'.$viewUrl.'" class="btn btn-outline-info btn-sm btn-action shadow-sm" title="View"><i class="fas fa-eye"></i></a>';
                    if($canEdit) {
                        $html .= '<a href="'.$editUrl.'" class="btn btn-outline-primary btn-sm btn-action shadow-sm" title="Edit"><i class="fas fa-edit"></i></a>';
                    }
                    
                    // Mark as Unpaid button
                    $strikeExists = \App\Models\UserStrike::where('auction_id', $auction->id)->exists();
                    $payment = \App\Models\Payment::where('auction_id', $auction->id)->where('status', 'success')->first();
                    
                    if ($auction->winner_id && !$payment && !$strikeExists && ($auction->status === 'closed' || ($auction->end_time && $auction->end_time->isPast()))) {
                        $markUnpaidUrl = route('user.auctions.mark-unpaid', $auction->id);
                        $csrf = csrf_field();
                        $html .= '<form action="'.$markUnpaidUrl.'" method="POST" class="d-inline" onsubmit="return confirm(\'Are you sure you want to mark this buyer as Unpaid? This will penalize their account.\')">
                                    '.$csrf.'
                                    <button type="submit" class="btn btn-outline-warning btn-sm btn-action shadow-sm" title="Mark Buyer as Unpaid"><i class="fas fa-user-slash"></i></button>
                                  </form>';
                    }
                    
                    $html .= '<button type="button" onclick="confirmDelete('.$auction->id.')" class="btn btn-outline-danger btn-sm btn-action shadow-sm" title="Delete"><i class="fas fa-trash"></i></button>';
                    $html .= '</div>';
                    
                    return $html;
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
                    return '<div class="d-flex flex-column">
                                <span class="fw-bold text-success">₹'.number_format($auction->current_price, 2).'</span>
                                <span class="text-muted extra-small" style="font-size: 0.6rem;">Total (Incl. Platform Fees)</span>
                            </div>';
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
                    
                    $html = '<div class="d-flex align-items-center gap-2">';
                    $html .= '<a href="'.$viewUrl.'" class="btn btn-outline-primary btn-sm btn-action shadow-sm" title="View"><i class="fas fa-eye"></i></a>';
                    
                    if (!$payment) {
                        $payUrl = route('payment.payu.summary', $auction->id);
                        $html .= '<a href="'.$payUrl.'" class="btn btn-sm px-3 rounded-pill shadow-sm fw-bold text-white mb-0 d-inline-flex align-items-center" 
                                    style="background: linear-gradient(135deg, #a88b77 0%, #7d6355 100%); border: none; font-size: 0.65rem; padding: 8px 16px; transition: transform 0.2s; text-transform: uppercase; letter-spacing: 0.05em;" 
                                    onmouseover="this.style.transform=\'scale(1.05)\'" 
                                    onmouseout="this.style.transform=\'scale(1)\'"
                                    title="Pay Now">
                                    <i class="fas fa-credit-card me-1" style="font-size: 0.7rem;"></i> Pay Now
                                  </a>';
                    }
                    $html .= '</div>';
                    
                    return $html;
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
                    
                    return '<div class="d-flex justify-content-center gap-1">
                                <a href="'.$viewUrl.'" class="btn btn-outline-info btn-sm btn-action shadow-sm" title="View"><i class="fas fa-eye"></i></a>
                                <form action="'.$toggleUrl.'" method="POST" class="d-inline">
                                    '.$csrf.'
                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-action shadow-sm" title="Remove">
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

    // Mark as Unpaid
    public function markAsUnpaid(\App\Models\Auction $auction)
    {
        // 1. Authorization
        if ($auction->user_id !== auth()->id()) {
            abort(403);
        }

        // 2. Check if there's a winner
        if (!$auction->winner_id) {
            return redirect()->back()->with('error', 'There is no winner for this auction.');
        }

        // 3. Check if payment is already made
        $payment = \App\Models\Payment::where('auction_id', $auction->id)->where('status', 'success')->first();
        if ($payment) {
            return redirect()->back()->with('error', 'The winner has already paid.');
        }

        // 4. Check if already marked
        if (\App\Models\UserStrike::where('auction_id', $auction->id)->exists()) {
            return redirect()->back()->with('error', 'You have already marked this auction as unpaid.');
        }

        // 5. Add strike
        \App\Models\UserStrike::create([
            'user_id' => $auction->winner_id,
            'auction_id' => $auction->id,
            'reported_by' => auth()->id(),
            'reason' => 'Failed to pay for auction: ' . $auction->title,
        ]);

        // 6. Reset the auction so it can be relisted or just leave it as closed/cancelled
        \App\Models\Payment::where('auction_id', $auction->id)->where('status', 'pending')->update(['status' => 'cancelled']);
        
        $auction->update(['status' => 'cancelled', 'cancellation_reason' => 'Winner failed to pay.']);

        return redirect()->back()->with('success', 'The buyer has been penalized with an Unpaid Item Strike. The auction has been cancelled.');
    }
}
