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
    <style>
        /* Action Buttons Global Styling */
        .btn-action {
            width: 34px;
            height: 34px;
            padding: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 0.5rem !important;
            transition: all 0.2s ease-in-out !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08) !important;
            font-size: 0.85rem !important;
        }
        .btn-action:hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.12) !important;
        }
        .gap-1 {
            gap: 0.25rem !important;
        }
        .gap-2 {
            gap: 0.5rem !important;
        }

        /* Filter & DataTable Consistent Styling */
        .filter-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.6rem;
            color: #64748b;
            font-weight: 700;
            display: flex;
            align-items: center;
        }
        .filter-control {
            border-radius: 0.6rem;
            border: 1px solid #e2e8f0;
            font-size: 0.85rem;
            padding: 0.65rem 0.85rem;
            background-color: #f8fafc;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            color: #1e293b;
            box-shadow: 0 1px 2px rgba(0,0,0,0.02);
            height: 42px;
        }
        .filter-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: #fff;
            outline: none;
        }
        
        .table th {
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            color: #64748b;
            font-weight: 700;
            border-bottom: 2px solid #f1f5f9 !important;
            background-color: #f8fafc;
            padding: 1.1rem 0.9rem;
            vertical-align: middle;
        }
        .table td {
            vertical-align: middle;
            color: #334155;
            font-size: 0.88rem;
            padding: 1.1rem 0.9rem;
            border-bottom: 1px solid #f8fafc;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.02);
        }

        /* DataTable Specific Labels & Inputs */
        .dataTables_wrapper label {
            color: #64748b !important;
            font-weight: 700;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            display: inline-flex;
            align-items: center;
        }
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 0.6rem;
            border: 1px solid #e2e8f0;
            padding: 0.45rem 0.9rem;
            margin-left: 0.6rem;
            background-color: #f8fafc;
            font-size: 0.85rem;
            transition: all 0.2s;
            height: 38px;
            width: 220px;
        }
        .dataTables_wrapper .dataTables_filter input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            background-color: #fff;
        }
        .dataTables_wrapper .dataTables_length select {
            border-radius: 0.6rem;
            border: 1px solid #e2e8f0;
            padding: 0.35rem 1.8rem 0.35rem 0.8rem;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 0.85rem;
            margin: 0 0.5rem;
            cursor: pointer;
            outline: none;
            transition: all 0.2s;
            height: 38px;
        }
        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #3b82f6;
            background-color: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
    </style>
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

            <div class="px-3 mb-2">
                <a href="{{ route('home') }}" class="btn btn-outline-light btn-sm w-100 rounded-pill opacity-75 hover-opacity-100 transition-all">
                    <i class="fas fa-arrow-left me-2"></i> Back to Website
                </a>
            </div>

            <nav>
                <div class="sidebar-heading">Core Dashboard</div>
                <a href="{{ route('user.dashboard') }}" 
                   class="sidebar-link rounded mb-1 {{ request()->routeIs('user.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-tachometer-alt me-2"></i> Overview
                </a>
                
                <div class="sidebar-heading mt-2">My Activity</div>
                
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
                <a href="{{ route('user.notifications.index') }}" 
                   class="sidebar-link rounded mb-1 {{ request()->routeIs('user.notifications.index') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-bell me-2"></i> Notifications
                </a>
                
                <div class="sidebar-heading mt-2">Settings</div>
                
                <a href="{{ route('profile.edit') }}" 
                   class="sidebar-link rounded mb-1 {{ request()->routeIs('profile.edit') ? 'active' : '' }}">
                    <i class="fas fa-fw fa-cog me-2"></i> Profile
                </a>

                
                <div class="mt-1">
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
            style="background: #f8f9fc; min-height: 100vh; overflow-x: hidden;">

            <!-- Removed Top Navbar -->

            <div class="p-4 p-lg-5 pt-lg-2">
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



