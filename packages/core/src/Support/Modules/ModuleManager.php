<?php

declare(strict_types=1);

namespace Reyhan\Core\Support\Modules;

use Composer\Autoload\ClassLoader;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ModuleManager
{
    /**
     * Cache of loaded extensions metadata.
     *
     * @var array<string, array{id: string, name: string, version: string, path: string, provider: class-string}>
     */
    private static array $loadedExtensions = [];

    /**
     * Scan the extensions directory and register discovered extension service providers.
     */
    public static function registerDiscoveredExtensions(Application $app): void
    {
        if (! (bool) config('reyhan.extensions.enabled', true)) {
            return;
        }

        $extensionsPath = (string) config('reyhan.extensions.directory', base_path('extensions'));

        if (! File::isDirectory($extensionsPath)) {
            return;
        }

        $directories = File::directories($extensionsPath);

        foreach ($directories as $dir) {
            self::loadExtensionFromDirectory($app, $dir);
        }
    }

    /**
     * Load an individual extension from a specific directory.
     */
    private static function loadExtensionFromDirectory(Application $app, string $directory): void
    {
        $manifestFile = $directory.'/module.json';
        $composerFile = $directory.'/composer.json';

        $providerClass = null;
        $id = basename($directory);
        $name = $id;
        $version = '1.0.0';

        if (File::exists($manifestFile)) {
            $manifest = json_decode(File::get($manifestFile), true);
            if (is_array($manifest)) {
                $id = (string) ($manifest['id'] ?? $id);
                $name = (string) ($manifest['name'] ?? $name);
                $version = (string) ($manifest['version'] ?? $version);
                $providerClass = $manifest['provider'] ?? null;
            }
        } elseif (File::exists($composerFile)) {
            $composer = json_decode(File::get($composerFile), true);
            if (is_array($composer)) {
                $id = (string) ($composer['name'] ?? $id);
                $name = (string) ($composer['description'] ?? $id);
                $version = (string) ($composer['version'] ?? $version);
                $providerClass = $composer['extra']['laravel']['providers'][0] ?? null;
            }
        }

        $pascalName = str_replace(['-', '_'], '', ucwords($id, '-_'));
        $srcDirectory = $directory.'/src';

        // 1. Dynamic PSR-4 Autoloader Registration
        self::registerExtensionPsr4Autoload($id, $pascalName, $directory, $composerFile, $manifestFile);

        // Conventional fallback: Check for ExtensionNameServiceProvider in src/
        if (! $providerClass) {
            $conventionalClass = "Extensions\\{$pascalName}\\{$pascalName}ServiceProvider";
            if (class_exists($conventionalClass)) {
                $providerClass = $conventionalClass;
            }
        }

        if ($providerClass && class_exists($providerClass)) {
            try {
                $app->register($providerClass);

                self::$loadedExtensions[$id] = [
                    'id' => $id,
                    'name' => $name,
                    'version' => $version,
                    'path' => $directory,
                    'provider' => $providerClass,
                ];
            } catch (Throwable $e) {
                Log::error("Failed to register Reyhan extension [{$id}]: ".$e->getMessage(), [
                    'exception' => $e,
                ]);
            }
        }
    }

    /**
     * Dynamically register extension PSR-4 namespaces into Composer ClassLoader.
     */
    private static function registerExtensionPsr4Autoload(
        string $id,
        string $pascalName,
        string $directory,
        string $composerFile,
        string $manifestFile
    ): void {
        static $composerLoader = null;

        if ($composerLoader === null) {
            $autoloaders = spl_autoload_functions() ?: [];
            foreach ($autoloaders as $autoloader) {
                if (is_array($autoloader) && isset($autoloader[0]) && $autoloader[0] instanceof ClassLoader) {
                    $composerLoader = $autoloader[0];
                    break;
                }
            }
        }

        if (! $composerLoader) {
            return;
        }

        $registered = false;

        if (File::exists($composerFile)) {
            $composer = json_decode(File::get($composerFile), true);
            if (is_array($composer) && isset($composer['autoload']['psr-4']) && is_array($composer['autoload']['psr-4'])) {
                foreach ($composer['autoload']['psr-4'] as $prefix => $path) {
                    $composerLoader->addPsr4($prefix, $directory.'/'.ltrim((string) $path, '/'));
                    $registered = true;
                }
            }
        }

        if (File::exists($manifestFile)) {
            $manifest = json_decode(File::get($manifestFile), true);
            if (is_array($manifest) && ! empty($manifest['namespace'])) {
                $prefix = rtrim((string) $manifest['namespace'], '\\').'\\';
                $srcPath = $directory.'/'.ltrim((string) ($manifest['src'] ?? 'src'), '/');
                $composerLoader->addPsr4($prefix, $srcPath);
                $registered = true;
            }
        }

        if (! $registered) {
            // Default PSR-4 mapping: Extensions\<PascalName>\ -> extensions/<id>/src
            $composerLoader->addPsr4("Extensions\\{$pascalName}\\", $directory.'/src');
        }
    }

    /**
     * Get a list of all actively loaded extensions.
     *
     * @return array<string, array{id: string, name: string, version: string, path: string, provider: class-string}>
     */
    public static function getLoadedExtensions(): array
    {
        return self::$loadedExtensions;
    }
}
