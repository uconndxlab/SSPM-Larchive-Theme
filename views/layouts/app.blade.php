<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Larchive') }}</title>

    <!-- Bootstrap (CDN fallback kept for reliability) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Theme CSS (provided by theme package) -->
    <link rel="stylesheet" href="{{ \App\Support\Theme::asset('css/extended-bootstrap.css') }}">
    <link rel="stylesheet" href="{{ \App\Support\Theme::asset('css/styles.css') }}">

    <!-- Bootstrap icons (used by theme) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    @stack('head')
</head>
<body>
    {{-- Contributor top bar (preserves existing app behaviour) --}}
    @auth
        @if(Auth::user()->isContributor())
            <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
                <div class="container">
                    <span class="navbar-text me-3">
                        <i class="bi bi-person-circle"></i>
                        {{ Auth::user()->name }}
                        @if(Auth::user()->isAdmin())
                            <span class="badge bg-danger">Admin</span>
                        @endif
                    </span>

                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
                        <span class="navbar-toggler-icon"></span>
                    </button>

                    <div class="collapse navbar-collapse" id="adminNav">
                        <ul class="navbar-nav mx-auto">
                            @if(Auth::user()->isContributor())
                                <li class="nav-item"><a class="nav-link" href="{{ route('admin.items.workspace') }}"><i class="bi bi-briefcase"></i> Items Workspace</a></li>
                            @endif

                            @if(Auth::user()->isAdmin())
                                <li class="nav-item"><a class="nav-link" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> Users</a></li>
                                <li class="nav-item"><a class="nav-link" href="{{ route('admin.settings.theme') }}"><i class="bi bi-palette"></i> Theme Settings</a></li>
                            @endif
                        </ul>
                        <ul class="navbar-nav">
                            <li class="nav-item"><a class="nav-link" href="{{ route('profile.show') }}"><i class="bi bi-person-badge"></i> My Profile</a></li>
                            <li class="nav-item">
                                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-link nav-link"><i class="bi bi-box-arrow-right"></i> Logout</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        @endif
    @endauth

    {{-- SSPM-style header/navigation (theme-provided) --}}
    <header class="bg-white">
        <div class="d-flex justify-content-between flex-wrap align-items-center py-2 container">
            @php
                $themeInfo = \App\Support\Theme::get(\App\Support\Theme::active());
                $themeFolder = $themeInfo['folder'] ?? \App\Support\Theme::active();
                $logoCandidates = [
                    public_path("themes/{$themeFolder}/logo.png"),
                    public_path("themes/{$themeFolder}/logo.svg"),
                    public_path("themes/{$themeFolder}/assets/logo.png"),
                    public_path("themes/{$themeFolder}/assets/logo.svg"),
                ];
                $logoPath = null;
                foreach ($logoCandidates as $c) {
                    if (file_exists($c)) { $logoPath = str_replace(public_path() . '/', '', $c); break; }
                }
            @endphp

            <a class="d-flex align-items-end text-decoration-none pointer" href="{{ route('home') }}">
                @if($logoPath)
                    <img src="{{ asset($logoPath) }}" alt="{{ config('app.name') }}" style="height:56px; object-fit:contain;" />
                @else
                    <h1 class="grotesk-mono-bold letters-tight head-h1 fs-head text-grey">SING<span style="margin-left: 4px">SING</span></h1>
                    <span class="grotesk-mono-bold letters-tight head-prison fs-md text-grey ms-1">
                        <span style="letter-spacing: 2px">PRISON</span>
                        <br />
                        <span style="letter-spacing: 1px">MUSEUM</span>
                    </span>
                @endif
            </a>

            <div class="admin-info position-relative d-flex align-items-center gap-2">
                @auth
                    <span class="admin-name ms-0 ms-sm-auto grotesk-mono-reg">{{ Auth::user()->name }}</span>
                    <span class="admin-title author ms-0 me-auto ms-sm-auto me-sm-auto grotesk-mono-bold text-white rounded-1 py-1 px-2">{{ Auth::user()->isContributor() ? 'Author' : (Auth::user()->isAdmin() ? 'Admin' : 'User') }}</span>
                    <div class="rounded-2 hamburg-wrap bg-off-white d-flex justify-content-center align-items-center">
                        <i class="bi bi-list text-grey fs-1"></i>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="btn btn-link">Login</a>
                @endauth

                {{-- theme mobile menu / admin menu --- keep markup so JS in the theme can operate unchanged --}}
                <div class="hamburg-popup position-absolute bg-off-white">
                    <div class="list-unstyled hamburg-menu mb-0 w-100">
                        <a class="w-100 h-100 d-block px-5 text-grey text-decoration-none fs-md-sm text-start px-4 py-2 pointer" href="{{ route('home') }}">Home</a>
                        <a class="w-100 h-100 d-block px-5 text-grey text-decoration-none fs-md-sm text-start px-4 py-2 pointer" href="#">Exhibitions?</a>
                        @guest
                            <a class="w-100 h-100 d-block px-5 text-grey text-decoration-none fs-md-sm text-start px-4 py-2 pointer" href="{{ route('login') }}">Admin Login</a>
                        @endguest
                    </div>
                </div>
            </div>
        </div>
    </header>

    <main class="py-4">
        <div class="container">
            @include('partials.flash')
            @yield('content')
        </div>
    </main>

    {{-- Simple footer copied from theme assets (keeps look consistent) --}}
    <footer class="container-fluid bg-grey-dark py-9 ps-2 ps-md-4 px-lx-5 text-white">
        <div class="w-100 w-md-75 m-auto ps-3 ps-md-0 d-flex flex-column justify-content-between gap-4">
            <div class="d-flex align-items-end footer-title">
                <h1 class="grotesk-mono-bold letters-tight head-h1 fs-xl text-white">SING<span style="margin-left: 5px">SING</span></h1>
                <span class="grotesk-mono-reg letters-tight foot-prison fs-sm text-white ms-1">
                    <span style="letter-spacing: 2px">PRISON</span>
                    <br />
                    <span style="letter-spacing: 1px">MUSEUM</span>
                </span>
            </div>

            <div class="row">
                <div class="col-12 col-md-4 my-3 my-md-0 order-1">
                    <div>
                        <h4 class="grotesk-mono-bold fs-footer-bold mb-0">Address<br /></h4>
                        <span class="grotesk-reg fs-body">Sing Sing Prison Museum Office<br />30 State Street <br />Ossing, New York 10562</span>
                    </div>

                    <div>
                        <h4 class="grotesk-mono-bold mt-4 mt-md-0 fs-footer-bold mb-0">Telephone<br /></h4>
                        <span class="grotesk-reg fs-body">+1 914-236-5407</span>
                    </div>
                </div>

                <div class="col-12 col-md-4 my-3 my-md-0 order-4 order-md-2">
                    <div class="w-100 w-md-75 h-100 ps-md-0 mt-3 mt-md-0 m-auto d-flex flex-column justify-content-between">
                        <ul class="list-unstyled footer-ul grotesk-reg fs-body pt-3 d-flex d-md-block flex-wrap gap-4 pe-2">
                            <li><a href="#" class="footer-link">Full Exhibition</a></li>
                            <li><a href="#" class="footer-link">Religion In Incarceration Exhibition</a></li>
                            <li><a href="#" class="footer-link">About The Project</a></li>
                            <li><a href="#" class="footer-link">Donate</a></li>
                            <li><a href="#" class="footer-link">Contact</a></li>
                            <li><a href="#" class="footer-link">Full SSPM Website</a></li>
                        </ul>

                        <div class="socials d-flex justify-content-start gap-2">
                            <a href="#" class="social"><i class="footer-icon bi bi-linkedin fs-lg text-white"></i></a>
                            <a href="#" class="social"><i class="footer-icon bi bi-facebook fs-lg text-white"></i></a>
                            <a href="#" class="social"><i class="footer-icon bi bi-twitter-x fs-lg text-white"></i></a>
                            <a href="#" class="social"><i class="footer-icon bi bi-instagram fs-lg text-white"></i></a>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4 my-3 my-md-0 ps-3 ps-md-0 order-2">
                    <div class="w-100 w-md-75 m-auto d-flex flex-column justify-content-start">
                        <h4 class="grotesk-mono-bold fs-footer-bold mb-3">Newsletter Sign Up</h4>
                        <form action="#" method="post" class="d-flex flex-column">
                            <label for="email" class="grotesk-reg fs-body">Email Address</label>
                            <input type="email" id="email" name="email" class="w-75 ms-0 bg-grey-mid opacity-25 border-0 rounded-2 py-2">
                            <button class="py-3 px-4 bg-purple border-0 rounded-2 mt-4 text-white fs-4 text-decoration-none full-button">GET IN TOUCH <i class="bi bi-arrow-right-short"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Core scripts (keep HTMX/Sortable used by app) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script src="https://unpkg.com/htmx.org@1.9.10"></script>
    <script>
        document.body.addEventListener('htmx:configRequest', (event) => {
            event.detail.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').content;
        });
    </script>

    <!-- Theme scripts (loaded after core libs) -->
    <script src="{{ \App\Support\Theme::asset('js/header.js') }}"></script>
    <script src="{{ \App\Support\Theme::asset('js/main.js') }}"></script>

    @stack('scripts')
</body>
</html>
