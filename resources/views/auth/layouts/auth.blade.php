<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light-style layout-wide customizer-hide" dir="ltr"
    data-theme="theme-default" data-assets-path="assets/" data-template="vertical-menu-template-no-customizer">

<head>
    @include('auth.layouts.header')
</head>

<body>
    @yield('content')

    @include('auth.layouts.footer')

    <script>
        function validateNumber(input) {
            input.value = input.value.replace(/\D/g, '');
        }
    </script>
</body>

</html>
