<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RAPI-Sekolah</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #F5F5F5; 
            min-height: 100vh;
        }

        .wrapper {
            display: flex;
            width: 100%;
            align-items: stretch;
        }

        #sidebar {
            min-width: 260px;
            max-width: 260px;
            background-color: #FFFFFF; 
            border-right: 1px solid #E2E8F0; 
            transition: all 0.3s;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        #sidebar .sidebar-header {
            padding: 20px;
            background-color: #FFFFFF;
            border-bottom: 1px solid #E2E8F0;
        }

        #sidebar ul.components {
            padding: 15px;
        }

        #sidebar ul li {
            margin-bottom: 6px;
        }

        #sidebar ul li a {
            padding: 12px 16px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            color: #1E293B; 
            text-decoration: none;
            transition: 0.2s ease;
            border-radius: 8px; 
            font-weight: 500;
            background-color: transparent;
        }

        #sidebar ul li a:hover,
        #sidebar ul li a.active {
            color: #0EA5E9 !important; 
            background-color: #E0F2FE !important; 
            font-weight: 600;
        }

        #sidebar ul li a i {
            margin-right: 12px;
            font-size: 1.2rem;
        }

        #content {
            width: 100%;
            padding: 30px;
            min-height: 100vh;
        }

        .btn-logout:hover {
            background-color: #FEE2E2 !important; 
            color: #DC2626 !important; 
        }

        .btn-profil:hover,
        .btn-profil.active {
            background-color: #E0F2FE !important;
            color: #0EA5E9 !important;
        }

        .table-responsive {
            overflow-x: auto;
        }

        #content table.text-nowrap {
            white-space: normal !important;
        }

        .dropdown-menu { max-height: 70vh; overflow-y: auto; }
    </style>
</head>
<body>
@php
    $user = auth()->user();
@endphp

