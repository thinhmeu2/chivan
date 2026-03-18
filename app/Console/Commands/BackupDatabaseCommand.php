<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-database-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Tạo thư mục
        $disk = Storage::disk('local');
        $dir = 'db-backups';
        if (! $disk->exists($dir)) {
            $disk->makeDirectory($dir);
        }

        // Tên file
        $filename = 'backup_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/' . $dir . '/' . $filename);

        // Dùng Laravel Process
        $process = new Process([
            'mysqldump',
            '--user=' . env('DB_USERNAME'),
            '--password=' . env('DB_PASSWORD'),
            '--host=' . env('DB_HOST'),
            env('DB_DATABASE'),
        ]);

        // Ghi ra file thay vì chuyển hướng bằng shell
        $process->run(function ($type, $buffer) use ($path) {
            file_put_contents($path, $buffer, FILE_APPEND);
        });

        $this->info('Backup created: ' . $path);

        // Xóa backup cũ hơn 7 ngày
        $files = $disk->files($dir);
        $now = now();

        foreach ($files as $file) {
            // Lấy timestamp từ file (dựa vào tên file backup_YYYY-mm-dd_HH-ii-ss.sql)
            $basename = basename($file);

            if (preg_match('/backup_(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})\.sql/', $basename, $m)) {
                $fileTime = \Carbon\Carbon::createFromFormat('Y-m-d_H-i-s', $m[1]);

                if ($fileTime->diffInDays($now) > 7) {
                    $disk->delete($file);
                    $this->info('Deleted old backup: ' . $file);
                }
            }
        }

        return self::SUCCESS;
    }

}
