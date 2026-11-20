<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:user.view')->only(['manageUser', 'allUsers']);
        $this->middleware('permission:user.invite')->only(['inviteUser', 'addInvite']);
        $this->middleware('permission:user.edit')->only(['editUser', 'updateUser']);
        $this->middleware('permission:user.delete')->only(['deleteUser']);
        $this->middleware('permission:user.status')->only(['statusUser']);
    }

    public function manageUser()
    {
        return view('admin.user.manageUser');
    }

    public function allUsers(Request $request)
    {
        if ($request->ajax() || $request->wantsJson()) {
            $authUser = Auth::user();
            $query = User::query()
                ->select('users.id', 'users.name', 'users.email', 'users.is_enabled', 'users.company_id', 'roles.name as role_name', 'companies.company_name')
                ->leftJoin('model_has_roles', function ($join) {
                    $join->on('users.id', '=', 'model_has_roles.model_id')
                        ->where('model_has_roles.model_type', '=', User::class);
                })
                ->leftJoin('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->leftJoin('companies', 'users.company_id', '=', 'companies.id')
                ->where('users.is_deleted', 'N')
                ->where('users.id', '!=', $authUser->id)
                ->where(function ($query) {
                    $query->whereNull('roles.name')
                        ->orWhere('roles.name', '!=', 'SuperAdmin');
                });

            if ($authUser->hasRole('SuperAdmin')) {
                $role = $request->input('role');
                if (in_array($role, ['Admin', 'Member'], true)) {
                    $query->where('roles.name', $role);
                } else {
                    $query->whereIn('roles.name', ['Admin', 'Member']);
                }
                $query->orderBy('users.id', 'desc');
            } else {
                $query->where('users.company_id', $authUser->company_id);
            }

            $table = datatables()->of($query)
                ->addIndexColumn()
                ->addColumn('Name', function ($row) {
                    return e(ucfirst($row->name));
                })
                ->addColumn('Email', function ($row) {
                    return e($row->email);
                })
                ->addColumn('Role', function ($row) {
                    return e($row->role_name ?: '-');
                })
                ->addColumn('Company', function ($row) {
                    return e($row->company_name ?: '-');
                })
                ->addColumn('action', function ($row) {
                    $actions = '';
                    if (!$this->canManageUser($row)) {
                        return $actions;
                    }

                    if (Auth::user()->hasPermissionTo('user.status')) {
                        $statusUrl = route('admin.statusUser', $row->id);
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

                    if (Auth::user()->hasPermissionTo('user.edit')) {
                        $url = route('admin.editUser', $row->id);
                        $actions .= '<a href="' . $url . '"><button type="button" class="btn btn-secondary btn-sm mb-1 mx-1 edit-button">Edit</button></a>';
                    }

                    if (Auth::user()->hasPermissionTo('user.delete')) {
                        $actions .= '<a class="btn btn-danger btn-sm mb-1 mx-1 btn-delete" href="javascript:void(0);" data-id="' . $row->id . '" data-toggle="modal" data-target="#deleteModal">Delete</a>';
                    }

                    return $actions;
                })
                ->rawColumns(['action']);

            if ($authUser->hasRole('SuperAdmin')) {
                $table->order(function ($query) {
                    $query->orderBy('users.id', 'desc');
                });
            }

            return $table->make(true);
        }

        return view('admin.user.manageUser');
    }

    public function inviteUser()
    {
        $authUser = Auth::user();
        if (!$authUser->hasRole('SuperAdmin') && !$authUser->hasRole('Admin')) {
            return redirect('/admin/unaccess');
        }

        if ($authUser->hasRole('Admin') && !$authUser->company_id) {
            return redirect()->route('admin.manageUser')->with('errorMessage', 'Your account is not linked to a company.');
        }

        $company = $authUser->company;

        return view('admin.user.inviteUser', compact('company'));
    }

    public function addInvite(Request $request)
    {
        $authUser = Auth::user();
        if ($authUser->hasRole('SuperAdmin')) {
            return $this->inviteCompanyAdmin($request, $authUser);
        }

        if ($authUser->hasRole('Admin')) {
            return $this->inviteCompanyUser($request, $authUser);
        }

        return redirect('/admin/unaccess');
    }

    public function editUser($id)
    {
        $user = $this->findManagedUser($id);
        if (!$user) {
            return redirect()->route('admin.manageUser')->with('errorMessage', 'User account does not exist.');
        }

        $roleName = $user->getRoleNames()->first();

        return view('admin.user.editUser', compact('user', 'roleName'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = $this->findManagedUser($id);
        if (!$user) {
            return redirect()->route('admin.manageUser')->with('errorMessage', 'User account does not exist.');
        }

        $roleName = $user->getRoleNames()->first();

        $request->validate(
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
                'role' => ['required', 'in:' . $roleName],
            ],
            [
                'name.required' => 'The Name field is required.',
                'name.max' => 'The Name must not be greater than 100 characters.',
                'email.required' => 'The Email field is required.',
                'email.email' => 'The Email must be a valid email address.',
                'email.unique' => 'The Email has already been taken.',
                'role.required' => 'The Role field is required.',
                'role.in' => 'This role cannot be changed.',
            ]
        );

        try {
            $user->name = $request->post('name');
            $user->email = $request->post('email');
            $user->updated_by = Auth::id();
            $user->save();
            $user->syncRoles([$roleName]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());

            return redirect()->back()->withInput()->with('errorMessage', 'Something went wrong. Please try again.');
        }

        return redirect()->route('admin.manageUser')->with('message', 'User updated successfully.');
    }

    public function deleteUser($id)
    {
        $user = $this->findManagedUser($id);
        if (!$user) {
            return response('User account does not exist.', 404);
        }

        $user->is_enabled = 'N';
        $user->is_deleted = 'Y';
        $user->updated_by = Auth::id();
        $user->save();

        return response('success');
    }

    public function statusUser($id)
    {
        $user = $this->findManagedUser($id);
        if (!$user) {
            return redirect()->route('admin.manageUser')->with('errorMessage', 'User account does not exist.');
        }

        if ($user->is_enabled == 'Y') {
            $user->is_enabled = 'N';
        } else {
            $user->is_enabled = 'Y';
        }
        $user->updated_by = Auth::id();
        $user->save();

        return redirect()->back()->with('message', 'User updated successfully.');
    }

    private function inviteCompanyAdmin(Request $request, User $authUser)
    {
        $request->validate(
            [
                'company_name' => ['required', 'string', 'max:150', Rule::unique('companies', 'company_name')->where(function ($query) {
                    return $query->where('is_deleted', 'N');
                })],
                'company_code' => ['required', 'string', 'max:50', 'unique:companies,company_code'],
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email'],
                'password' => ['required', 'string', 'min:6', 'max:50', 'confirmed'],
                'role' => ['required', 'in:Admin'],
            ],
            [
                'company_name.required' => 'The Company Name field is required.',
                'company_name.max' => 'The Company Name must not be greater than 150 characters.',
                'company_name.unique' => 'The Company Name has already been taken.',
                'company_code.required' => 'The Company Code field is required.',
                'company_code.max' => 'The Company Code must not be greater than 50 characters.',
                'company_code.unique' => 'The Company Code has already been taken.',
                'name.required' => 'The Name field is required.',
                'name.max' => 'The Name must not be greater than 100 characters.',
                'email.required' => 'The Email field is required.',
                'email.email' => 'The Email must be a valid email address.',
                'email.unique' => 'The Email has already been taken.',
                'password.required' => 'The Password field is required.',
                'password.min' => 'The Password must be at least 6 characters.',
                'password.confirmed' => 'The Password confirmation does not match.',
                'role.required' => 'The Role field is required.',
                'role.in' => 'Super Admin can only invite an Admin for a new company.',
            ]
        );

        DB::beginTransaction();
        try {
            $company = new Company();
            $company->company_name = $request->post('company_name');
            $company->company_code = $request->post('company_code');
            $company->is_enabled = 'Y';
            $company->is_deleted = 'N';
            $company->created_by = $authUser->id;
            $company->save();

            $user = $this->storeInvitedUser($request, $company->id, 'Admin', $authUser->id);
            $this->storeInvitation($company->id, $user->id, 'Admin', $authUser->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->withInput()->with('errorMessage', 'Something went wrong. Please try again.');
        }

        return redirect()->route('admin.manageUser')->with('message', 'Company admin invited successfully.');
    }

    private function inviteCompanyUser(Request $request, User $authUser)
    {
        if ($request->filled('company_name') || $request->filled('new_company')) {
            return redirect()->back()->withInput()->with('errorMessage', 'A new company cannot be created.');
        }

        if (!$authUser->company_id) {
            return redirect()->back()->withInput()->with('errorMessage', 'Your account is not linked to a company.');
        }

        $request->validate(
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:150', 'unique:users,email'],
                'password' => ['required', 'string', 'min:6', 'max:50', 'confirmed'],
                'role' => ['required', 'in:Admin,Member'],
            ],
            [
                'name.required' => 'The Name field is required.',
                'name.max' => 'The Name must not be greater than 100 characters.',
                'email.required' => 'The Email field is required.',
                'email.email' => 'The Email must be a valid email address.',
                'email.unique' => 'The Email has already been taken.',
                'password.required' => 'The Password field is required.',
                'password.min' => 'The Password must be at least 6 characters.',
                'password.confirmed' => 'The Password confirmation does not match.',
                'role.required' => 'The Role field is required.',
                'role.in' => 'An Admin can invite an Admin or a Member in their own company.',
            ]
        );

        $role = $request->post('role');

        DB::beginTransaction();
        try {
            $user = $this->storeInvitedUser($request, $authUser->company_id, $role, $authUser->id);
            $this->storeInvitation($authUser->company_id, $user->id, $role, $authUser->id);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());

            return redirect()->back()->withInput()->with('errorMessage', 'Something went wrong. Please try again.');
        }

        return redirect()->route('admin.manageUser')->with('message', $role . ' invited successfully.');
    }

    private function storeInvitedUser(Request $request, $companyId, $role, $createdBy)
    {
        $user = new User();
        $user->name = $request->post('name');
        $user->email = $request->post('email');
        $user->password = $request->post('password');
        $user->company_id = $companyId;
        $user->is_enabled = 'Y';
        $user->is_deleted = 'N';
        $user->created_by = $createdBy;
        $user->save();
        $user->assignRole($role);

        return $user;
    }

    private function storeInvitation($companyId, $userId, $role, $invitedBy)
    {
        $invitation = new Invitation();
        $invitation->company_id = $companyId;
        $invitation->user_id = $userId;
        $invitation->role_name = $role;
        $invitation->invited_by = $invitedBy;
        $invitation->is_enabled = 'Y';
        $invitation->is_deleted = 'N';
        $invitation->save();
    }

    private function findManagedUser($id)
    {
        $authUser = Auth::user();
        $user = User::where('id', $id)->where('is_deleted', 'N')->first();

        if (!$user) {
            return null;
        }

        if ($user->hasRole('SuperAdmin')) {
            return null;
        }

        if ($authUser && $user->id == $authUser->id) {
            return null;
        }

        if (!$this->canManageUser($user)) {
            return null;
        }

        return $user;
    }

    private function canManageUser($user)
    {
        $authUser = Auth::user();

        if ($authUser->hasRole('SuperAdmin')) {
            return $user->hasRole('Admin') || $user->hasRole('Member');
        }

        if ($authUser->hasRole('Admin')) {
            $sameCompany = (int) $user->company_id === (int) $authUser->company_id;

            return $sameCompany && $user->hasRole('Member');
        }

        return false;
    }
}
