<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="/" class="app-brand-link">
            <span class="app-brand-logo demo">
                <x-app-logo></x-app-logo>
            </span>
            <span class="app-brand-text text-primary demo menu-text fw-bold"><x-app-name></x-app-name></span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
            <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item {{ Request::is('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons ti ti-smart-home"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('user/account', 'user/changer/password') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-user"></i>
                <div>User Setting</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('user/account') ? 'active' : '' }}">
                    <a href="{{ route('user.account') }}" class="menu-link">
                        <div>Account</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('user/changer/password') ? 'active' : '' }}">
                    <a href="{{ route('user.password') }}" class="menu-link">
                        <div>Password</div>
                    </a>
                </li>
            </ul>
        </li>

        @can('Administrator')
            <li class="menu-header small text-uppercase">
                <span class="menu-header-text">Administrator</span>
            </li>

            <li class="menu-item {{ Request::is('admin/site') ? 'active' : '' }}">
                <a href="{{ route('site.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-world"></i>
                    <div>Site Configuration</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('admin/user') ? 'active' : '' }}">
                <a href="{{ route('admin.user.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-users"></i>
                    <div>Users Management</div>
                </a>
            </li>

            <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-settings"></i>
                    <div>Roles &amp; Permissions</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Roles</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Permission</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Assign Permission</div>
                        </a>
                    </li>
                </ul>
            </li>

            {{-- <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-list"></i>
                <div>Menu Management</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>List Menu</div>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>List Submenu</div>
                    </a>
                </li>
            </ul>
        </li> --}}
        @endcan
    </ul>
</aside>
