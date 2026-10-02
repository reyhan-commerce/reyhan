<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Reyhan\Core\Support\Modules\ModuleManager;
use Reyhan\Core\Support\Reyhan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class ReyhanVersionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reyhan:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display Reyhan Commerce system versions and runtime environment';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('<fg=green;options=bold>╭────────────────────────────────────────────────────────╮</>');
        $this->line('<fg=green;options=bold>│       🌿  Reyhan Headless Commerce Framework           │</>');
        $this->line('<fg=green;options=bold>╰────────────────────────────────────────────────────────╯</>');
        $this->newLine();

        $rootManifestPath = base_path('../version.json');
        $rootManifest = File::exists($rootManifestPath)
            ? json_decode(File::get($rootManifestPath), true)
            : [];

        $installedExtensions = ModuleManager::getLoadedExtensions();

        $rows = [
            ['Reyhan Core', Reyhan::version()],
            ['Laravel Framework', app()->version()],
            ['PHP Runtime', PHP_VERSION],
            ['Filament Admin Panel', '5.8.x'],
            ['PostgreSQL Driver', config('database.default')],
            ['Cache / Queue Store', config('cache.default').' / '.config('queue.default')],
            ['Active User Extensions', (string) count($installedExtensions)],
        ];

        $this->table(['Component', 'Version / Driver'], $rows);
        $this->newLine();

        if (! empty($installedExtensions)) {
            $this->info('📦 Installed Custom Extensions:');
            foreach ($installedExtensions as $ext) {
                $this->line("  • <fg=yellow>{$ext['name']}</> (<fg=gray>v{$ext['version']}</>) - {$ext['id']}");
            }
            $this->newLine();
        }

        return self::SUCCESS;
    }
}
