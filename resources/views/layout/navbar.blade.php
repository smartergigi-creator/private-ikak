<nav class="navbar navbar-expand-lg nova-navbar sticky-top">
    @php
        // Get user from custom session (stdClass from API)
        $navbarUser = (object) session('ikak_user', []);

        // Determine role
        $userRole = $navbarUser->role ?? ($navbarUser->roles[0] ?? 'Guest');

        $navbarUserLabel = null; // optional role badge

        $navbarUserName = trim((string) ($navbarUser->name ?? ($navbarUser->email ?? 'User')));
        $navbarUserDisplay = strtok($navbarUserName, ' ') ?: 'User';

        $defaultPhoto = asset('admin/dist/assets/images/logo/userlogo.webp');
        $navbarUserPhoto = !empty($navbarUser->profile_photo) ? asset($navbarUser->profile_photo) : $defaultPhoto;

        $homeUrl = url('/home');

        // Get all direct children of Karate eBooks category (parent_id = 4)
        $karateSubcategories = \App\Models\Category::where('parent_id', 4)->orderBy('name')->get();

        // Build the menu item with its children
        $navbarMenus = collect([
            (object) [
                'name' => 'Karate eBooks',
                'slug' => 'karate-ebooks',
                'url' => '/karate-ebooks', // the "All Karate eBooks" landing page
                'children' => $karateSubcategories,
            ],
        ]);

        // Helper for active state
        $menuHref = function ($menu) {
            return url($menu->url);
        };
        $menuIsActive = function ($menu) {
            $path = trim(parse_url($menu->url, PHP_URL_PATH), '/');
            return request()->is($path);
        };
    @endphp

    <style>
        .navbar-user-photo {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #d9f3fb;
            background: #f3fcfe;
            flex: 0 0 auto;
        }

        .navbar-user-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.34rem 0.78rem;
            border-radius: 999px;
            background: linear-gradient(135deg, #5fd45f 0%, #1da851 55%, #0f8d4a 100%);
            color: #fff;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.01em;
            white-space: nowrap;
            box-shadow: 0 10px 22px rgba(29, 168, 81, 0.24);
            border: 1px solid rgba(255, 255, 255, 0.28);
        }

        .navbar-role-note {
            padding: 0.8rem 1rem 0.45rem;
        }

        .navbar-user-name {
            display: inline-block;
            max-width: 84px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
            font-weight: 700;
        }

        .login-btn {
            display: inline-block;
            padding: 6px 14px;
            /* Height and width reduced */
            background: #A6633C;
            color: #f9f5f5;
            text-decoration: none;
            font-size: 14px;
            /* Smaller text */
            font-weight: 600;
            line-height: 1.2;
            border-radius: 6px;
            border: 2px solid #A6633C;
            transition: all .3s ease;
        }

        .login-btn:hover {
            background: #e6e2bf;
            color: #0f0f0e;
        }



        /* Role text style */
        .navbar-role {
            color: #fff;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
        }

        /* Make sure the right-side block aligns well */
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-shrink: 0;
        }

        /* Offcanvas toggle should be visible only on small screens */
        .navbar-toggler {
            border: none;
            padding: 0.25rem 0.5rem;
        }

        .navbar-toggler:focus {
            box-shadow: none;
        }

        .navbar-role {
            color: #1c2e3a;
            font-weight: 600;
            font-size: 0.9rem;
            white-space: nowrap;
        }
    </style>

    <div class="container d-flex align-items-center">
        <!-- Logo (left) -->
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="{{ asset('images/logo.webp') }}" width="50" alt="">
            <span class="brand-text ms-2">IKAK</span>
        </a>

        <!-- Desktop Menu (centered, hidden on mobile) -->
        <div class="collapse navbar-collapse d-none d-lg-flex justify-content-center" id="navbarNav">
            <ul class="navbar-nav">
                @foreach ($navbarMenus as $navbarMenu)
                    @php $children = $navbarMenu->children ?? collect(); @endphp

                    @if ($children->isNotEmpty())
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ $menuIsActive($navbarMenu) ? 'active' : '' }}"
                                href="{{ $menuHref($navbarMenu) }}" role="button" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                {{ $navbarMenu->name }}
                            </a>
                            <ul class="dropdown-menu">
                                @foreach ($children as $child)
                                    <li>
                                        <a class="dropdown-item"
                                            href="{{ $homeUrl }}?category={{ $child->id }}#ebooksSection">
                                            {{ $child->name }}
                                        </a>
                                    </li>
                                @endforeach
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item fw-bold" href="{{ url('/karate-ebooks') }}">
                                        📚 All Karate eBooks
                                    </a>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link {{ $menuIsActive($navbarMenu) ? 'active' : '' }}"
                                href="{{ $menuHref($navbarMenu) }}">
                                {{ $navbarMenu->name }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>

        <!-- Right side: role + login/avatar + mobile toggler -->
        <div class="navbar-right ms-auto">
            <!-- Role badge (always visible) -->
            <span class="navbar-role">
                Welcome back, {{ $userRole }}
            </span>

            @if (session()->has('logged_in'))
                <!-- Logged in: avatar dropdown -->
                <div class="dropdown">
                    <button class="btn user-menu-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown"
                        title="{{ $navbarUserName }}">
                        <img src="{{ $navbarUserPhoto }}" alt="{{ $navbarUserName }}" class="navbar-user-photo">
                        <span class="navbar-user-name">{{ $navbarUserDisplay }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        @if ($navbarUserLabel)
                            <li class="navbar-role-note">
                                <span class="navbar-user-badge">{{ $navbarUserLabel }}</span>
                            </li>
                        @endif
                        @if (in_array($userRole, ['Branch Chief', 'Operator']))
                            <li><a href="{{ route('admin.dashboard') }}" class="dropdown-item">Dashboard</a></li>
                        @endif
                        <li><a href="{{ url('/home') }}" class="dropdown-item">Home</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <!-- Guest: login button -->
                <a href="/login" class="login-btn">Login</a>
            @endif

            <!-- Mobile offcanvas toggle (visible only on small screens) -->
            <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="offcanvas"
                data-bs-target="#mobileMenu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </div>
</nav>

<!-- Mobile Offcanvas Menu (unchanged) -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fw-semibold">Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <ul class="navbar-nav mobile-menu-list">
            @foreach ($navbarMenus as $menu)
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        {{ $menu->name }}
                    </a>
                    <ul class="dropdown-menu">
                        @foreach ($menu->children as $child)
                            <li><a class="dropdown-item"
                                    href="{{ route('ebooks.category', $child->id) }}">{{ $child->name }}</a></li>
                        @endforeach
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li><a class="dropdown-item fw-bold" href="{{ route('ebooks.category', 4) }}">📚 All Karate
                                eBooks</a></li>
                    </ul>
                </li>
            @endforeach
        </ul>
    </div>
</div>
