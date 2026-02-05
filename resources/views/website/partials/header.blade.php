
<nav class="navbar navbar-expand-lg navbar-elite sticky-top py-3">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <span class="gold-text display-font fw-bold fs-3">LaraBids</span>
        </a>
        <button class="navbar-toggler border-0 text-dark" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarNav">
            <i class="fas fa-bars"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('home') }}" data-nav-active="{{ request()->is('/') ? 'true' : 'false' }}">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('auctions.index') }}" data-nav-active="{{ request()->is('auctions*') ? 'true' : 'false' }}">Auctions</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('about') }}" data-nav-active="{{ request()->is('about*') ? 'true' : 'false' }}">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('contact') }}" data-nav-active="{{ request()->is('contact*') ? 'true' : 'false' }}">Contact</a>
                </li>
            </ul>
            <div class="d-flex gap-3 align-items-center">
                <!-- Search Bar -->
                <form action="{{ route('auctions.index') }}" method="GET" class="nav-search d-none d-lg-block me-3 mb-0">
                    <button type="submit" class="search-submit-btn">
                        <i class="fas fa-search"></i>
                    </button>
                    <input type="search" name="q" class="form-control" placeholder="Search auctions..." value="{{ request('q') }}">
                </form>

                @guest
                    <!-- Guest State -->
                    <div class="d-none d-sm-flex gap-2">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary px-4 btn-sm">Sign In</a>
                        <a href="{{ route('register') }}" class="btn btn-primary px-4 btn-sm fw-bold">Join Now</a>
                    </div>

                 @else
                    <!-- Member State -->
                    <a href="{{ route('auctions.create') }}" class="btn btn-primary d-none d-lg-inline-block rounded-pill px-4 fw-bold me-2 shadow-sm">
                        <i class="fas fa-plus-circle me-1"></i> Sell Item
                    </a>
                     <!-- Notifications -->
                    <div class="dropdown ms-3">
                        <a class="text-dark position-relative" href="#" id="alertsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell fa-lg"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.5rem; padding: 0.2rem 0.3rem;">
                                    {{ auth()->user()->unreadNotifications->count() > 9 ? '9+' : auth()->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="alertsDropdown" style="width: 350px; max-height: 400px; overflow-y: auto;">
                            <li><h6 class="dropdown-header text-uppercase text-secondary fw-bold" style="font-size: 0.75rem;">Alerts Center</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            
                            @forelse(auth()->user()->notifications()->take(5)->get() as $notification)
                                <li>
                                    <a class="dropdown-item d-flex align-items-start p-3 {{ $notification->read_at ? 'bg-light bg-opacity-25' : 'bg-primary bg-opacity-10' }}" 
                                       href="{{ route('user.notifications.index') }}"
                                       onclick="event.preventDefault(); document.getElementById('mark-read-{{ $notification->id }}').submit();">
                                        <div class="me-3">
                                            <div class="bg-{{ isset($notification->data['type']) && $notification->data['type'] === 'auction_cancelled' ? 'danger' : 'primary' }} text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="fas fa-{{ isset($notification->data['type']) && $notification->data['type'] === 'auction_cancelled' ? 'gavel' : 'bell' }}"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="small text-muted">{{ $notification->created_at->diffForHumans() }}</div>
                                            <span class="fw-bold d-block">{{ $notification->data['title'] ?? 'Notification' }}</span>
                                            <span class="small text-truncate d-block" style="max-width: 240px;">{{ $notification->data['message'] ?? '' }}</span>
                                        </div>
                                    </a>
                                    <form id="mark-read-{{ $notification->id }}" action="{{ route('user.notifications.read', $notification->id) }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                                </li>
                            @empty
                                <li>
                                    <div class="dropdown-item text-center text-muted py-4">
                                        <i class="fas fa-bell-slash fa-2x mb-2"></i>
                                        <p class="mb-0">No notifications yet</p>
                                    </div>
                                </li>
                            @endforelse
                            
                            @if(auth()->user()->notifications->count() > 0)
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center small text-secondary py-2" href="{{ route('user.notifications.index') }}">Show All Alerts</a></li>
                            @endif
                        </ul>
                    </div>
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle text-dark"
                            data-bs-toggle="dropdown">
                            <img src="{{ auth()->user()->avatar_url }}"
                                class="rounded-circle border border-primary border-opacity-25 me-2" height="35" width="35" style="object-fit: cover;" alt="Avatar">
                            <span class="small d-none d-md-inline">{{ auth()->user()->name }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">

                            <li>
                                <a class="dropdown-item text-dark small" href="{{ route('user.dashboard') }}">
                                    <i class="fas fa-th-large me-2 text-primary"></i> Dashboard
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-dark small" href="{{ route('user.my-auctions') }}">
                                    <i class="fas fa-gavel me-2 text-primary"></i> My Auctions
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-dark small" href="{{ route('user.profile') }}">
                                    <i class="fas fa-user-edit me-2 text-primary"></i> Profile
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item text-dark small" href="{{ route('user.watchlist') }}">
                                    <i class="fas fa-heart me-2 text-primary"></i> Watchlist
                                </a>
                            </li>

                            <li>
                                <hr class="dropdown-divider border-white border-opacity-10">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger small">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>


                @endguest
            </div>
        </div>
    </div>
</nav>
