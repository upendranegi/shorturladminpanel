<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Permissions: shorturl.view, shorturl.create, shorturl.edit, shorturl.delete,
     * shorturl.status, user.view, user.invite, user.edit, user.delete, user.status.
     */
    public function run(): void
    {
        $now = now()->format('Y-m-d H:i:s');
        $password = DB::getPdo()->quote(password_hash('Admin@123', PASSWORD_BCRYPT));

        DB::statement("INSERT INTO roles (name, guard_name, created_at, updated_at) VALUES
            ('SuperAdmin', 'web', '{$now}', '{$now}'),
            ('Admin', 'web', '{$now}', '{$now}'),
            ('Member', 'web', '{$now}', '{$now}')");

        DB::statement("INSERT INTO permissions (name, guard_name, created_at, updated_at) VALUES
            ('shorturl.view', 'web', '{$now}', '{$now}'),
            ('shorturl.create', 'web', '{$now}', '{$now}'),
            ('shorturl.edit', 'web', '{$now}', '{$now}'),
            ('shorturl.delete', 'web', '{$now}', '{$now}'),
            ('shorturl.status', 'web', '{$now}', '{$now}'),
            ('user.view', 'web', '{$now}', '{$now}'),
            ('user.invite', 'web', '{$now}', '{$now}'),
            ('user.edit', 'web', '{$now}', '{$now}'),
            ('user.delete', 'web', '{$now}', '{$now}'),
            ('user.status', 'web', '{$now}', '{$now}')");

        DB::statement("INSERT INTO role_has_permissions (permission_id, role_id)
            SELECT permissions.id, roles.id FROM permissions, roles
            WHERE roles.name = 'SuperAdmin' AND permissions.name IN ('shorturl.view', 'user.view', 'user.invite', 'user.edit', 'user.delete', 'user.status')");

        DB::statement("INSERT INTO role_has_permissions (permission_id, role_id)
            SELECT permissions.id, roles.id FROM permissions, roles
            WHERE roles.name = 'Admin' AND permissions.name IN ('shorturl.view', 'shorturl.create', 'shorturl.edit', 'shorturl.delete', 'shorturl.status', 'user.view', 'user.invite', 'user.edit', 'user.delete', 'user.status')");

        DB::statement("INSERT INTO role_has_permissions (permission_id, role_id)
            SELECT permissions.id, roles.id FROM permissions, roles
            WHERE roles.name = 'Member' AND permissions.name IN ('shorturl.view', 'shorturl.create', 'shorturl.edit', 'shorturl.delete', 'shorturl.status')");

        DB::statement("INSERT INTO users (name, email, password, company_id, is_enabled, is_deleted, email_verified_at, created_at, updated_at)
            VALUES ('Super Admin', 'superadmin@example.com', {$password}, NULL, 'Y', 'N', '{$now}', '{$now}', '{$now}')");

        DB::statement("INSERT INTO model_has_roles (role_id, model_type, model_id)
            SELECT roles.id, 'App\\\\Models\\\\User', users.id
            FROM roles, users
            WHERE roles.name = 'SuperAdmin' AND users.email = 'superadmin@example.com'");

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
