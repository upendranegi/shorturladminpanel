<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $permissionId)
    {
        $permissionName = $permissionId;
        if (is_numeric($permissionId)) {
            $permission = Permission::find($permissionId);
            $permissionName = $permission ? $permission->name : null;
        }

        if (!$permissionName || !auth()->user() || !auth()->user()->hasPermissionTo($permissionName)) {
            return redirect('/admin/unaccess');
        }

        return $next($request);
    }
}
