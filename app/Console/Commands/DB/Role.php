<?php

namespace App\Console\Commands\DB;

use App\Console\Commands\BaseCommand;
use App\Models\Permission;

class Role extends BaseCommand
{
    protected $signature = 'db:roles';

    protected function executeCommand(): void
    {
        $permissionIds = Permission::query()->pluck('id')->toArray();

        $role = \App\Models\Role::query()->createOrFirst([
            'name' => 'Admin'
        ]);

        $role->permissions()->sync($permissionIds);
    }
}
