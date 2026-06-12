<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Telecom Auth')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: radial-gradient(circle at top, rgba(255, 107, 0, 0.18), transparent 25%),
                        linear-gradient(135deg, #fff8f0 0%, #ffe1c5 45%, #ffcc9c 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-card {
            border-radius: 28px;
            box-shadow: 0 24px 70px rgba(0,0,0,0.12);
            border: none;
            background-color: rgba(255, 255, 255, 0.98);
        }

        .hero-panel {
            min-height: 480px;
            background: linear-gradient(180deg, rgba(255, 241, 227, 0.95) 0%, rgba(255, 236, 216, 0.95) 100%);
        }

        @media (max-width: 991px) {
            .hero-panel {
                min-height: auto;
                padding: 2rem 1.5rem;
            }
        }
        .auth-header {
            color: #ff6b00;
            font-weight: 700;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #ff6b00;
        }
        .btn-primary {
            background-color: #ff6b00;
            border: none;
        }
        .btn-primary:hover {
            background-color: #e66000;
        }
        .feature-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 999px;
            background: rgba(255, 107, 0, 0.1);
            color: #ff6b00;
            font-weight: 600;
        }
        .icon-badge {
            width: 52px;
            height: 52px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            background: rgba(255, 107, 0, 0.14);
            color: #ff6b00;
            font-size: 1.25rem;
        }
        footer a {
            color: #ff6b00;
            text-decoration: none;
        }
        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container-fluid min-vh-100 py-4">
        <div class="row g-4 align-items-center justify-content-center min-vh-100">
            @hasSection('hero')
                <div class="col-12 col-lg-6">
                    <div class="hero-panel h-100 rounded-4 p-4 p-lg-5 shadow-sm bg-white">
                        @yield('hero')
                    </div>
                </div>
            @endif

            <div class="col-12 col-lg-4">
                <div class="card auth-card w-100">
                    <div class="card-body p-5">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-4 text-center">
        <div class="container">
            <div class="card shadow-sm mx-auto" style="max-width:920px; border-radius:12px;">
                <div class="card-body py-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-3">
                    <div class="text-start">
                        <h6 class="mb-1" style="color:#ff6b00;">Telecom System</h6>
                        <p class="mb-0 text-muted" style="font-size:0.95rem;">Modern telecom payments — fast, secure, and built for mobile-first transfers.</p>
                    </div>
                    <div class="text-md-end">
                        <div class="mb-1"><strong>Contact:</strong> <a href="mailto:help@telecom.example">help@telecom.example</a> | +250 788 000 000</div>
                        <div class="small text-muted">Follow us: <a href="#">Twitter</a> · <a href="#">LinkedIn</a></div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
