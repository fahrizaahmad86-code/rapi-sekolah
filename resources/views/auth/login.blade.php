<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RAPI-Sekolah</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #F8FAFC;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: #FFFFFF;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.05), 0 8px 10px -6px rgba(15, 23, 42, 0.01);
            padding: 40px 32px;
        }

        .brand-title {
            color: #1E293B;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        .form-label {
            color: #1E293B;
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 6px;
        }

        .form-control {
            border-radius: 10px;
            border: 1.5px solid #E2E8F0;
            padding: 12px 16px;
            font-size: 0.95rem;
            color: #0F172A;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #0EA5E9;
            box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.15);
        }

        .btn-primary-custom {
            background-color: #0EA5E9;
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 1rem;
            color: #FFFFFF;
            transition: all 0.2s ease;
        }

        .btn-primary-custom:hover {
            background-color: #0284C7;
            transform: translateY(-1px);
        }

        .btn-primary-custom:active {
            transform: translateY(0);
        }

        .link-back {
            color: #64748B;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: color 0.2s;
        }

        .link-back:hover {
            color: #0EA5E9;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="brand-title mb-1">RAPI-Sekolah</h3>
        <p class="text-muted small">Gerbang Login Terpusat</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success small">{{ session('success') }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        
        <!-- Username Input -->
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <div class="input-group">
                <input type="text" 
                       name="username" 
                       id="username" 
                       class="form-control @error('username') is-invalid @enderror" 
                       placeholder="Masukkan username" 
                       value="{{ old('username') }}" 
                       maxlength="50"
                       onkeypress="return event.charCode != 32"
                       required autofocus>
            </div>
            @error('username')
                <div class="invalid-feedback d-block mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Password Input -->
        <div class="mb-4">
            <label for="password" class="form-label">Password</label>
            <input type="password" 
                   name="password" 
                   id="password" 
                   class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Masukkan password" 
                   maxlength="30"
                   required>
            @error('password')
                <div class="invalid-feedback d-block mt-1">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary-custom w-100 mb-3">
            Masuk Sistem
        </button>

        <!-- Lupa Password -->
        <div class="text-center mb-3">
            <a href="{{ route('password.request') }}" class="link-back">Lupa password?</a>
        </div>

        <!-- Back to Public Calendar -->
        <div class="text-center">
            <a href="{{ route('home') }}" class="link-back">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Kalender Publik
            </a>
        </div>
    </form>
</div>

</body>
</html>     