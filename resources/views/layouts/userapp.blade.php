<!doctype html>
<html lang="en">
<head>
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Patient Portal')</title>

    {{-- Page-specific styles --}}
    @stack('styles')

    <style>
        .navbar-brand { font-weight: bold; }
        footer { margin-top: 50px; padding: 20px 0; background-color: #f8f9fa; }
        
        /* consistent page background */
        body { background-color: #f3fbfb; }
        
        /* header/title styles */
        .navbar.py-3 { padding-top: 4px !important; padding-bottom: 4px !important; }
        .site-header { padding: 0 0 6px; }
            .site-header .display-4 { font-weight: 700; }
        .site-tabs { margin-top: 0; margin-bottom: -20px; }
        .site-tabs .btn { border-radius: 6px; margin: 0 1px; padding: 2px 6px; font-size:0.85rem; }

        /* pull main content further up so it overlaps under the tabs slightly more */
        main.container.mt-4 { margin-top: -72px !important; }

        /* tighten default .my-5 used by pages so cards are flush under header */
        .my-5 { margin-top: 0 !important; margin-bottom: 0 !important; }

        /* On small screens restore normal flow to avoid overlap issues */
        @media (max-width: 768px) {
            main.container.mt-4 { margin-top: 0 !important; }
            .site-header .display-4 { font-size: 1.6rem; }
            .site-tabs { margin-bottom: 6px; }
        }
    </style>

    {{-- Critical inline CSS to avoid FOUC (flash of unstyled content) while full CSS loads --}}
    <style>
        /* typography & base */
        html,body { height:100%; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, system-ui, sans-serif; color: #1f2937; }
        /* tighten container padding so header content sits slightly higher
           and use a wider max-width on large screens to reduce left/right gutters */
        .container { max-width: 1280px; margin: 0 auto; padding: 8px 12px; }

        /* ensure full-width app shell has a small outer inset on very large screens */
        @media (min-width: 1400px) {
            .container { max-width: 1400px; padding-left: 18px; padding-right: 18px; }
        }

        /* header */
        .site-header { text-align:center; padding: 0 0 6px; }
        .site-header .display-4 { font-size: 2.25rem; margin-bottom: 6px; font-weight:700; }
        .site-header .lead { color: #6b7280; margin-bottom: 12px; }

          /* Floating Chat FAB (bottom-right)
              - Purpose: quick access to the chatbot page from any page
              - Styling: floating white rounded card with shadow; uses public/images/bot.svg
          */
          .chat-fab {
                position: fixed;
                right: 20px;
                bottom: 28px;
                width: 64px;
                height: 64px;
                border-radius: 16px;
                background: #fff;
                display:flex;
                align-items:center;
                justify-content:center;
                box-shadow: 0 8px 30px rgba(2,6,23,.12);
                text-decoration:none;
                z-index: 9999;
          }
        .chat-fab img { width:34px; height:34px; display:block; transition: filter 160ms ease; }
        .chat-fab {
            transition: transform 160ms ease, background-color 160ms ease, box-shadow 160ms ease;
        }
        .chat-fab:hover {
            transform: translateY(-3px);
            /* change background to requested color on hover */
            background: #4ea8de;
            box-shadow: 0 12px 34px rgba(78,168,222,0.18);
        }
        /* make the icon stand out on the colored background (invert if needed) */
        .chat-fab:hover img { filter: brightness(0) invert(1); }
        .chat-fab:focus-visible { outline: 3px solid rgba(78,168,222,0.28); outline-offset: 3px; }
          @media (max-width:768px) { .chat-fab { right:14px; bottom:18px; width:56px; height:56px; } .chat-fab img { width:28px; height:28px; } }

        /* tabs (minimal) */
        .site-tabs .btn { padding: 8px 14px; border-radius: 8px; border: 1px solid #e6eef0; background: #ffffff; color:#374151; font-weight:500; text-decoration:none; }
        .site-tabs .btn + .btn { margin-left: 8px; }
        .site-tabs .btn.btn-primary { background: #3b82f6; color: #fff; border-color: transparent; }
        .site-tabs .btn.btn-outline-secondary { background: #fff; color:#374151; border-color:#e6eef0; }

        /* basic card panel to look consistent before full CSS loads */
        .bg-white { background: #ffffff; }
        .rounded-4 { border-radius: 12px; }
        .shadow-sm { box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
        .p-4 { padding: 18px; }
        .my-5 { margin-top: 40px; margin-bottom: 40px; }
        .card-header { padding: 18px; border-bottom: 1px solid #eef7f7; }
        .card-title { font-size:1.15rem; font-weight:600; }
        .card-description { color:#6b7280; font-size:0.9rem; }

        /* make empty state centered and softer */
        #waitingQueue, #inProgressQueue { min-height: 80px; padding: 14px; color:#374151; }

        /* basic controls so forms don't look raw before CSS loads */
        .btn, button { display:inline-block; padding:8px 12px; border-radius:8px; border:1px solid #d1d5db; background:#ffffff; color:#0f172a; cursor:pointer; }
        .btn-primary, button.btn-primary { background:#3b82f6; color:#fff; border-color:transparent; }
        .form-input, input[type="text"], input[type="number"], input[type="tel"], textarea { padding:8px 10px; border:1px solid #e6eef0; border-radius:8px; width:100%; box-sizing:border-box; }
        .form-select, select { padding:8px 10px; border:1px solid #e6eef0; border-radius:8px; background:#fff; }
        table { border-collapse:collapse; width:100%; }
        .table th, .table td { padding:10px 12px; border-bottom:1px solid #eef7f7; }

        /* Priority badges for queue items */
        .priority-badge { display:inline-block; padding:4px 8px; border-radius:8px; font-size:0.8rem; color:#fff; }
        .priority-urgent { background:#ef4444; } /* red */
        .priority-regular { background:#6b7280; } /* gray */
        .priority-high { background:#f59e0b; } /* amber for other priorities */

        /* ensure fallback animations exist */
        @keyframes enterFromBelowFallback { from { opacity:0; transform:translateY(8px);} to { opacity:1; transform:translateY(0);} }
        .animate-in { animation: enterFromBelowFallback 220ms cubic-bezier(.0,.0,.2,1) both; }

        @media (prefers-reduced-motion: reduce) { .animate-in { animation:none !important; } }
    </style>
    
    {{-- Minimal inline animation & card styles as a fallback when Vite isn't running --}}
    <style>
        @keyframes enterFromBelowFallback {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-in {
            animation: enterFromBelowFallback 220ms cubic-bezier(0.0, 0.0, 0.2, 1) both;
        }

        /* Basic card transition & hover for fallback */
        .card {
            transition: transform 0.12s ease, box-shadow 0.12s ease;
        }
        .card:hover { transform: translateY(-4px); box-shadow: 0 6px 20px rgba(15,23,42,0.06); }

        @media (prefers-reduced-motion: reduce) {
            .animate-in { animation: none !important; }
            .card { transition: none !important; }
        }
    </style>

    @php
        $manifestPath = public_path('build/manifest.json');
        $preloadCss = null;
        if (file_exists($manifestPath)) {
            try {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if (!empty($manifest['resources/js/app.js']['css'][0])) {
                    $preloadCss = 'build/' . ltrim($manifest['resources/js/app.js']['css'][0], '/');
                }
            } catch (Throwable $e) {
                $preloadCss = null;
            }
        }
        $preloadCss = $preloadCss ?? 'build/assets/app.css';
    @endphp

    <link rel="stylesheet" href="{{ asset($preloadCss) }}">
    @vite(['resources/js/app.js'])
</head>
<body>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-3">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">Hospital Queue</a>

            <!-- Authentication Links -->
            <ul class="navbar-nav ms-auto">
                @guest
                    @if (Route::has('login'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    @endif
                    @if (Route::has('register'))
                        <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Register</a></li>
                    @endif
                @else
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
                            </li>
                        </ul>
                    </li>
                @endguest
            </ul>
        </div>
    </nav>

    <!-- Header with centered title and tabs (user-focused) -->
    <header class="site-header text-center">
        <div class="container">
            <h1 class="display-4">Hospital Queue</h1>
            <p class="lead text-muted">Patient Portal — queue and personal history</p>

            {{-- User tabs: Queue and History only --}}
            <div class="d-flex justify-content-center site-tabs">
                <div class="btn-group" role="group" aria-label="User Tabs">
                    <a href="{{ route('user.dashboard') }}" class="btn {{ request()->routeIs('user.dashboard') && request()->get('tab') !== 'history' ? 'btn-primary' : 'btn-outline-secondary' }}">Queue</a>
                    <a href="{{ route('user.dashboard', ['tab' => 'history']) }}" class="btn {{ request()->get('tab') === 'history' ? 'btn-primary' : 'btn-outline-secondary' }}">History</a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main content -->
    <main class="container mt-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer>
        <div class="container text-center">
            <p>&copy; {{ date('Y') }} Health Queue Management System. All rights reserved.</p>
        </div>
    </footer>

    @stack('scripts')
    @auth
    <a href="{{ route('botman.chat') }}" class="chat-fab" title="Open Chatbot" aria-label="Open Chatbot">
        <img src="{{ asset('images/chatboticon.png') }}" alt="Chatbot">
    </a>
    @endauth

</body>
</html>
