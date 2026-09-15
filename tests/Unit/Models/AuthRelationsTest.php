<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Tests\TestCase;

class AuthRelationsTest extends TestCase
{
    public function test_user_roles_relation(): void
    {
        $this->assertInstanceOf(
            BelongsToMany::class,
            (new User())->roles()
        );
    }

    public function test_role_users_relation(): void
    {
        $this->assertInstanceOf(
            BelongsToMany::class,
            (new Role())->users()
        );
    }

    public function test_role_permissions_relation(): void
    {
        $this->assertInstanceOf(
            BelongsToMany::class,
            (new Role())->permissions()
        );
    }

    public function test_permission_roles_relation(): void
    {
        $this->assertInstanceOf(
            BelongsToMany::class,
            (new Permission())->roles()
        );
    }
}