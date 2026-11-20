@extends('layouts.admin.app')
@section('title', 'Home')
@section('pageTitle', 'Home')
@section('content')
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted">{{ $scopeLabel }}</div>
                    <h3>{{ $scopeValue }}</h3>
                </div>
            </div>
        </div>
        @if (!is_null($userCount))
            <div class="col-md-4 mb-3">
                <div class="card stat-card">
                    <div class="card-body">
                        <div class="text-muted">Users</div>
                        <h3>{{ $userCount }}</h3>
                    </div>
                </div>
            </div>
        @endif
        <div class="col-md-4 mb-3">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="text-muted">Short URLs you can see</div>
                    <h3>{{ $shortUrlCount }}</h3>
                </div>
            </div>
        </div>
    </div>
@endsection
