<!DOCTYPE html>
<html lang="en">
<head>
    @include('layouts.admin.header')
</head>
<body>
    @include('layouts.admin.sidebar')
    <div class="content">
        <div class="topbar">
            <h4 class="mb-0">@yield('pageTitle')</h4>
            <div>
                <span class="mr-3">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" style="display:inline-block">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Logout</button>
                </form>
            </div>
        </div>
        @include('layouts.admin.message')
        @yield('content')
    </div>
    @include('layouts.admin.js')
    @stack('scripts')
</body>
</html>
