<?php

namespace App\Console\Commands\DB;

use App\Console\Commands\BaseCommand;
use App\Enums\ModelEnum;
use App\Enums\PermissionEnum;
use Illuminate\Support\Facades\DB;

class Permission extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'tạo permission theo các model có route';


    protected function executeCommand(): void
    {
        $count = 0;

        foreach (ModelEnum::routeName() as $className => $routerName) {

            foreach (PermissionEnum::cases() as $permission) {

                $exists = DB::table('permissions')
                    ->where('type', $permission->value)
                    ->where('model_type', $className)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('permissions')->insert([
                    'type'       => $permission->value,
                    'model_type' => $className,
                ]);

                $count++;
            }
        }

        $this->line("Created {$count} permissions");
    }
}
