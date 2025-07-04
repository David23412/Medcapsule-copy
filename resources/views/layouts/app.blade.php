<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MedCapsule') }} - @yield('title', 'Admin Panel')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        .navbar-brand {
            font-weight: 600;
            color: #0891b2 !important;
        }
        .nav-link {
            font-weight: 500;
        }
        .main-content {
            min-height: calc(100vh - 80px);
        }
        .card-header {
            background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%);
            color: white;
            font-weight: 600;
        }
        .btn-primary {
            background-color: #0891b2;
            border-color: #0891b2;
        }
        .btn-primary:hover {
            background-color: #0e7490;
            border-color: #0e7490;
        }
        .alert-info {
            background-color: #f0f9ff;
            border-color: #0891b2;
            color: #0c4a6e;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8fafc;
        }
        .sidebar {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-right: 1px solid #e2e8f0;
        }
        .sidebar .nav-link {
            color: #475569;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }
        .sidebar .nav-link:hover {
            background-color: #e2e8f0;
            color: #0891b2;
        }
        .sidebar .nav-link.active {
            background-color: #0891b2;
            color: white;
        }
    </style>

    @stack('styles')
</head>

<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-capsule me-2"></i>
                {{ config('app.name', 'MedCapsule') }}
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @auth
                        @if(auth()->user()->is_admin)
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.payments.pending') }}">
                                    <i class="bi bi-credit-card me-1"></i>
                                    Payments
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('questions.add') }}">
                                    <i class="bi bi-plus-circle me-1"></i>
                                    Add Question
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>
                
                <ul class="navbar-nav">
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i>
                                {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('home') }}">Dashboard</a></li>
                                <li><a class="dropdown-item" href="{{ route('notifications.index') }}">Notifications</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        Logout
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            @auth
                @if(auth()->user()->is_admin)
                    <div class="col-md-2 sidebar py-3">
                        <nav class="nav flex-column">
                            <a class="nav-link {{ Request::is('admin/payments*') ? 'active' : '' }}" href="{{ route('admin.payments.pending') }}">
                                <i class="bi bi-credit-card me-2"></i>
                                Payment Verification
                            </a>
                            <a class="nav-link {{ Request::is('add-question') ? 'active' : '' }}" href="{{ route('questions.add') }}">
                                <i class="bi bi-plus-circle me-2"></i>
                                Add Question
                            </a>
                            <a class="nav-link {{ Request::is('payment-settings') ? 'active' : '' }}" href="{{ route('payment.settings') }}">
                                <i class="bi bi-gear me-2"></i>
                                Payment Settings
                            </a>
                            <a class="nav-link {{ Request::is('notifications') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                                <i class="bi bi-bell me-2"></i>
                                Notifications
                            </a>
                        </nav>
                    </div>
                    <div class="col-md-10 main-content py-4">
                @else
                    <div class="col-12 main-content py-4">
                @endif
            @else
                <div class="col-12 main-content py-4">
            @endauth
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <!-- Main Content -->
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Custom JS -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);

        // CSRF token for AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>

    @stack('scripts')
</body>
</html>