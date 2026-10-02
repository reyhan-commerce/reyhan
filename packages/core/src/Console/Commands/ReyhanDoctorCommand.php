<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class ReyhanDoctorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reyhan:doctor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform comprehensive system health, environment, and dependency diagnostics for Reyhan';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=green;options=bold>╭────────────────────────────────────────────────────────╮</>');
        $this->line('<fg=green;options=bold>│        🩺  Reyhan Health & Doctor Diagnostics          │</>');
        $this->line('<fg=green;options=bold>╰────────────────────────────────────────────────────────╯</>');
        $this->newLine();

        $allPassed = true;

        // 1. PHP Version
        $phpOk = version_compare(PHP_VERSION, '8.4.0', '>=');
        $this->renderCheck('PHP Runtime (>= 8.4)', $phpOk, PHP_VERSION);
        if (! $phpOk) {
            $allPassed = false;
        }

        // 2. Required PHP Extensions
        $requiredExtensions = ['pdo_pgsql', 'redis', 'bcmath', 'intl', 'mbstring', 'curl'];
        foreach ($requiredExtensions as $ext) {
            $loaded = extension_loaded($ext);
            $this->renderCheck("PHP Extension: {$ext}", $loaded, $loaded ? 'Loaded' : 'Missing');
            if (! $loaded) {
                $allPassed = false;
            }
        }

        // 3. PostgreSQL Database Connection (via .env)
        $dbHost = (string) config('database.connections.pgsql.host', '127.0.0.1');
        $dbPort = (string) config('database.connections.pgsql.port', '5432');
        try {
            DB::connection()->getPdo();
            $pgVersion = DB::selectOne('SELECT version()')->version ?? 'Connected';
            $shortVersion = explode(' ', (string) $pgVersion)[1] ?? '17.x';
            $this->renderCheck("PostgreSQL (.env {$dbHost}:{$dbPort})", true, "v{$shortVersion}");
        } catch (Throwable $e) {
            $this->renderCheck("PostgreSQL (.env {$dbHost}:{$dbPort})", false, 'Check DB credentials in .env');
            $allPassed = false;
        }

        // 4. Redis In-Memory Engine Connection (via .env)
        $redisHost = (string) config('database.redis.default.host', '127.0.0.1');
        $redisPort = (string) config('database.redis.default.port', '6379');
        try {
            Redis::connection()->ping();
            $this->renderCheck("Redis (.env {$redisHost}:{$redisPort})", true, 'Connected (PONG)');
        } catch (Throwable $e) {
            $this->renderCheck("Redis (.env {$redisHost}:{$redisPort})", false, 'Check REDIS credentials in .env');
            $allPassed = false;
        }

        // 5. Writable Directories
        $directories = [
            'storage/app' => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
        ];

        foreach ($directories as $label => $path) {
            $isWritable = is_writable($path);
            $this->renderCheck("Directory Permissions: {$label}", $isWritable, $isWritable ? 'Writable' : 'Read-Only');
            if (! $isWritable) {
                $allPassed = false;
            }
        }

        // 6. Public Storage Link
        $storageLinked = File::exists(public_path('storage'));
        $this->renderCheck('Public Storage Symlink', $storageLinked, $storageLinked ? 'Linked' : 'Run php artisan storage:link');
        if (! $storageLinked) {
            $allPassed = false;
        }

        // 7. Core Configuration File
        $configExists = File::exists(config_path('reyhan.php'));
        $this->renderCheck('Reyhan Core Config', $configExists, $configExists ? 'Present' : 'Missing config/reyhan.php');
        if (! $configExists) {
            $allPassed = false;
        }

        $this->newLine();

        if ($allPassed) {
            $this->info('✨ All system diagnostics passed! Reyhan Core is operating in optimal health.');
            $this->newLine();

            return self::SUCCESS;
        }

        $this->error('✖ Some diagnostic checks failed. Review the warnings above before launching.');
        $this->newLine();

        return self::FAILURE;
    }

    private function renderCheck(string $title, bool $passed, string $detail): void
    {
        $status = $passed ? '<fg=green>✔ PASS</>' : '<fg=red;options=bold>✖ FAIL</>';
        $detailColor = $passed ? 'gray' : 'yellow';

        $this->line(sprintf('  %-35s %s  <fg=%s>(%s)</>', $title, $status, $detailColor, $detail));
    }
}
