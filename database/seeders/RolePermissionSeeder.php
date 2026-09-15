<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            ['name' => 'View dashboard', 'slug' => 'dashboard.view'],
            ['name' => 'Manage clients', 'slug' => 'clients.manage'],
            ['name' => 'Manage services', 'slug' => 'services.manage'],
            ['name' => 'Manage domains', 'slug' => 'domains.manage'],
            ['name' => 'Manage DNS', 'slug' => 'dns.manage'],
            ['name' => 'Manage email', 'slug' => 'email.manage'],
            ['name' => 'Manage hosting', 'slug' => 'hosting.manage'],
            ['name' => 'Manage providers', 'slug' => 'providers.manage'],
            ['name' => 'Manage servers', 'slug' => 'servers.manage'],
            ['name' => 'Manage provisioning', 'slug' => 'provisioning.manage'],
            ['name' => 'Manage synchronization', 'slug' => 'synchronization.manage'],
            ['name' => 'View audit logs', 'slug' => 'audit.view'],
            ['name' => 'Manage users', 'slug' => 'users.manage'],
            ['name' => 'Manage roles', 'slug' => 'roles.manage'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $allPermissions = Permission::all();

        $superAdmin = Role::updateOrCreate(
            ['slug' => 'super-admin'],
            [
                'name' => 'Super Admin',
                'description' => 'Full access to the platform.',
            ]
        );

        $admin = Role::updateOrCreate(
            ['slug' => 'admin'],
            [
                'name' => 'Admin',
                'description' => 'Administrative access to the platform.',
            ]
        );

        $operator = Role::updateOrCreate(
            ['slug' => 'operator'],
            [
                'name' => 'Operator',
                'description' => 'Operational access to services and provisioning.',
            ]
        );

        $viewer = Role::updateOrCreate(
            ['slug' => 'viewer'],
            [
                'name' => 'Viewer',
                'description' => 'Read-only access to the platform.',
            ]
        );

        /*
         * Super Admin
         */
        $this->attachPermissions(
            $superAdmin,
            $allPermissions->pluck('id')->all()
        );

        /*
         * Admin
         */
        $this->attachPermissions(
            $admin,
            $allPermissions
                ->whereNotIn('slug', [
                    'users.manage',
                    'roles.manage',
                ])
                ->pluck('id')
                ->all()
        );

        /*
         * Operator
         */
        $this->attachPermissions(
            $operator,
            $allPermissions
                ->whereIn('slug', [
                    'dashboard.view',
                    'clients.manage',
                    'services.manage',
                    'domains.manage',
                    'dns.manage',
                    'email.manage',
                    'hosting.manage',
                    'provisioning.manage',
                    'synchronization.manage',
                ])
                ->pluck('id')
                ->all()
        );

        /*
         * Viewer
         */
        $this->attachPermissions(
            $viewer,
            $allPermissions
                ->whereIn('slug', [
                    'dashboard.view',
                    'audit.view',
                ])
                ->pluck('id')
                ->all()
        );
    }

    private function attachPermissions(Role $role, array $permissionIds): void
    {
        $now = now();

        $permissions = [];

        foreach ($permissionIds as $permissionId) {
            $permissions[$permissionId] = [
                'created_at' => $now,
            ];
        }

        $role->permissions()->sync($permissions);
    }
}