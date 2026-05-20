<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telecom System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #ffecdc 0%, #ffffff 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .hero-card {
            border-radius: 24px;
            box-shadow: 0 20px 80px rgba(0,0,0,0.08);
            border: none;
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
    <div class="hero">
        <div class="card hero-card shadow-sm w-100" style="max-width: 900px;">
            <div class="row g-0 align-items-center">
                <div class="col-md-6 p-5">
                    <h1 class="display-6 fw-bold">Welcome to Telecom System</h1>
                    <p class="lead text-muted">Manage SIM cards, review transactions, buy data, and send money with a role-based dashboard built for customers and admins.</p>
                    <div class="mt-4">
                        <a href="{{ route('login') }}" class="btn btn-primary btn-lg me-2">Login</a>
                        <a href="{{ route('register.show') }}" class="btn btn-outline-secondary btn-lg">Register</a>
                    </div>
                </div>
                <div class="col-md-6 d-none d-md-block">
                    <div class="p-5 text-center">
                        <img src="https://cdn.jsdelivr.net/gh/tabler/tabler-icons@latest/icons/phone.svg" alt="Telecom" width="180" class="img-fluid opacity-75">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>