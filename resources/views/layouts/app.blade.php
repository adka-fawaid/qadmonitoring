<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'QAD Monitoring')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="min-h-screen bg-[#111516] text-slate-200">
    <div class="app-shell min-h-screen">
        @include('layouts.sidebar')

        <div class="app-content min-w-0">
            @include('layouts.navbar')
            <main class="min-w-0 p-4 sm:p-6">
                @yield('content')
            </main>
        </div>
    </div>



    <div id="sidebarBackdrop" class="fixed inset-0 z-30 hidden bg-black/60 lg:hidden"></div>
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>