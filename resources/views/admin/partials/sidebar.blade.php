<div id="sidebar" class="sidebar active">


    <div class="sidebar-wrapper">


        <!-- Header -->
        <div class="sidebar-header">

            <div class="d-flex justify-content-between align-items-center">

                <!-- Logo -->
                <div class="logo">
                    <img style="width:50px; height:50px;" src="{{ asset('images/logo.webp') }}">
                    <span class="brand-text">IKAK</span>
                </div>

                <!-- Mobile Close Button -->
                <button class="btn btn-sm btn-light d-md-none sidebar-toggle" type="button" aria-label="Close sidebar">
                    <i class="bi bi-x-lg"></i>
                </button>

                <!-- Desktop Toggle Button -->
                <button class="btn btn-sm btn-info d-none d-md-inline-flex sidebar-toggle-desktop" type="button"
                    aria-label="Toggle sidebar collapse">
                    <i class="bi bi-list"></i>
                </button>


            </div>

        </div>

        <div class="sidebar-logo-separator"></div>


        <!-- Menu -->
        <div class="sidebar-menu">

            <ul class="menu">

                <li class="sidebar-title">Menu</li>


                @if (session('logged_in'))

                    <li class="sidebar-item {{ request()->is('home') ? 'active' : '' }}">
                        <a href="{{ url('/home') }}" class="sidebar-link">
                            <i class="bi bi-house-door"></i>
                            <span>Home</span>
                        </a>
                    </li>

                    @if (strtolower(session('user_role', '')) === 'operator')
                        <li class="sidebar-item {{ request()->is('admin/dashboard*') ? 'active' : '' }}">
                            <a href="{{ route('admin.dashboard') }}" class="sidebar-link">
                                <i class="bi bi-grid-fill"></i>
                                <span>Dashboard</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('admin/menus*') ? 'active' : '' }}">
                            <a href="{{ route('admin.menus') }}" class="sidebar-link">
                                <i class="bi bi-menu-button-wide"></i>
                                <span>Create Menu</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('admin/categories*') ? 'active' : '' }}">
                            <a href="{{ route('admin.categories') }}" class="sidebar-link">
                                <i class="bi bi-tags"></i>
                                <span>Category</span>
                            </a>
                        </li>

                        <li class="sidebar-item {{ request()->is('admin/users*') ? 'active' : '' }}">
                            <a href="{{ route('admin.users') }}" class="sidebar-link">
                                <i class="bi bi-people"></i>
                                <span>Manage User</span>
                            </a>
                        </li>
                    @endif

                @endif
            </ul>

        </div>

    </div>

</div>
