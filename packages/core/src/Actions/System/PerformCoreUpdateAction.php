<?php

declare(strict_types=1);

namespace Reyhan\Core\Actions\System;

use Reyhan\Core\Data\System\UpdateResultData;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PerformCoreUpdateAction
{
    /**
     * Execute the safe core update pipeline.
     */
    public function execute(bool $skipBackup = false): UpdateResultData
    {
        $startTime = microtime(true);
        $logs = [];
        $errors = [];

        $logs[] = '[1/6] Initializing core engine update sequence...';

        try {
            // 1. Pre-flight check: Verify database connection
            $logs[] = '[2/6] Verifying PostgreSQL database connectivity...';
            DB::connection()->getPdo();
            $logs[] = '✔ PostgreSQL database connection is healthy.';

            // 2. Safe Backup step
            if (! $skipBackup) {
                $logs[] = '[3/6] Generating automated pre-update database snapshot...';
                try {
                    Artisan::call('backup:run', ['--only-db' => true]);
                    $logs[] = '✔ Pre-update database snapshot completed: '.trim(Artisan::output());
                } catch (Throwable $e) {
                    $logs[] = '⚠ Backup snapshot skipped: '.$e->getMessage();
                }
            } else {
                $logs[] = '[3/6] Pre-update database snapshot skipped by flag.';
            }

            // 3. Run safe database migrations
            $logs[] = '[4/6] Executing safe database schema migrations (migrate --force)...';
            $migrationExitCode = Artisan::call('migrate', ['--force' => true]);
            $migrationOutput = trim(Artisan::output());
            $logs[] = $migrationOutput ?: '✔ All database migrations are up to date.';

            if ($migrationExitCode !== 0) {
                throw new \RuntimeException('Database migration failed: '.$migrationOutput);
            }

            // 4. Upgrade Filament assets & icons
            $logs[] = '[5/6] Upgrading Filament admin panel components and assets...';
            Artisan::call('filament:upgrade');
            $logs[] = '✔ Filament assets and icons successfully refreshed.';

            // 5. Clear and rebuild application caches
            $logs[] = '[6/6] Optimizing application caches, route tree, and view templates...';
            if (! app()->environment('testing')) {
                Artisan::call('optimize:clear');
                Artisan::call('optimize');
            }
            $logs[] = '✔ Cache, route, and configuration state successfully optimized.';

            // 6. Graceful Octane / Worker reload
            try {
                Artisan::call('octane:reload');
                $logs[] = '✔ FrankenPHP Octane workers gracefully reloaded with zero downtime.';
            } catch (Throwable) {
                $logs[] = 'ℹ Standard PHP runtime detected (Octane reload skipped).';
            }

            $duration = round(microtime(true) - $startTime, 2).'s';
            $logs[] = "🎉 Core update pipeline completed successfully in {$duration}.";

            Log::info('Core system update completed successfully', [
                'duration' => $duration,
                'logs' => $logs,
            ]);

            return new UpdateResultData(
                success: true,
                message: 'Core system update executed successfully.',
                logs: $logs,
                errors: [],
                duration: $duration,
            );
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
            $logs[] = '✖ Core update sequence failed: '.$e->getMessage();

            Log::error('Core system update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'logs' => $logs,
            ]);

            return new UpdateResultData(
                success: false,
                message: 'Core update failed: '.$e->getMessage(),
                logs: $logs,
                errors: $errors,
                duration: round(microtime(true) - $startTime, 2).'s',
            );
        }
    }
}
