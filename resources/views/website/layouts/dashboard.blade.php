<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard | LaraBids')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">

    @stack('styles')
</head>

<body>

    <div class="d-flex">
        <!-- Sidebar -->
        <aside class="sidebar-elite p-3 shadow" style="width: 250px;">
            <div class="text-center py-3 mb-2">
                <a href="{{ route('home') }}" class="text-decoration-none">
                    <span class="text-white display-font fw-bold fs-4 text-uppercase letter-spacing-2">LaraBids</span>
                </a>
            </div>

            <hr class="sidebar-divider">


            <nav>
                <div class="sidebar-heading">Core Dashboard</div>
                <a href="{{ route('user.dashboard') }}" 
                   class="sidebar-link rounded mb-1 {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-tachometer-alt me-2"></i> Overview
                </a>
                
                <hr class="sidebar-divider">
                <div class="sidebar-heading">My Activity</div>
                
                <a href="{{ route('user.my-bids') }}" 
                   class="sidebar-link rounded mb-1 {{ request()->routeIs('user.my-bids') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-gavel me-2"></i> Active Bids
                </a>
                <a href="{{ route('user.my-auctions') }}" 
                   class="sidebar-link rounded mb-1 {{ request()->routeIs('user.my-auctions') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-boxes me-2"></i> My Auctions
                </a>
                <a href="{{ route('user.winning-items') }}" 
                   class="sidebar-link rounded mb-1 {{ request()->routeIs('user.winning-items') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-trophy me-2"></i> Won Items
                </a>
                <a href="{{ route('user.watchlist') }}" 
                   class="sidebar-link rounded mb-1 {{ request()->routeIs('user.watchlist') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-heart me-2"></i> Watchlist
                </a>
                
                <hr class="sidebar-divider">
                <div class="sidebar-heading">Settings</div>
                
                <a href="{{ route('user.profile') }}" 
                   class="sidebar-link rounded mb-1 {{ request()->routeIs('user.profile') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-cog me-2"></i> Profile
                </a>
                
                <div class="mt-5">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sidebar-link rounded text-white opacity-50 w-100 border-0 bg-transparent text-start">
                            <i class="fas fa-fw fa-sign-out-alt me-2"></i> Logout
                        </button>
                    </form>
                </div>
            </nav>

        </aside>

        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay"></div>

        <!-- Sidebar Toggle Button -->
        <button class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Main Content -->
        <main class="flex-grow-1"
            style="background: #f8f9fc; min-height: 100vh;">


            <div class="p-4 p-lg-5">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

    @stack('scripts')
</body>

</html>
