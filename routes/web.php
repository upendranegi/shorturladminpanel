<?php

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\ShortUrlController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/admin/home');
    }

    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->prefix('admin')->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('admin.home');
    Route::get('/unaccess', [HomeController::class, 'unaccess'])->name('admin.unaccess');

    Route::get('/short-urls', [ShortUrlController::class, 'manageShortUrl'])->name('admin.manageShortUrl');
    Route::get('/short-urls/list', [ShortUrlController::class, 'allShortUrls'])->name('admin.allShortUrls');
    Route::get('/short-urls/create', [ShortUrlController::class, 'createShortUrl'])->name('admin.createShortUrl');
    Route::post('/short-urls', [ShortUrlController::class, 'addShortUrl'])->name('admin.addShortUrl');
    Route::get('/short-urls/{id}/edit', [ShortUrlController::class, 'editShortUrl'])->name('admin.editShortUrl');
    Route::post('/short-urls/{id}', [ShortUrlController::class, 'updateShortUrl'])->name('admin.updateShortUrl');
    Route::post('/short-urls/{id}/delete', [ShortUrlController::class, 'deleteShortUrl'])->name('admin.deleteShortUrl');
    Route::post('/short-urls/{id}/status', [ShortUrlController::class, 'statusShortUrl'])->name('admin.statusShortUrl');

    Route::get('/users', [UserController::class, 'manageUser'])->name('admin.manageUser');
    Route::get('/users/list', [UserController::class, 'allUsers'])->name('admin.allUsers');
    Route::get('/users/invite', [UserController::class, 'inviteUser'])->name('admin.inviteUser');
    Route::post('/users/invite', [UserController::class, 'addInvite'])->name('admin.addInvite');
    Route::get('/users/{id}/edit', [UserController::class, 'editUser'])->name('admin.editUser');
    Route::post('/users/{id}', [UserController::class, 'updateUser'])->name('admin.updateUser');
    Route::post('/users/{id}/delete', [UserController::class, 'deleteUser'])->name('admin.deleteUser');
    Route::post('/users/{id}/status', [UserController::class, 'statusUser'])->name('admin.statusUser');
});


