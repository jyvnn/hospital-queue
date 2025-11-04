<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Hospital Queue Management System')</title>

    {{-- Assets are bundled via Vite. Vendor CSS (bootstrap, fontawesome) and project CSS
        are imported in resources/js/app.js and injected by @vite below. --}}

    <!-- Page-specific styles -->
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
    {{--
        Load built stylesheet directly as a fallback to avoid FOUC when Vite dev server is not
        running or HMR injects styles late. This preloads the CSS and switches rel to
        stylesheet on load; noscript provides a fallback for no-JS environments.
        Ensure you run the Vite build (npm run build) so the file exists at the path below in production.
    --}}
    @php
        // Try to read Vite manifest to find the built CSS file for the app entry.
        $manifestPath = public_path('build/manifest.json');
        $preloadCss = null;
        if (file_exists($manifestPath)) {
            try {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if (!empty($manifest['resources/js/app.js']['css'][0])) {
                    $preloadCss = 'build/' . ltrim($manifest['resources/js/app.js']['css'][0], '/');
                } elseif (!empty($manifest['resources/sass/app.scss']['file'])) {
                    // fallback: some projects emit a separate CSS entry from scss
                    $preloadCss = 'build/' . ltrim($manifest['resources/sass/app.scss']['file'], '/');
                }
            } catch (
                Throwable $e
            ) {
                $preloadCss = null;
            }
        }
        // final fallback if manifest not readable
        $preloadCss = $preloadCss ?? 'build/assets/app.css';
    @endphp

    {{-- If we have a built CSS file, load it as a blocking stylesheet so the browser
        applies full styles before painting (prevents FOUC). Using a blocking
        stylesheet is acceptable because the file is cached and small in prod. --}}
    <link rel="stylesheet" href="{{ asset($preloadCss) }}">

    {{-- Vite entry: imports CSS/js from resources/js/app.js (bundles project styles). Placed after critical
         inline CSS so the critical rules apply immediately and prevent FOUC while dev HMR injects styles. --}}
    @vite(['resources/js/app.js'])
</head>
<body>

        <!-- Navigation (responsive, based on professor template) -->
        {{--
                LAYOUT SECTIONS / PURPOSE
                -------------------------
                - Navigation: top-left brand and authentication links (Login/Register or user menu).
                - Header: centered site title and lead description.
                - Site tabs: quick links to application pages (Queue Management, Patient Registration, Doctors, History, Reports).
                    These tabs link to routes that may require authentication; by default we show them only to
                    authenticated users to avoid exposing management controls to guests. To change behavior,
                    edit the @auth / @endauth block around the .site-tabs element below.
                - Main content: `@yield('content')` — page-specific content lives here (cards, forms, lists).
                - Footer: small copyright / metadata area.
        --}}
    <nav class="navbar navbar-expand-lg navbar-light bg-transparent py-3">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">Hospital Queue</a>

            <!-- Authentication Links (upper-right) -->
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

    <!-- Header with centered title and tabs -->
    <header class="site-header text-center">
        <div class="container">
            <h1 class="display-4">Hospital Queue Management System</h1>
            <p class="lead text-muted">Efficient patient flow management for healthcare facilities</p>

            {{-- Site tabs: visible to authenticated users only. These links navigate to app sections.
                 If you want tabs visible to guests, remove the @auth / @endauth wrapper. --}}
            @auth
            <div class="d-flex justify-content-center site-tabs">
                <div class="btn-group" role="group" aria-label="Tabs">
                    <a href="{{ route('home') }}" class="btn {{ request()->routeIs('home') ? 'btn-primary' : 'btn-outline-secondary' }}">Queue Management</a>
                    <a href="{{ route('registration') }}" class="btn {{ request()->routeIs('registration') ? 'btn-primary' : 'btn-outline-secondary' }}">Patient Registration</a>
                    <a href="{{ route('doctors') }}" class="btn {{ request()->routeIs('doctors') ? 'btn-primary' : 'btn-outline-secondary' }}">Doctors</a>
                    <a href="{{ route('history') }}" class="btn {{ request()->routeIs('history') ? 'btn-primary' : 'btn-outline-secondary' }}">Patient History</a>
                    <a href="{{ route('reports') }}" class="btn {{ request()->routeIs('reports') ? 'btn-primary' : 'btn-outline-secondary' }}">Reports & Analytics</a>
                </div>
            </div>
            @endauth
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

    <!-- Core JS: bundled by Vite (imported in resources/js/app.js) -->
    {{-- Vite will inject the JS bundle here via @vite in the head. --}}

    <!-- Page-specific scripts -->
    @stack('scripts')
    {{-- Floating chat FAB: visible to authenticated users only (links to the chat UI) --}}
    @auth
    <a href="{{ route('botman.chat') }}" class="chat-fab" title="Open Chatbot" aria-label="Open Chatbot">
        <img src="{{ asset('images/chatboticon.png') }}" alt="Chatbot">
    </a>
    @endauth
    {{-- Animations are provided via CSS classes. JS stagger removed; add `animate-in` class server-side where desired. --}}
</body>
</html>