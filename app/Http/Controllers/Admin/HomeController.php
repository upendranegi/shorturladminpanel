<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        if ($user->hasRole('SuperAdmin')) {
            $scopeLabel = 'Companies';
            $scopeValue = Company::where('companies.is_deleted', 'N')->where('is_enabled', 'Y')->count();
            $userCount = User::where('users.is_deleted', 'N')->withoutRole('SuperAdmin')->count();
        } elseif ($user->hasRole('Admin')) {
            $scopeLabel = 'Company';
            $scopeValue = optional($user->company)->company_name ?: '-';
            $userCount = User::where('users.is_deleted', 'N')->where('company_id', $user->company_id)->count();
        } else {
            $scopeLabel = 'Company';
            $scopeValue = optional($user->company)->company_name ?: '-';
            $userCount = null;
        }

        $shortUrlCount = $this->visibleShortUrls($user)->count();

        return view('admin.home', compact('scopeLabel', 'scopeValue', 'userCount', 'shortUrlCount'));
    }

    public function unaccess()
    {
        return view('admin.unaccess');
    }

    private function visibleShortUrls($user)
    {
        $query = ShortUrl::where('short_urls.is_deleted', 'N');

        if ($user->hasRole('SuperAdmin')) {
            return $query;
        }

        if ($user->hasRole('Admin')) {
            return $query->where('short_urls.company_id', $user->company_id);
        }

        return $query->where('short_urls.created_by', $user->id);
    }
}
