<a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
    <i class="fas fa-tachometer-alt"></i> Dashboard
</a>
<a class="nav-link {{ request()->routeIs('admin.customers') ? 'active' : '' }}" href="{{ route('admin.customers') }}">
    <i class="fas fa-users"></i> Customers
</a>
<a class="nav-link {{ request()->routeIs('admin.sim-cards*') ? 'active' : '' }}" href="{{ route('admin.sim-cards') }}">
    <i class="fas fa-sim-card"></i> SIM Cards
</a>
<a class="nav-link {{ request()->routeIs('admin.pending') ? 'active' : '' }}" href="{{ route('admin.pending') }}">
    <i class="fas fa-clock"></i> Pending Approvals
</a>
<a class="nav-link {{ request()->routeIs('admin.history') ? 'active' : '' }}" href="{{ route('admin.history') }}">
    <i class="fas fa-history"></i> Transaction History
</a>
<a class="nav-link {{ request()->routeIs('admin.api-checker') ? 'active' : '' }}" href="{{ route('admin.api-checker') }}">
    <i class="fas fa-code"></i> API Checker
</a>
