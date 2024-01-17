<title>@yield('title') - {{ $site->app_name }}</title>
<link rel="icon" type="image/x-icon" href="{{ asset('storage') . '/' . $site->favicon }}" />
<meta name="description" content="{{ $site->meta_description }}">
<meta name="keywords" content="{{ $site->meta_keyword }}">
<meta property="og:image" content="{{ asset('storage') . '/' . $site->meta_image }}">
