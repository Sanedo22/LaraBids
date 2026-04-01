@extends('website.layouts.app', ['hideFooter' => true])

@section('title', 'Browse Auctions | LaraBids')

@section('content')

<!-- Main Content -->
<section class="pt-2 pb-5">
    <div class="container-fluid px-lg-5">
        <div class="row g-4">
            
            <!-- Sidebar Filters -->
            <div class="col-md-4 col-lg-3">
                <div class="sidebar-sticky-area">
                    <div class="card border-0 shadow-sm rounded-4 mb-3 p-4 filter-sidebar">
                        <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                            <h5 class="fw-bold mb-0"><i class="fas fa-filter text-primary me-2"></i>Filters</h5>
                            <a href="{{ route('auctions.index') }}" class="text-primary small fw-semibold text-decoration-none hover-underline ajax-filter-link" id="clear-all-filters">Clear All</a>
                        </div>

                        <form action="{{ route('auctions.index') }}" method="GET" id="filterForm">
                            
                            <!-- Auction Status (Compact) -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-uppercase text-secondary letter-spacing-1 mb-2">Status</label>
                                <div class="status-filters d-flex flex-column gap-1">
                                    @php $status = request('status', 'live'); @endphp
                                    
                                    <label class="d-flex align-items-center justify-content-between p-2 rounded-3 cursor-pointer transition-all {{ $status == 'live' ? 'bg-primary-subtle text-primary fw-bold' : 'hover-bg-light text-secondary' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-bolt me-2 {{ $status == 'live' ? 'text-primary' : 'text-muted' }}" style="width: 20px;"></i>
                                            <span class="small">Live Auctions</span>
                                        </div>
                                        <input type="radio" name="status" value="live" class="d-none status-filter-input" {{ $status == 'live' ? 'checked' : '' }}>
                                        @if($status == 'live') <i class="fas fa-check-circle small"></i> @endif
                                    </label>

                                    <label class="d-flex align-items-center justify-content-between p-2 rounded-3 cursor-pointer transition-all {{ $status == 'upcoming' ? 'bg-primary-subtle text-primary fw-bold' : 'hover-bg-light text-secondary' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-alt me-2 {{ $status == 'upcoming' ? 'text-primary' : 'text-muted' }}" style="width: 20px;"></i>
                                            <span class="small">Upcoming</span>
                                        </div>
                                        <input type="radio" name="status" value="upcoming" class="d-none status-filter-input" {{ $status == 'upcoming' ? 'checked' : '' }}>
                                        @if($status == 'upcoming') <i class="fas fa-check-circle small"></i> @endif
                                    </label>

                                    <label class="d-flex align-items-center justify-content-between p-2 rounded-3 cursor-pointer transition-all {{ $status == 'past' ? 'bg-primary-subtle text-primary fw-bold' : 'hover-bg-light text-secondary' }}">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-history me-2 {{ $status == 'past' ? 'text-primary' : 'text-muted' }}" style="width: 20px;"></i>
                                            <span class="small">Closed</span>
                                        </div>
                                        <input type="radio" name="status" value="past" class="d-none status-filter-input" {{ $status == 'past' ? 'checked' : '' }}>
                                        @if($status == 'past') <i class="fas fa-check-circle small"></i> @endif
                                    </label>
                                </div>
                            </div>

                            <!-- Categories -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-uppercase text-secondary letter-spacing-1 mb-3 d-block">Categories</label>
                                <div class="category-sidebar-nav">
                                    <ul class="list-unstyled mb-0 category-tree-list">
                                        <li class="mb-2">
                                            <a href="{{ route('auctions.index', request()->except('category')) }}" class="category-link ajax-filter-link {{ !request('category') ? 'active' : '' }}">
                                                <i class="fas fa-th-large me-2"></i> All Categories
                                            </a>
                                        </li>
                                        @foreach($categories as $category)
                                            @php 
                                                $hasChildren = $category->children->count() > 0;
                                                $requestCat = request('category');
                                                $isActiveParent = ($requestCat === $category->slug || $category->isAncestorOf($requestCat));
                                                $isCurrentCat = $requestCat === $category->slug;
                                            @endphp
                                            <li class="category-item mb-1 {{ $hasChildren ? 'has-sub' : '' }} {{ $isActiveParent ? 'open' : '' }}">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <a href="{{ route('auctions.index', array_merge(request()->query(), ['category' => $category->slug])) }}" 
                                                       class="category-link ajax-filter-link flex-grow-1 {{ $isCurrentCat ? 'active fw-bold' : '' }}">
                                                        <i class="{{ $category->icon ?? 'fas fa-chevron-right' }} me-2 opacity-75"></i> {{ $category->name }}
                                                    </a>
                                                    @if($hasChildren)
                                                        <button class="btn btn-link btn-sm text-secondary p-1 sub-dropdown-toggle" type="button">
                                                            <i class="fas fa-chevron-down small transition-all"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                                
                                                @if($hasChildren)
                                                    <ul class="list-unstyled ps-4 sub-category-list mt-1 border-start ms-2">
                                                        @foreach($category->children as $sub)
                                                            <li class="mb-1">
                                                                <a href="{{ route('auctions.index', array_merge(request()->query(), ['category' => $sub->slug])) }}" 
                                                                   class="category-link ajax-filter-link py-1 ps-2 {{ request('category') == $sub->slug ? 'active fw-bold' : '' }}" style="font-size: 0.85rem;">
                                                                    <span class="subcategory-dot"></span> {{ $sub->name }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>

                            <!-- Price Range -->
                            <div class="mb-4">
                                <label class="form-label small fw-bold text-uppercase text-secondary letter-spacing-1">Price Range (₹)</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control form-control-sm bg-light border-0 py-2 shadow-none rounded-3" placeholder="Min" value="{{ request('min_price') }}">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control form-control-sm bg-light border-0 py-2 shadow-none rounded-3" placeholder="Max" value="{{ request('max_price') }}">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm mt-2">
                                Apply Filters <i class="fas fa-arrow-right ms-2 small"></i>
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            <!-- Auction Grid -->
            <div class="col-md-8 col-lg-9 position-relative">
                <!-- Loading Overlay -->
                <div id="loading-overlay" class="position-absolute top-0 start-0 w-100 h-100 d-none flex-column align-items-center justify-content-center" 
                     style="z-index: 100; background: rgba(255,255,255,0.7); backdrop-filter: blur(2px); border-radius: 1rem;">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="fw-bold text-primary">Updating Auctions...</span>
                </div>

                <div id="auction-results-container">
                    @include('website.auctions._auction_grid')
                </div>
            </div>
        </div>
    </div>
</section>

@push('styles')
<style>
    /* Premium Architecture Styles */
    :root {
        --primary-color: #4e73df;
        --gold-color: #d4af37;
        --dark-bg: #1a1a1a;
        --glass-bg: rgba(255, 255, 255, 0.85);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
        background-color: #f8f9fc;
    }

    /* Ultimate Sticky Sidebar */
    @media (min-width: 992px) {
        .sidebar-sticky-area {
            position: sticky;
            top: 90px; /* Space for Navbar */
            height: calc(100vh - 110px); /* Fill screen height */
            overflow-y: auto; /* Scroll inside this area */
            z-index: 20;
            padding-bottom: 20px;
            /* Prevent parent scroll chaining */
            overscroll-behavior-y: contain; 
        }

        /* Refined Scrollbar Styling */
        .sidebar-sticky-area::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-sticky-area::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-sticky-area::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        .sidebar-sticky-area:hover::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
        }
    }

    /* Reset inner card to fit wrapper */
    .filter-sidebar {
        backdrop-filter: blur(10px);
        background: var(--glass-bg);
        border: 1px solid rgba(0,0,0,0.05) !important;
        /* Height handled by wrapper now */
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        padding-right: 5px;
    }

    /* Custom Scrollbar for Sidebar */
    .filter-sidebar::-webkit-scrollbar {
        width: 5px;
    }

    .filter-sidebar::-webkit-scrollbar-track {
        background: transparent;
    }

    .filter-sidebar::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 10px;
    }
    
    .filter-sidebar::-webkit-scrollbar-thumb:hover {
        background: #bbb;
    }

    .letter-spacing-1 {
        letter-spacing: 1px;
    }

    .status-filters .status-label {
        transition: var(--transition);
        border-color: #eee !important;
    }

    .status-filters .btn-check:checked + .status-label {
        background-color: var(--primary-color) !important;
        color: white !important;
        border-color: var(--primary-color) !important;
        transform: translateX(5px);
    }

    .category-tree-list li {
        margin-bottom: 2px;
    }

    .category-item .sub-category-list {
        max-height: 0;
        overflow: hidden;
        opacity: 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        margin-top: 0 !important;
    }

    /* Show on Click/Open */
    .category-item.open .sub-category-list {
        max-height: 500px;
        opacity: 1;
        margin-top: 5px !important;
        margin-bottom: 10px;
    }

    .sub-dropdown-toggle {
        border: none !important;
        box-shadow: none !important;
        color: #bbb !important;
        transition: all 0.3s ease;
    }

    .category-item.open .sub-dropdown-toggle {
        color: var(--primary-color) !important;
    }

    .category-item.open .sub-dropdown-toggle i {
        transform: rotate(-180deg);
    }

    .sub-category-list {
        border-color: rgba(78, 115, 223, 0.1) !important;
    }

    .subcategory-dot {
        width: 6px;
        height: 6px;
        background: var(--primary-color);
        border-radius: 50%;
        display: inline-block;
        vertical-align: middle;
        margin-right: 8px;
        opacity: 0.2;
        transition: all 0.3s ease;
    }

    .category-link.active .subcategory-dot {
        opacity: 1;
        box-shadow: 0 0 8px var(--primary-color);
    }

    .category-link {
        display: block;
        padding: 8px 12px;
        border-radius: 8px;
        color: #444;
        text-decoration: none;
        font-size: 0.88rem;
        font-weight: 500;
        transition: var(--transition);
    }

    .category-link:hover, .category-link.active {
        background-color: rgba(78, 115, 223, 0.08);
        color: var(--primary-color);
        transform: translateX(5px);
    }

    /* Auction Card Styles */
    .auction-card {
        transition: var(--transition);
        border: 1px solid rgba(0,0,0,0.03) !important;
    }

    .auction-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 1rem 3rem rgba(0,0,0,0.1) !important;
    }

    .auction-img-wrapper {
        border-radius: 12px 12px 0 0;
    }

    .auction-img {
        transform: scale(1.02);
    }

    .auction-card:hover .auction-img {
        transform: scale(1.1);
    }

    .glass-badge {
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255,255,255,0.3);
    }

    .btn-watchlist {
        background: white;
        border: none;
    }

    .btn-watchlist:hover {
        transform: scale(1.1);
        background: #fff;
    }

    .cursor-pointer {
        cursor: pointer;
    }
    
    .hover-bg-light:hover {
        background-color: #f8f9fc !important;
    }
    
    .bg-gradient-dark-transparent {
        background: linear-gradient(to top, rgba(0,0,0,0.7), transparent);
    }

    .animate-pulse {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.7; }
        100% { opacity: 1; }
    }

    .pulse-dot {
        width: 6px;
        height: 6px;
        background: white;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 rgba(255,255,255, 0.4);
        animation: pulse-dot 2s infinite;
    }

    @keyframes pulse-dot {
        0% { box-shadow: 0 0 0 0 rgba(255,255,255, 0.7); }
        70% { box-shadow: 0 0 0 8px rgba(255,255,255, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255,255,255, 0); }
    }

    .title-hover a:hover {
        color: var(--primary-color) !important;
    }

    .btn-bid-view {
        background: var(--primary-color);
        position: relative;
        z-index: 1;
        overflow: hidden;
    }

    .btn-bid-view::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.05);
        z-index: -1;
        transform: translateY(100%);
        transition: transform 0.3s ease;
    }

    .btn-bid-view:hover::after {
        transform: translateY(0);
    }

    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .w-fit-content {
        width: fit-content;
    }

    .bg-gold {
        background-color: var(--gold-color);
    }

    .text-gold {
        color: var(--gold-color);
    }

    /* Custom Scrollbar for Tree */
    .category-tree-list {
        max-height: 400px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #ddd transparent;
    }

    .category-tree-list::-webkit-scrollbar {
        width: 4px;
    }

    .category-tree-list::-webkit-scrollbar-track {
        background: transparent;
    }

    .category-tree-list::-webkit-scrollbar-thumb {
        background: #ddd;
        border-radius: 10px;
    }

    /* Home Page Replica Styles */
    .card-elite {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .card-elite:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
    }
    .bg-gold {
        background-color: #d4af37;
    }
    .text-xs {
        font-size: 0.75rem;
    }
    .glass-timer {
        background: rgba(248, 249, 250, 0.8);
        backdrop-filter: blur(4px);
        border-radius: 8px;
    }
    .fs-7 {
        font-size: 0.9rem;
    }
    /* Premium Scrollbar for Sidebar */
    .sidebar-sticky-area {
        scrollbar-width: thin;
        scrollbar-color: rgba(0,0,0,0.1) transparent;
    }
    
    .sidebar-sticky-area::-webkit-scrollbar {
        width: 4px;
    }
    .sidebar-sticky-area::-webkit-scrollbar-track {
        background: transparent;
    }
    .sidebar-sticky-area::-webkit-scrollbar-thumb {
        background-color: rgba(0,0,0,0.1);
        border-radius: 20px;
    }
    .sidebar-sticky-area:hover::-webkit-scrollbar-thumb {
        background-color: rgba(0,0,0,0.2);
    }

    /* Enhanced Category Links */
    .category-link {
        display: block;
        padding: 10px 14px;
        border-radius: 12px;
        color: #5a5c69;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease-in-out;
        border: 1px solid transparent;
    }

    .category-link:hover {
        background-color: rgba(78, 115, 223, 0.05);
        color: var(--primary-color);
        transform: translateX(3px);
    }

    .category-link.active {
        background-color: rgba(78, 115, 223, 0.1);
        color: var(--primary-color);
        font-weight: 700;
        border-color: rgba(78, 115, 223, 0.1);
        box-shadow: 0 4px 12px rgba(78, 115, 223, 0.1);
    }

    /* Soften the Filter Sidebar */
    .filter-sidebar {
        background: rgba(255, 255, 255, 0.8);
        box-shadow: none !important; /* Cleaner look inside the sticky wrapper */
    }

    /* Premium Button Hover */
    .btn-hover-effect:active {
        transform: scale(0.98);
    }

    /* Urgent Timer Style */
    .urgent-timer {
        background: rgba(220, 53, 69, 0.08) !important;
        border-color: rgba(220, 53, 69, 0.3) !important;
    }
    .urgent-timer * {
        color: #dc3545 !important;
    }
    .urgent-timer .border-light {
        border-color: rgba(220, 53, 69, 0.2) !important;
    }
    .urgent-timer .border-light {
        border-color: rgba(220, 53, 69, 0.2) !important;
    }

    /* Standard Pagination Styling (To match My Auctions look) */
    .pagination {
        margin-bottom: 0;
        gap: 2px;
    }
    .page-item .page-link {
        border-radius: 6px !important;
        color: #4e73df;
        border: 1px solid #eaecf4;
        padding: 8px 14px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .page-item.active .page-link {
        background-color: #4e73df;
        border-color: #4e73df;
        color: #fff;
    }
    .page-item.disabled .page-link {
        color: #d1d3e2;
        background-color: #f8f9fc;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Category Dropdown Toggle (Accordion Style)
        document.querySelectorAll('.sub-dropdown-toggle').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const currentItem = this.closest('.category-item');
                const wasOpen = currentItem.classList.contains('open');
                
                // Close all other open items
                document.querySelectorAll('.category-item.open').forEach(item => {
                    if (item !== currentItem) {
                        item.classList.remove('open');
                    }
                });
                
                // Toggle current item
                if (wasOpen) {
                    currentItem.classList.remove('open');
                } else {
                    currentItem.classList.add('open');
                }
            });
        });

        // --- AJAX FILTERING LOGIC ---
        const filterForm = document.getElementById('filterForm');
        const resultsContainer = document.getElementById('auction-results-container');
        const loadingOverlay = document.getElementById('loading-overlay');
        const sidebar = document.querySelector('.filter-sidebar');

        function updateAuctions(url, pushState = true) {
            loadingOverlay.classList.remove('d-none');
            loadingOverlay.classList.add('d-flex');
            resultsContainer.style.opacity = '0.5';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                resultsContainer.innerHTML = html;
                loadingOverlay.classList.add('d-none');
                loadingOverlay.classList.remove('d-flex');
                resultsContainer.style.opacity = '1';

                if (pushState) {
                    window.history.pushState({ path: url }, '', url);
                }

                // Refresh AOS animations for new elements
                if (typeof AOS !== 'undefined') {
                    AOS.refresh();
                }

                // Re-initialize any necessary UI components
                if (typeof initializeTimers === 'function') {
                    initializeTimers();
                }

                // Smooth scroll to results on mobile
                if (window.innerWidth < 992) {
                    const gridTop = resultsContainer.getBoundingClientRect().top + window.pageYOffset - 100;
                    window.scrollTo({ top: gridTop, behavior: 'smooth' });
                }
            })
            .catch(error => {
                console.error('Error fetching auctions:', error);
                loadingOverlay.classList.add('d-none');
                loadingOverlay.classList.remove('d-flex');
                resultsContainer.style.opacity = '1';
            });
        }

        // Handle Status Radio Changes
        document.querySelectorAll('.status-filter-input').forEach(input => {
            input.addEventListener('change', function() {
                const formData = new FormData(filterForm);
                const params = new URLSearchParams(formData);
                
                // Ensure current sorting is preserved if it exists in URL
                const currentUrl = new URL(window.location.href);
                if (currentUrl.searchParams.has('sort')) {
                    params.set('sort', currentUrl.searchParams.get('sort'));
                }

                const url = `${window.location.pathname}?${params.toString()}`;
                updateAuctions(url);

                // Update Visuals (active state)
                document.querySelectorAll('.status-filters label').forEach(lbl => {
                    lbl.classList.remove('bg-primary-subtle', 'text-primary', 'fw-bold');
                    lbl.classList.add('hover-bg-light', 'text-secondary');
                    const icon = lbl.querySelector('i.fas');
                    if(icon) icon.classList.replace('text-primary', 'text-muted');
                    const check = lbl.querySelector('i.fa-check-circle');
                    if(check) check.remove();
                });

                const parentLabel = this.closest('label');
                parentLabel.classList.add('bg-primary-subtle', 'text-primary', 'fw-bold');
                parentLabel.classList.remove('hover-bg-light', 'text-secondary');
                const icon = parentLabel.querySelector('i.fas');
                if(icon) icon.classList.replace('text-muted', 'text-primary');
                parentLabel.insertAdjacentHTML('beforeend', '<i class="fas fa-check-circle small"></i>');
            });
        });

        // Handle Category & Clear All Links
        document.addEventListener('click', function(e) {
            const link = e.target.closest('.ajax-filter-link');
            if (link) {
                e.preventDefault();
                const url = link.getAttribute('href');
                updateAuctions(url);

                // Update active state for categories
                if (link.classList.contains('category-link')) {
                    document.querySelectorAll('.category-link').forEach(l => l.classList.remove('active', 'fw-bold'));
                    link.classList.add('active', 'fw-bold');
                }
            }
        });

        // Handle Form Submit (Price Range)
        filterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            
            // Preserve category from URL if not in form (though it is usually a hidden input or part of URL)
            const currentUrl = new URL(window.location.href);
            if (currentUrl.searchParams.has('category')) {
                params.set('category', currentUrl.searchParams.get('category'));
            }
            if (currentUrl.searchParams.has('sort')) {
                params.set('sort', currentUrl.searchParams.get('sort'));
            }

            const url = `${window.location.pathname}?${params.toString()}`;
            updateAuctions(url);
        });

        // Handle Pagination Clicks
        document.addEventListener('click', function(e) {
            const paginationLink = e.target.closest('#pagination-container .page-link');
            if (paginationLink) {
                e.preventDefault();
                const url = paginationLink.getAttribute('href');
                if (url && url !== '#') {
                    updateAuctions(url);
                }
            }
        });

        // Handle Popstate (Back/Forward browser buttons)
        window.addEventListener('popstate', function(e) {
            updateAuctions(window.location.href, false);
        });

        // Global Sort Function (Replaced from the one in _auction_grid)
        window.applySort = function(sortValue) {
            const url = new URL(window.location.href);
            url.searchParams.set('sort', sortValue);
            
            if (sortValue === 'price_asc' || sortValue === 'price_desc') {
                url.searchParams.delete('min_price');
                url.searchParams.delete('max_price');
            }
            if (sortValue === 'latest') {
                url.searchParams.delete('sort');
            }

            updateAuctions(url.toString());
        };
    });
</script>
@endpush

@endsection


