<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

abstract class BaseCommand extends Command
{
    final public function handle(): void
    {
        $this->info("🔍 Bắt đầu xử lý: $this->signature");

        $start = microtime(true);

        $this->executeCommand();

        $time = round(microtime(true) - $start, 2);
        $this->info("🎉 {$this->signature} hoàn tất trong {$time}s");
    }

    /**
     * Command con sẽ implement ở đây
     */
    abstract protected function executeCommand(): void;
}
