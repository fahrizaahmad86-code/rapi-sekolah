<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - RAPI-Sekolah</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        body { background-color:#F8FAFC; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif; }
        .login-card { width:100%; max-width:420px; background:#FFF; border-radius:16px; border:1px solid #E2E8F0; box-shadow:0 10px 25px -5px rgba(15,23,42,.05),0 8px 10px -6px rgba(15,23,42,.01); padding:40px 32px; }
        .brand-title { color:#1E293B; font-weight:700; letter-spacing:-.5px; }
        .form-label { color:#1E293B; font-weight:600; font-size:.9rem; margin-bottom:6px; }
        .form-control { border-radius:10px; border:1.5px solid #E2E8F0; padding:12px 16px; font-size:.95rem; color:#0F172A; transition:all .2s ease; }
        .form-control:focus { border-color:#0EA5E9; box-shadow:0 0 0 4px rgba(14,165,233,.15); }
        .btn-primary-custom { background-color:#0EA5E9; border:none; border-radius:10px; padding:12px; font-weight:600; font-size:1rem; color:#FFF; transition:all .2s ease; }
        .btn-primary-custom:hover { background-color:#0284C7; color:#FFF; transform:translateY(-1px); }
        .btn-primary-custom:active { transform:translateY(0); }
        .link-back { color:#64748B; text-decoration:none; font-size:.9rem; font-weight:500; transition:color .2s; }
        .link-back:hover { color:#0EA5E9; }
        .otp-input { text-align:center; font-size:1.75rem; font-weight:700; letter-spacing:.6rem; padding-left:calc(16px + .6rem); }
        .alert { border-radius:10px; font-size:.9rem; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="text-center mb-4">
        <h3 class="brand-title mb-1">RAPI-Sekolah</h3>
        <p class="text-muted small mb-0">@yield('subtitle')</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @yield('content')
</div>
</body>
</html>
