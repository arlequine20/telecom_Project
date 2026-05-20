<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Telecom Auth')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffe5d1 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .auth-card {
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            border: none;
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
    </style>
</head>
<body>
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card auth-card w-100" style="max-width: 480px;">
            <div class="card-body p-5">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
