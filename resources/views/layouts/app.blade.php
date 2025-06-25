<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kompresi File</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@500;700&family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f2027 0%, #2c5364 100%);
            font-family: 'Roboto', Arial, sans-serif;
            color: #e0e0e0;
        }
        .navbar {
            background: rgba(20, 30, 48, 0.95) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .navbar-brand {
            font-family: 'Orbitron', Arial, sans-serif;
            font-size: 1.7rem;
            letter-spacing: 2px;
            color: #00fff0 !important;
        }
        .futuristic-card {
            background: rgba(30, 40, 60, 0.85);
            border: 1px solid #00fff0;
            border-radius: 18px;
            box-shadow: 0 4px 32px 0 rgba(0,255,240,0.08), 0 1.5px 8px 0 rgba(0,0,0,0.15);
            padding: 2.5rem 2rem;
            margin-top: 3rem;
            max-width: 480px;
            margin-left: auto;
            margin-right: auto;
        }
        h2, label, .btn-primary {
            font-family: 'Orbitron', Arial, sans-serif;
        }
        .btn-primary {
            background: linear-gradient(90deg, #00fff0 0%, #007cf0 100%);
            border: none;
            color: #181c24;
            font-weight: 700;
            letter-spacing: 1px;
            box-shadow: 0 2px 8px 0 rgba(0,255,240,0.15);
            transition: background 0.2s, color 0.2s;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, #007cf0 0%, #00fff0 100%);
            color: #181c24;
        }
        .form-control, .form-check-input {
            background: rgba(255,255,255,0.08);
            color: #e0e0e0;
            border: 1px solid #00fff0;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.15);
            color: #fff;
            border-color: #007cf0;
            box-shadow: 0 0 0 0.2rem rgba(0,255,240,0.15);
        }
        .form-check-input:checked {
            background-color: #00fff0;
            border-color: #00fff0;
        }
        .alert-danger {
            background: rgba(255,0,80,0.12);
            color: #ff4b7d;
            border: 1px solid #ff4b7d;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/compress">Kompresi File</a>
        </div>
    </nav>
    <main>
        @yield('content')
    </main>
</body>
</html> 