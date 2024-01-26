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
        @hasanyrole(Auth::user()->roles->pluck('name')[0] ?? '')
            <li class="menu-item {{ Request::is('dashboard') ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons ti ti-smart-home"></i>
                    <div>Dashboard</div>
                </a>
            </li>
        @endhasanyrole

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

            <li
                class="menu-item {{ Request::is('admin/role', 'admin/permission', 'admin/permission/sync/*/edit') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-lock"></i>
                    <div>Roles &amp; Permissions</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Request::is('admin/role', 'admin/permission/sync/*/edit') ? 'active' : '' }}">
                        <a href="{{ route('admin.role.index') }}"
                            class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Roles</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('admin/permission') ? 'active' : '' }}"">
                        <a href="{{ route('admin.permission.index') }}"
                            class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Permission</div>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons ti ti-database"></i>
                    <div>Master Data Kredit</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Data Survey</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Data Usulan</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Data Jaminan</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Data Tracking</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Data Nasabah</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Data Pengajuan</div>
                        </a>
                    </li>
                    <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                        <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <div>Data Pendamping</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endcan

        <li class="menu-header small text-uppercase">
            <span class="menu-header-text">Main Menu</span>
        </li>
        <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-users"></i>
                <div>Pengajuan Kredit</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Add Pengajuan</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>List Pengajuan</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Otor Pengajuan</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-report-analytics"></i>
                <div>Analisa Kredit</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Jadwal Survey</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Input Analisa</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Input Persetujuan</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Reverse Persetujuan</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-ambulance"></i>
                <div>Rescue Kredit</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Jadwal Survey</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Input Analisa</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Input Persetujuan</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Reverse Persetujuan</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-notification"></i>
                <div>Notifikasi Kredit</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Add Notifikasi</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>List Notifikasi</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-file-description"></i>
                <div>Perjanjian Kredit</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Add Perjanjian</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>List Perjanjian</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Otor Perjanjian</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons ti ti-moneybag"></i>
                <div>Realisasi Kredit</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons ti ti-report-off"></i>
                <div>Penolakan Kredit</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
            <a href="#" class="menu-link">
                <i class="menu-icon tf-icons ti ti-checklist"></i>
                <div>Pemberkasan</div>
            </a>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-printer"></i>
                <div>Cetak Berkas</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Pengajuan Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Analisa Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Rescue Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Notifikasi Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Perjanjian Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Penolakan Kredit</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-report"></i>
                <div>Laporan Kredit</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Pengajuan Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Analisa Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Rescue Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Notifikasi Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Perjanjian Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Realisasi Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Penolakan Kredit</div>
                    </a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ Request::is('') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons ti ti-tools"></i>
                <div>Checking Tool</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Tracking Pengajuan</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Pembukaan CIF</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Droping Agunan</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Droping Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Pengecekan CIF</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Simulasi Kredit</div>
                    </a>
                </li>
                <li class="menu-item {{ Request::is('') ? 'active' : '' }}">
                    <a href="#" class="menu-link {{ Request::is('dashboard') ? 'active' : '' }}">
                        <div>Simulasi Asuransi</div>
                    </a>
                </li>
            </ul>
        </li>

    </ul>
</aside>
