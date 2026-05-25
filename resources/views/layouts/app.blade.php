<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Telecom System')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #FF6B00;
            --primary-dark: #e66000;
            --dark: #1A1A2E;
            --success: #00B894;
            --danger: #FF3B30;
            --warning: #FFA502;
            --gray-bg: #F8F9FA;
        }
        
        html,
        body {
            min-height: 100%;
        }

        body {
            background-color: var(--gray-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .app-shell {
            min-height: 100vh;
        }

        .sidebar-column {
            background-color: var(--dark);
        }
        
        .sidebar {
            background-color: var(--dark);
            min-height: 100%;
            padding: 20px 0;
        }
        
        .sidebar .nav-link {
            color: #fff;
            padding: 12px 24px;
            margin: 4px 0;
            transition: all 0.3s;
            border-radius: 8px;
        }
        
        .sidebar .nav-link:hover {
            background-color: rgba(255, 107, 0, 0.2);
            color: var(--primary);
        }
        
        .sidebar .nav-link.active {
            background-color: var(--primary);
            color: white;
        }
        
        .sidebar .nav-link i {
            width: 24px;
            margin-right: 12px;
        }
        
        .navbar-top {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 12px 24px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border: none;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .btn-primary-custom {
            background-color: var(--primary);
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
        }
        
        .btn-primary-custom:hover {
            background-color: var(--primary-dark);
        }
        
        .table-custom {
            background: white;
            border-radius: 12px;
            overflow: hidden;
        }
        
        .table-custom th {
            background-color: var(--dark);
            color: white;
            border: none;
        }
        
        .badge-approved {
            background-color: var(--success);
        }
        
        .badge-pending {
            background-color: var(--warning);
        }
        
        .badge-cancelled {
            background-color: var(--danger);
        }
        
        .content-wrapper {
            padding: 24px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: bold;
            color: var(--dark);
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
    <div class="container-fluid app-shell">
        <div class="row app-shell">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar-column">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <h4 class="text-white">📱 TELECOM</h4>
                    </div>
                    <nav class="nav flex-column">
                        @yield('sidebar')
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-0">
                <!-- Top Navbar -->
                <div class="navbar-top d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">@yield('header-title')</h5>
                    <div class="d-flex align-items-center gap-3">
                        <span>
                            <i class="fas fa-user-circle"></i>
                            @yield('user-name', 'Admin')
                        </span>
                        @if(auth()->check())
                            <form method="POST" action="{{ route('logout') }}" class="mb-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">Logout</button>
                            </form>
                        @endif
                    </div>
                </div>
                
                <!-- Content -->
                <div class="content-wrapper">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    
                    @yield('content')
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