<div class="wrapper">
    <nav id="sidebar">
        <div class="sidebar-header">
            <h4 class="fw-bold m-0" style="color: #1E293B;">RAPI-Sekolah</h4>
        </div>

        <ul class="list-unstyled components flex-grow-1">
            @guest
                <li>
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                         Kalender Agenda
                    </a>
                </li>
                <li>
                    <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'active' : '' }}">
                         Login 
                    </a>
                </li>
            @endguest

            @auth
            @if($user && $user->role_id == 1)
                <li>
                    <a href="{{ route('waka.dashboard') }}" class="{{ request()->routeIs('waka.dashboard') ? 'active' : '' }}">
                         Dashboard 
                    </a>
                </li>
                <li>
                    <a href="{{ route('user.index') }}" class="{{ request()->routeIs('user.*') ? 'active' : '' }}">
                         Kelola Pengguna
                    </a>
                </li>
                <li>
                    <a href="{{ route('ruangan.index') }}" class="{{ request()->routeIs('ruangan.*') ? 'active' : '' }}">
                         Kelola Ruangan
                    </a>
                </li>
                <li>
                    <a href="{{ route('cari-ruangan.index') }}" class="{{ request()->routeIs('cari-ruangan.*') ? 'active' : '' }}">
                         Cari Ruangan
                    </a>
                </li>
                <li>
                    <a href="{{ route('peminjaman.index') }}" class="{{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                         Peminjaman Ruangan
                    </a>
                </li>
                <li>
                    <a href="{{ route('pengaduan.index') }}" class="{{ request()->routeIs('pengaduan.*') ? 'active' : '' }}">
                         Pengaduan Kerusakan 
                    </a>
                </li>
                <li>
                    <a href="{{ route('laporan.harian') }}" class="{{ request()->routeIs('laporan.harian*') ? 'active' : '' }}">
                         Laporan Harian
                    </a>
                </li>
                <li>
                    <a href="{{ route('laporan.bulanan') }}" class="{{ request()->routeIs('laporan.bulanan*') ? 'active' : '' }}">
                         Laporan Bulanan
                    </a>
                </li>

            @elseif($user && $user->role_id == 2)
                <li>
                    <a href="{{ route('guru.dashboard') }}" class="{{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
                         Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('cari-ruangan.index') }}" class="{{ request()->routeIs('cari-ruangan.*') ? 'active' : '' }}">
                         Cari Ruangan
                    </a>
                </li>
                <li>
                    <a href="{{ route('peminjaman.index') }}" class="{{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                         Peminjaman Ruang
                    </a>
                </li>
                <li>
                    <a href="{{ route('pengaduan.index') }}" class="{{ request()->routeIs('pengaduan.*') ? 'active' : '' }}">
                         Lapor Kerusakan
                    </a>
                </li>

            @elseif($user && $user->role_id == 3)
                <li>
                    <a href="{{ route('pic.dashboard') }}" class="{{ request()->routeIs('pic.dashboard') ? 'active' : '' }}">
                         Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('pengaduan.index') }}" class="{{ request()->routeIs('pengaduan.*') ? 'active' : '' }}">
                         Status Perbaikan
                    </a>
                </li>

            @elseif($user && $user->role_id == 4)
                <li>
                    <a href="{{ route('organisasi.dashboard') }}" class="{{ request()->routeIs('organisasi.dashboard') ? 'active' : '' }}">
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('cari-ruangan.index') }}" class="{{ request()->routeIs('cari-ruangan.*') ? 'active' : '' }}">
                         Cari Ruangan
                    </a>
                </li>
                <li>
                    <a href="{{ route('peminjaman.index') }}" class="{{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                         Peminjaman Ruang
                    </a>
                </li>
                <li>
                    <a href="{{ route('pengaduan.index') }}" class="{{ request()->routeIs('pengaduan.*') ? 'active' : '' }}">
                         Lapor Kerusakan
                    </a>
                </li>
            @endif
            @endauth
        </ul>

        @auth
        @if($user)
            <div class="p-3 border-top" style="border-color: #E2E8F0 !important;">
                <a href="{{ route('profil.show') }}" class="btn text-start w-100 d-flex align-items-center py-2 px-3 border-0 fw-medium btn-profil mb-1 {{ request()->routeIs('profil.*') ? 'active' : '' }}" style="color: #1E293B; border-radius: 8px; font-size: 1rem;">
                     Profil
                </a>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn text-start w-100 d-flex align-items-center py-2 px-3 border-0 fw-medium btn-logout" style="color: #1E293B; border-radius: 8px; font-size: 1rem;">
                         Logout
                    </button>
                </form>
            </div>
        @endif
        @endauth
        
    </nav>

    <div id="content">
        @auth
        @if(auth()->user()->role_id != 1)
        <div class="d-flex justify-content-end mb-4">
            <div class="dropdown">
                <button class="btn btn-white position-relative border shadow-sm rounded-circle p-2 bg-white" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-bell fs-5 text-dark"></i>
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    @endif
                </button>

                <ul class="dropdown-menu dropdown-menu-end p-2 shadow-lg border-0" style="width: 320px;">
                    <li class="dropdown-header fw-bold text-dark border-bottom pb-2 mb-2 d-flex justify-content-between align-items-center">
                        <span>Notifikasi</span>
                        <span class="badge bg-primary text-white" style="font-size: 0.7rem;">{{ auth()->user()->unreadNotifications->count() }} Baru</span>
                    </li>

                    @forelse(auth()->user()->unreadNotifications as $notification)
                        <li class="mb-1">
                            <a href="{{ route('notifications.read', $notification->id) }}" class="dropdown-item p-2 rounded bg-light text-wrap">
                                <strong class="d-block small text-primary">{{ $notification->data['title'] }}</strong>
                                <span class="small text-dark d-block">{{ $notification->data['message'] }}</span>
                                <small class="text-muted" style="font-size: 11px;">{{ $notification->created_at->diffForHumans() }}</small>
                            </a>
                        </li>
                    @empty
                        <li class="text-muted small text-center py-3">Tidak ada notifikasi baru</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endif
        @endauth

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>