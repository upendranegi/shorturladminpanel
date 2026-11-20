@extends('layouts.admin.app')
@section('title', 'Edit User')
@section('pageTitle', 'Edit User')
@section('content')
    <div class="card">
        <div class="card-body">
            <p class="text-muted">Company: {{ $user->company->company_name ?? '-' }}</p>
            <form id="userForm" method="POST" action="{{ route('admin.updateUser', $user->id) }}">
                @csrf
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $user->name) }}" maxlength="100">
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $user->email) }}" maxlength="150">
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <input type="text" id="role_label" class="form-control" value="{{ $roleName }}" readonly>
                    <input type="hidden" name="role" id="role" value="{{ old('role', $roleName) }}">
                    @error('role')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.manageUser') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/userForm.js') }}"></script>
@endpush
