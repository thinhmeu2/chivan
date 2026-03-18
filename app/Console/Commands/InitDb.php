<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InitDb extends BaseCommand
{
    protected $signature = 'db:init';
    protected $description = 'Khởi tạo db';

    protected function executeCommand(): void
    {
        Schema::disableForeignKeyConstraints();

        $tables = DB::select('SHOW TABLES');
        $dbName = DB::getDatabaseName();
        $key = 'Tables_in_' . $dbName;

        foreach ($tables as $table) {
            $tableName = $table->$key;

            // nếu muốn giữ migrations thì bỏ qua
            if ($tableName === 'migrations') {
                continue;
            }

            DB::table($tableName)->truncate();
        }

        Schema::enableForeignKeyConstraints();

        \Artisan::call('db:permissions');
        \Artisan::call('db:roles');
        \Artisan::call('db:admins');
        \Artisan::call('db:categories');
        \Artisan::call('db:results');
    }
}
