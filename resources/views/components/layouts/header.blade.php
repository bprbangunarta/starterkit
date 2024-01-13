<title>@yield('title') - {{ $site->app_name }}</title>
<meta name="description" content="{{ $site->meta_description }}">
<meta name="keywords" content="{{ $site->meta_keyword }}">
<meta property="og:image" content="{{ $site->meta_image }}">
<link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />
{{-- <link rel="icon" type="image/x-icon" href="{{ $site->favicon }}" /> --}}
