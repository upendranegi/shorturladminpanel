@extends('layouts.admin.app')
@section('title', 'Invite User')
@section('pageTitle', 'Invite User')
@section('content')
    <div class="card">
        <div class="card-body">
            @if (auth()->user()->hasRole('SuperAdmin'))
                <p class="text-muted">Create a company and invite its Admin.</p>
            @else
                <p class="text-muted">Company: {{ $company->company_name ?? '-' }}</p>
            @endif
            <form id="userForm" method="POST" action="{{ route('admin.addInvite') }}">
                @csrf
                @if (auth()->user()->hasRole('SuperAdmin'))
                    <div class="form-group">
                        <label for="company_name">Company Name</label>
                        <input type="text" name="company_name" id="company_name" class="form-control" value="{{ old('company_name') }}" maxlength="150">
                        @error('company_name')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="company_code">Company Code</label>
                        <input type="text" name="company_code" id="company_code" class="form-control" value="{{ old('company_code') }}" maxlength="50">
                        @error('company_code')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                @endif
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name') }}" maxlength="100">
                    @error('name')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" maxlength="150">
                    @error('email')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control">
                    @error('password')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    @if (auth()->user()->hasRole('SuperAdmin'))
                        <select name="role" id="role" class="form-control">
                            <option value="Admin" {{ old('role', 'Admin') == 'Admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                    @else
                        <select name="role" id="role" class="form-control">
                            <option value="">Select Role</option>
                            <option value="Admin" {{ old('role') == 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Member" {{ old('role') == 'Member' ? 'selected' : '' }}>Member</option>
                        </select>
                    @endif
                    @error('role')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Invite</button>
                <a href="{{ route('admin.manageUser') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/userForm.js') }}"></script>
@endpush
