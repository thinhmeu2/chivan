<?php

namespace App\Console\Commands\DB;

use App\Console\Commands\BaseCommand;
use Illuminate\Support\Facades\Hash;

class Admin extends BaseCommand
{
    protected $signature = 'db:admins';

    protected function executeCommand(): void
    {
        \App\Models\Admin::query()->upsert(
            [
                [
                    'name'       => 'thinhdev',
                    'password'   => Hash::make('Q123day2@'), // để trống tạm, xử lý sau
                    'is_admin'   => true,
                    'last_login' => null,
                    'is_active'  => true,
                    'role_id'    => 0,
                ]
            ],
            ['name'], // unique key
        );
    }
}
