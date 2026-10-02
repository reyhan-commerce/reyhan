<?php

declare(strict_types=1);

namespace Reyhan\Core\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

final class MakePluginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reyhan:make:plugin {name : The English name of the plugin, e.g. SmsKavenegar or LoyaltyBonus}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scaffold a complete, isolated Reyhan plugin extension package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawName = (string) $this->argument('name');
        $studlyName = Str::studly($rawName);
        $kebabName = Str::kebab($rawName);
        $snakeName = Str::snake($rawName);

        $pluginDir = base_path("extensions/{$kebabName}");

        if (File::exists($pluginDir)) {
            $this->error("✖ Plugin extension directory already exists at: {$pluginDir}");

            return self::FAILURE;
        }

        File::ensureDirectoryExists("{$pluginDir}/src");
        File::ensureDirectoryExists("{$pluginDir}/config");
        File::ensureDirectoryExists("{$pluginDir}/routes");

        // 1. composer.json
        $composerJson = json_encode([
            'name' => "reyhan-extensions/{$kebabName}",
            'description' => "Reyhan Commerce Extension: {$studlyName}",
            'type' => 'reyhan-plugin',
            'license' => 'MIT',
            'autoload' => [
                'psr-4' => [
                    "Reyhan\\Plugins\\{$studlyName}\\" => 'src/',
                ],
            ],
            'extra' => [
                'laravel' => [
                    'providers' => [
                        "Reyhan\\Plugins\\{$studlyName}\\{$studlyName}ServiceProvider",
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        File::put("{$pluginDir}/composer.json", (string) $composerJson);

        // 2. Service Provider
        $serviceProviderContent = <<<PHP
<?php

declare(strict_types=1);

namespace Reyhan\Plugins\\{$studlyName};

use Reyhan\Core\Support\Extensions\ReyhanExtensionServiceProvider;

final class {$studlyName}ServiceProvider extends ReyhanExtensionServiceProvider
{
    public function register(): void
    {
        \$this->mergeConfigFrom(__DIR__ . '/../config/{$snakeName}.php', 'reyhan.plugins.{$snakeName}');
    }

    public function boot(): void
    {
        if (file_exists(__DIR__ . '/../routes/api.php')) {
            \$this->loadExtensionApiRoutes(__DIR__ . '/../routes/api.php', 'v1/plugins/{$kebabName}');
        }

        if (\$this->app->runningInConsole()) {
            \$this->publishes([
                __DIR__ . '/../config/{$snakeName}.php' => config_path('reyhan/plugins/{$snakeName}.php'),
            ], '{$kebabName}-config');
        }
    }
}
PHP;
        File::put("{$pluginDir}/src/{$studlyName}ServiceProvider.php", $serviceProviderContent);

        // 3. Config file
        $configContent = <<<PHP
<?php

declare(strict_types=1);

return [
    'enabled' => env('REYHAN_PLUGIN_' . strtoupper('{$snakeName}') . '_ENABLED', true),
];
PHP;
        File::put("{$pluginDir}/config/{$snakeName}.php", $configContent);

        // 4. Routes file
        $routesContent = <<<PHP
<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/plugins/{$kebabName}')
    ->middleware(['api'])
    ->group(function () {
        // Define plugin API routes here
    });
PHP;
        File::put("{$pluginDir}/routes/api.php", $routesContent);

        // 5. README.md
        $readmeContent = "# Reyhan Commerce Plugin: {$studlyName}\n\nPlugin package created for Reyhan Headless E-Commerce Framework.\n";
        File::put("{$pluginDir}/README.md", $readmeContent);

        $this->newLine();
        $this->line("<fg=green;options=bold>✔ Plugin [{$studlyName}] successfully scaffolded!</>");
        $this->line("<fg=gray>Location:</> <fg=yellow>{$pluginDir}</>");
        $this->newLine();
        $this->line('  <fg=cyan>• Service Provider:</> '."Reyhan\\Plugins\\{$studlyName}\\{$studlyName}ServiceProvider");
        $this->line("  <fg=cyan>• Config:</>           config/{$snakeName}.php");
        $this->line('  <fg=cyan>• API Routes:</>       routes/api.php');
        $this->newLine();

        return self::SUCCESS;
    }
}
