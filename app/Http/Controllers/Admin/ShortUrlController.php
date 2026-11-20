<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShortUrl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ShortUrlController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:shorturl.view')->only(['manageShortUrl', 'allShortUrls']);
        $this->middleware('permission:shorturl.create')->only(['createShortUrl', 'addShortUrl']);
        $this->middleware('permission:shorturl.edit')->only(['editShortUrl', 'updateShortUrl']);
        $this->middleware('permission:shorturl.delete')->only(['deleteShortUrl']);
        $this->middleware('permission:shorturl.status')->only(['statusShortUrl']);
    }

    public function manageShortUrl()
    {
        return view('admin.shorturl.manageShortUrl');
    }

    public function allShortUrls(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $user = Auth::user();
            $query = ShortUrl::query()
                ->select('short_urls.*', 'users.name as creator_name', 'companies.company_name')
                ->leftJoin('users', 'short_urls.created_by', '=', 'users.id')
                ->leftJoin('companies', 'short_urls.company_id', '=', 'companies.id')
                ->where('short_urls.is_deleted', 'N');

            if ($user->hasRole('SuperAdmin')) {
                // SuperAdmin sees short URLs from every company.
            } elseif ($user->hasRole('Admin')) {
                $query->where('short_urls.company_id', $user->company_id);
            } else {
                $query->where('short_urls.created_by', $user->id);
            }

            if ($request->status === 'Y' || $request->status === 'N') {
                $query->where('short_urls.is_enabled', $request->status);
            }

            return datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('Title', function ($row) {
                    $col = $row->title ? ucfirst($row->title) : '-';

                    return e($col);
                })
                ->addColumn('Original', function ($row) {
                    return e($row->original_url);
                })
                ->addColumn('Creator', function ($row) {
                    return e($row->creator_name ?: '-');
                })
                ->addColumn('Company', function ($row) {
                    return e($row->company_name ?: '-');
                })
                ->addColumn('action', function ($row) {
                    $actions = '';
                    $owner = $row->created_by == Auth::id();

                    if ($owner && Auth::user()->hasPermissionTo('shorturl.status')) {
                        $statusUrl = route('admin.statusShortUrl', $row->id);
                        $class = 'btn-danger';
                        $text = 'Disable';
                        if ($row->is_enabled == 'N') {
                            $class = 'btn-success';
                            $text = 'Enable';
                        }
                        $actions .= '<form method="POST" action="' . $statusUrl . '" style="display:inline-block">'
                            . csrf_field()
                            . '<button type="submit" class="btn ' . $class . ' btn-sm mb-1 mx-1">' . $text . '</button></form>';
                    }

                    if ($owner && Auth::user()->hasPermissionTo('shorturl.edit')) {
                        $url = route('admin.editShortUrl', $row->id);
                        $actions .= '<a href="' . $url . '"><button type="button" class="btn btn-secondary btn-sm mb-1 mx-1 edit-button">Edit</button></a>';
                    }

                    if ($owner && Auth::user()->hasPermissionTo('shorturl.delete')) {
                        $actions .= '<a class="btn btn-danger btn-sm mb-1 mx-1 btn-delete" href="javascript:void(0);" data-id="' . $row->id . '" data-toggle="modal" data-target="#deleteModal">Delete</a>';
                    }

                    return $actions;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        return view('admin.shorturl.manageShortUrl');
    }

    public function createShortUrl()
    {
        if (!ShortUrl::userCanCreate(Auth::user())) {
            return redirect('/admin/unaccess');
        }

        return view('admin.shorturl.addShortUrl');
    }

    public function addShortUrl(Request $request)
    {
        if (!ShortUrl::userCanCreate(Auth::user())) {
            return redirect('/admin/unaccess');
        }

        $request->validate(
            [
                'title' => ['nullable', 'string', 'max:100'],
                'original_url' => ['required', 'url', 'max:2048'],
            ],
            [
                'title.string' => 'The Title must be a string.',
                'title.max' => 'The Title must not be greater than 100 characters.',
                'original_url.required' => 'The Original URL field is required.',
                'original_url.url' => 'The Original URL must be a valid URL.',
                'original_url.max' => 'The Original URL must not be greater than 2048 characters.',
            ]
        );

        $nameExist = ShortUrl::where('created_by', Auth::id())
            ->where('original_url', $request->original_url)
            ->where('is_deleted', 'N')
            ->first();

        if ($nameExist) {
            return redirect()->back()->withInput()->with('errorMessage', 'This URL already exists.');
        }

        try {
            $shortUrl = new ShortUrl();
            $shortUrl->company_id = Auth::user()->company_id;
            $shortUrl->title = $request->post('title');
            $shortUrl->original_url = $request->post('original_url');
            $shortUrl->is_enabled = 'Y';
            $shortUrl->is_deleted = 'N';
            $shortUrl->created_by = Auth::id();
            $shortUrl->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->withInput()->with('errorMessage', 'Something went wrong. Please try again.');
        }

        return redirect()->route('admin.manageShortUrl')->with('message', 'Short URL created successfully.');
    }

    public function editShortUrl($id)
    {
        $shortUrl = $this->findOwnShortUrl($id);
        if (!$shortUrl) {
            return redirect('/admin/unaccess');
        }

        return view('admin.shorturl.editShortUrl', compact('shortUrl'));
    }

    public function updateShortUrl(Request $request, $id)
    {
        $shortUrl = $this->findOwnShortUrl($id);
        if (!$shortUrl) {
            return redirect('/admin/unaccess');
        }

        $request->validate(
            [
                'title' => ['nullable', 'string', 'max:100'],
                'original_url' => ['required', 'url', 'max:2048'],
            ],
            [
                'title.string' => 'The Title must be a string.',
                'title.max' => 'The Title must not be greater than 100 characters.',
                'original_url.required' => 'The Original URL field is required.',
                'original_url.url' => 'The Original URL must be a valid URL.',
                'original_url.max' => 'The Original URL must not be greater than 2048 characters.',
            ]
        );

        $nameExist = ShortUrl::where('created_by', Auth::id())
            ->where('original_url', $request->original_url)
            ->where('id', '!=', $id)
            ->where('is_deleted', 'N')
            ->first();

        if ($nameExist) {
            return redirect()->back()->withInput()->with('errorMessage', 'This URL already exists.');
        }

        try {
            $shortUrl->title = $request->post('title');
            $shortUrl->original_url = $request->post('original_url');
            $shortUrl->updated_by = Auth::id();
            $shortUrl->save();
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->withInput()->with('errorMessage', 'Something went wrong. Please try again.');
        }

        return redirect()->route('admin.manageShortUrl')->with('message', 'Short URL updated successfully.');
    }

    public function deleteShortUrl($id)
    {
        $shortUrl = $this->findOwnShortUrl($id);
        if (!$shortUrl) {
            return response('unauthorized', 403);
        }

        $shortUrl->is_enabled = 'N';
        $shortUrl->is_deleted = 'Y';
        $shortUrl->updated_by = Auth::id();
        $shortUrl->save();

        return response('success');
    }

    public function statusShortUrl($id)
    {
        $shortUrl = $this->findOwnShortUrl($id);
        if (!$shortUrl) {
            return redirect()->back()->with('errorMessage', 'Short URL does not exist.');
        }

        if ($shortUrl->is_enabled == 'Y') {
            $shortUrl->is_enabled = 'N';
        } else {
            $shortUrl->is_enabled = 'Y';
        }
        $shortUrl->updated_by = Auth::id();
        $shortUrl->save();

        return redirect()->back()->with('message', 'Short URL updated successfully.');
    }

    private function findOwnShortUrl($id)
    {
        if (!ShortUrl::userCanCreate(Auth::user())) {
            return null;
        }

        return ShortUrl::where('id', $id)
            ->where('created_by', Auth::id())
            ->where('is_deleted', 'N')
            ->first();
    }
}
