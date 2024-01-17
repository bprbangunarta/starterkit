<title>@yield('title') - {{ $site->app_name }}</title>
<meta name="description" content="{{ $site->meta_description }}">
<meta name="keywords" content="{{ $site->meta_keyword }}">
<meta property="og:image" content="{{ asset('storage') . '/' . $site->meta_image }}">
@if (is_null($site->favicon))
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
    </svg>
@else
    <link rel="icon" type="image/x-icon" href="{{ asset('storage') . '/' . $site->favicon }}" />
@endif
