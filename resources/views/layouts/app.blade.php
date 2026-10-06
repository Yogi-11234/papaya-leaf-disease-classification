<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PapayaLeafCNN') — Deteksi Penyakit Daun Pepaya</title>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <!-- FontAwesome for Premium Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <!-- Sticky Glassmorphism Header -->
    <header>
        <div class="header-inner">
            <a href="{{ route('dashboard') }}" class="logo-container">
                <span class="logo-icon"><i class="fa-solid fa-leaf"></i></span>
                <span class="logo-text">PapayaLeaf<span style="color: var(--color-primary);">CNN</span></span>
            </a>

            <nav>
                <ul class="nav-links">
                    <li>
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="fa-solid fa-chart-line"></i> Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('riwayat.index') }}" class="nav-link {{ request()->routeIs('riwayat.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-history"></i> Riwayat
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('klasifikasi.index') }}" class="nav-link nav-btn {{ request()->routeIs('klasifikasi.index') ? 'active' : '' }}">
                            <i class="fa-solid fa-cloud-arrow-up"></i> Mulai Deteksi
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Container -->
    <main class="app-container">
        <!-- Toast Notification Area -->
        @if (session('success'))
            <div class="glass-card mb-2 primary-edge" style="padding: 1rem 1.5rem; display: flex; align-items: center; gap: 0.75rem; background: rgba(16, 185, 129, 0.08); border-color: var(--color-primary);">
                <i class="fa-solid fa-circle-check" style="color: var(--color-success); font-size: 1.25rem;"></i>
                <div>
                    <span style="font-weight: 600; color: white;">Berhasil!</span>
                    <p style="font-size: 0.9rem; color: var(--color-text-muted);">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="glass-card mb-2" style="padding: 1rem 1.5rem; display: flex; align-items: center; gap: 0.75rem; background: rgba(248, 113, 113, 0.08); border-color: var(--color-danger); border-top: 4px solid var(--color-danger);">
                <i class="fa-solid fa-circle-exclamation" style="color: var(--color-danger); font-size: 1.25rem;"></i>
                <div>
                    <span style="font-weight: 600; color: white;">Terjadi Kesalahan!</span>
                    <p style="font-size: 0.9rem; color: var(--color-text-muted);">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <footer style="margin-top: 5rem; padding: 2rem 0; border-top: 1px solid var(--color-border); text-align: center; color: var(--color-text-muted); font-size: 0.85rem;">
        <div class="app-container" style="padding: 0;">
            <p>© {{ date('Y') }} PapayaLeafCNN. Skripsi S1 Teknik Informatika Yogi Aditia 22110593. </p>
        </div>
    </footer>

    @yield('scripts')
</body>
</html>
