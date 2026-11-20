<div class="sidebar">
    <a class="brand" href="{{ route('admin.home') }}">URL Shortener</a>
    <a class="nav-link {{ request()->routeIs('admin.home') ? 'active' : '' }}" href="{{ route('admin.home') }}">Home</a>
    @can('shorturl.view')
        <a class="nav-link {{ request()->routeIs('admin.manageShortUrl') || request()->routeIs('admin.createShortUrl') || request()->routeIs('admin.editShortUrl') ? 'active' : '' }}" href="{{ route('admin.manageShortUrl') }}">Short URLs</a>
    @endcan
    @can('user.view')
        <a class="nav-link {{ request()->routeIs('admin.manageUser') || request()->routeIs('admin.inviteUser') || request()->routeIs('admin.editUser') ? 'active' : '' }}" href="{{ route('admin.manageUser') }}">Users</a>
    @endcan
</div>
