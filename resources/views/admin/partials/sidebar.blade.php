<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-gavel"></i>
        </div>
        <div class="sidebar-brand-text mx-3">Auction Admin</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Management
    </div>

    <!-- Nav Item - Auctions -->
    <li class="nav-item {{ request()->routeIs('admin.auctions.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.auctions.index') }}">
            <i class="fas fa-fw fa-hammer"></i>
            <span>Auctions</span></a>
    </li>

    <!-- Nav Item - Users -->
    <li class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.users.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Manage Users</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Finance & Reports
    </div>

    <!-- Nav Item - Payments -->
    <li class="nav-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.payments.index') }}">
            <i class="fas fa-fw fa-wallet"></i>
            <span>Payments</span></a>
    </li>

    <!-- Nav Item - Reports -->
    <li class="nav-item {{ request()->routeIs('admin.reports') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.reports') }}">
            <i class="fas fa-fw fa-chart-line"></i>
            <span>Reports</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        System Config
    </div>

    <!-- Nav Item - Categories -->
    <li class="nav-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.categories.index') }}">
            <i class="fas fa-fw fa-list"></i>
            <span>Categories</span></a>
    </li>

    <!-- Nav Item - Contacts -->
    <li class="nav-item {{ request()->routeIs('admin.contacts.*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.contacts.index') }}">
            <i class="fas fa-fw fa-envelope"></i>
            <span>Contact Messages</span></a>
    </li>

    <!-- Nav Item - Settings -->
    <li class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.settings') }}">
            <i class="fas fa-fw fa-cogs"></i>
            <span>Settings</span></a>
    </li>

    <!-- Nav Item - Notifications -->
    <li class="nav-item {{ request()->routeIs('admin.notifications') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.notifications') }}">
            <i class="fas fa-fw fa-bell"></i>
            <span>Notifications</span></a>
    </li>
    
    <!-- Divider -->
    <hr class="sidebar-divider">
    
    <!-- Heading -->
    <div class="sidebar-heading">
        Account
    </div>
    
    <!-- Nav Item - Logout -->
    <li class="nav-item">
        <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-sidebar-form').submit();">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
        <form id="logout-sidebar-form" action="{{ route('logout') }}" method="POST" class="d-none">
            @csrf
        </form>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

<script>
    if (localStorage.getItem('sb|sidebar-toggle') === 'true') {
        document.body.classList.add('sidebar-toggled');
        var sidebar = document.getElementById('accordionSidebar');
        if (sidebar) sidebar.classList.add('toggled');
    }
</script>
<!-- End of Sidebar -->
