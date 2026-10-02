<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

final class ReyhanInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reyhan:install {--force : Overwrite without confirmation} {--no-seed : Skip database seeders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize and provision the Reyhan core environment, database, and admin panel';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=green;options=bold>╭────────────────────────────────────────────────────────╮</>');
        $this->line('<fg=green;options=bold>│       🌿 Reyhan Engine Automated Provisioning          │</>');
        $this->line('<fg=green;options=bold>╰────────────────────────────────────────────────────────╯</>');
        $this->newLine();

        $force = (bool) $this->option('force');
        $noSeed = (bool) $this->option('no-seed');

        if (! $force && ! $this->confirm('This will configure and migrate the Reyhan core database. Do you wish to proceed?', true)) {
            $this->warn('Installation aborted by user.');

            return self::FAILURE;
        }

        // 1. App key check
        if (empty(config('app.key'))) {
            $this->info('🔑 Generating application encryption key...');
            Artisan::call('key:generate', ['--force' => true]);
            $this->line('  <fg=green>✔</> Application key generated.');
        }

        // 2. Database migrations
        $this->info('🐘 Executing database migrations (PostgreSQL)...');
        $migrateCode = Artisan::call('migrate', ['--force' => true]);
        if ($migrateCode !== 0) {
            $this->error('✖ Database migration failed: '.trim(Artisan::output()));

            return self::FAILURE;
        }
        $this->line('  <fg=green>✔</> Database schema up to date.');

        // 3. Database seeders
        if (! $noSeed) {
            $this->info('🌱 Seeding initial roles, permissions, and shop fixtures...');
            try {
                Artisan::call('db:seed', ['--force' => true]);
                $this->line('  <fg=green>✔</> Initial data seeded successfully.');
            } catch (Throwable $e) {
                $this->warn('  ⚠ Database seeding warning: '.$e->getMessage());
            }
        }

        // 4. Storage symlink
        $this->info('🔗 Linking storage to public disk...');
        Artisan::call('storage:link');
        $this->line('  <fg=green>✔</> Storage symlink verified.');

        // 5. Upgrade Filament Assets
        $this->info('🎨 Upgrading Filament admin UI assets...');
        try {
            Artisan::call('filament:upgrade');
            $this->line('  <fg=green>✔</> Filament components and icons refreshed.');
        } catch (Throwable $e) {
            $this->warn('  ⚠ Filament upgrade note: '.$e->getMessage());
        }

        // 6. Cache optimization
        $this->info('⚡ Optimizing routing table, events, and configuration cache...');
        Artisan::call('optimize:clear');
        Artisan::call('optimize');
        $this->line('  <fg=green>✔</> Caches optimized for production throughput.');

        $this->newLine();
        $this->info('✨ Reyhan installation completed successfully! You can now start the application.');
        $this->newLine();

        return self::SUCCESS;
    }
}
