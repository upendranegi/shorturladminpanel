@extends('layouts.admin.app')
@section('title', 'Edit Short URL')
@section('pageTitle', 'Edit Short URL')
@section('content')
    <div class="card">
        <div class="card-body">
            <form id="shortUrlForm" method="POST" action="{{ route('admin.updateShortUrl', $shortUrl->id) }}">
                @csrf
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" class="form-control" value="{{ old('title', $shortUrl->title) }}" maxlength="100">
                    @error('title')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="original_url">Original URL</label>
                    <input type="text" name="original_url" id="original_url" class="form-control" value="{{ old('original_url', $shortUrl->original_url) }}">
                    @error('original_url')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('admin.manageShortUrl') }}" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="{{ asset('assets/js/shortUrlForm.js') }}"></script>
@endpush
