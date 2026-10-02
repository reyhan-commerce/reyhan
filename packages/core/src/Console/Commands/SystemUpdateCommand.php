<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Reyhan\Core\Actions\System\PerformCoreUpdateAction;
use Illuminate\Console\Command;

final class SystemUpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:update {--skip-backup : Skip automated pre-update database backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a safe, zero-downtime core update with migrations and cache rebuild';

    /**
     * Execute the console command.
     */
    public function handle(PerformCoreUpdateAction $action): int
    {
        $this->newLine();
        $this->line('<fg=cyan;options=bold>╭────────────────────────────────────────────────────────╮</>');
        $this->line('<fg=cyan;options=bold>│       🚀 Reyhan Core Engine Update Sequence            │</>');
        $this->line('<fg=cyan;options=bold>╰────────────────────────────────────────────────────────╯</>');
        $this->newLine();

        $skipBackup = (bool) $this->option('skip-backup');
        $result = $action->execute($skipBackup);

        foreach ($result->logs as $log) {
            if (str_starts_with($log, '[')) {
                $this->line("<fg=yellow>{$log}</>");
            } elseif (str_starts_with($log, '✔') || str_starts_with($log, '🎉')) {
                $this->line("<fg=green>{$log}</>");
            } elseif (str_starts_with($log, '✖')) {
                $this->line("<fg=red;options=bold>{$log}</>");
            } else {
                $this->line("  <fg=gray>{$log}</>");
            }
        }

        $this->newLine();

        if (! $result->success) {
            $this->error("✖ {$result->message}");
            $this->newLine();

            return self::FAILURE;
        }

        $this->info("✨ {$result->message} (Duration: {$result->duration})");
        $this->newLine();

        return self::SUCCESS;
    }
}
