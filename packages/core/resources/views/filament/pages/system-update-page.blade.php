<x-filament-panels::page>
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        
        <!-- 1. Hero Overview Section (Native Filament Section) -->
        <x-filament::section icon="heroicon-o-cpu-chip" icon-color="primary">
            <x-slot name="heading">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 1.125rem; font-weight: 800; font-family: ui-monospace, monospace;">
                        {{ __('Reyhan Core Engine Orchestrator') }}
                    </span>
                    <x-filament::badge color="success" size="sm" icon="heroicon-m-check-circle">
                        {{ __('System Ready') }}
                    </x-filament::badge>
                </div>
            </x-slot>

            <x-slot name="description">
                {{ __('Execute automated zero-downtime database migrations, refresh Filament assets, compile runtime caches, and reload FrankenPHP Octane workers with automatic database snapshot guarantees.') }}
            </x-slot>

            <x-slot name="headerEnd">
                <x-filament::button
                    wire:click="runUpdate"
                    wire:loading.attr="disabled"
                    icon="heroicon-o-bolt"
                    color="primary"
                    size="md"
                >
                    <span wire:loading.remove>{{ __('Execute Safe Update') }}</span>
                    <span wire:loading>{{ __('Executing Pipeline...') }}</span>
                </x-filament::button>
            </x-slot>

            <!-- 2. Infrastructure Spec Badges Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-top: 0.5rem;">
                <div style="padding: 1rem; border-radius: 0.75rem; border: 1px solid rgba(148, 163, 184, 0.2); background: rgba(15, 23, 42, 0.03);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">{{ __('APPLICATION RUNTIME') }}</span>
                        <x-filament::badge color="info" size="xs">{{ __('ACTIVE') }}</x-filament::badge>
                    </div>
                    <div style="font-size: 0.95rem; font-weight: 800; font-family: ui-monospace, monospace;">PHP 8.4 + Octane</div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">FrankenPHP Worker Mode</div>
                </div>

                <div style="padding: 1rem; border-radius: 0.75rem; border: 1px solid rgba(148, 163, 184, 0.2); background: rgba(15, 23, 42, 0.03);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">{{ __('PRIMARY DATABASE') }}</span>
                        <x-filament::badge color="success" size="xs">{{ __('HEALTHY') }}</x-filament::badge>
                    </div>
                    <div style="font-size: 0.95rem; font-weight: 800; font-family: ui-monospace, monospace;">PostgreSQL 17</div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">UTF-8 / pg_trgm Enabled</div>
                </div>

                <div style="padding: 1rem; border-radius: 0.75rem; border: 1px solid rgba(148, 163, 184, 0.2); background: rgba(15, 23, 42, 0.03);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">{{ __('CACHE & QUEUES') }}</span>
                        <x-filament::badge color="warning" size="xs">REDIS DB0-2</x-filament::badge>
                    </div>
                    <div style="font-size: 0.95rem; font-weight: 800; font-family: ui-monospace, monospace;">Laravel Horizon</div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">Asynchronous Job Workers</div>
                </div>

                <div style="padding: 1rem; border-radius: 0.75rem; border: 1px solid rgba(148, 163, 184, 0.2); background: rgba(15, 23, 42, 0.03);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.25rem;">
                        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b;">{{ __('WEB SERVER & SSL') }}</span>
                        <x-filament::badge color="primary" size="xs">{{ __('AUTO TLS') }}</x-filament::badge>
                    </div>
                    <div style="font-size: 0.95rem; font-weight: 800; font-family: ui-monospace, monospace;">Caddy Engine</div>
                    <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.25rem;">Let's Encrypt / Reverb</div>
                </div>
            </div>
        </x-filament::section>

        <!-- 3. Terminal Log Output Screen -->
        <x-filament::section icon="heroicon-o-command-line" icon-color="gray">
            <x-slot name="heading">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-weight: 700;">{{ __('Live Execution Terminal & Pipeline Logs') }}</span>
                    @if(!empty($updateLogs))
                        <x-filament::badge :color="$isSuccess ? 'success' : 'danger'" size="sm">
                            {{ $isSuccess ? __('Pipeline Succeeded') : __('Pipeline Failed') }}
                        </x-filament::badge>
                    @else
                        <x-filament::badge color="gray" size="sm">
                            {{ __('Awaiting Execution') }}
                        </x-filament::badge>
                    @endif
                </div>
            </x-slot>

            <x-slot name="description">
                {{ __('Real-time stdout/stderr stream from migration runner, cache compiler, and Octane worker reloaders.') }}
            </x-slot>

            <!-- Console Window -->
            <div style="background: #090d16; border-radius: 0.75rem; border: 1px solid #1e293b; overflow: hidden; box-shadow: inset 0 2px 8px rgba(0,0,0,0.5);" dir="ltr">
                <!-- Terminal Titlebar -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 0.6rem 1rem; background: #0f172a; border-bottom: 1px solid #1e293b;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 9999px; background: #ef4444;"></span>
                        <span style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 9999px; background: #f59e0b;"></span>
                        <span style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 9999px; background: #10b981;"></span>
                        <span style="font-family: ui-monospace, monospace; font-size: 0.75rem; color: #94a3b8; margin-left: 0.5rem;">
                            reyhan@core-orchestrator:~$ php artisan system:update
                        </span>
                    </div>

                    <div style="font-family: ui-monospace, monospace; font-size: 0.75rem; color: #64748b;">
                        bash v5.2
                    </div>
                </div>

                <!-- Terminal Content Body -->
                <div style="padding: 1.25rem; font-family: ui-monospace, monospace; font-size: 0.825rem; line-height: 1.7; min-height: 200px; max-height: 420px; overflow-y: auto; color: #e2e8f0;">
                    @if(empty($updateLogs))
                        <div style="padding: 3rem 1rem; text-align: center; color: #64748b;">
                            <div style="font-size: 1.5rem; margin-bottom: 0.5rem;">⚡</div>
                            <div style="font-weight: 600; color: #94a3b8;">{{ __('No update pipeline has been triggered yet in this session.') }}</div>
                            <div style="font-size: 0.75rem; margin-top: 0.5rem;">
                                {{ __('Click the "Execute Safe Update" button above or run from CLI:') }}
                            </div>
                            <div style="margin-top: 0.75rem; display: inline-block; padding: 0.4rem 0.8rem; background: #0f172a; border: 1px solid #1e293b; border-radius: 0.5rem; color: #38bdf8;">
                                docker compose exec app php artisan system:update
                            </div>
                        </div>
                    @else
                        @foreach($updateLogs as $index => $log)
                            <div style="display: flex; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.35rem;">
                                <span style="color: #475569; user-select: none; font-size: 0.75rem; min-width: 1.5rem;">{{ sprintf('%02d', $index + 1) }}</span>
                                <span style="color: #38bdf8; user-select: none;">➜</span>
                                
                                @if(str_starts_with($log, '['))
                                    <span style="color: #fbbf24; font-weight: 700;">{{ $log }}</span>
                                @elseif(str_starts_with($log, '✔') || str_starts_with($log, '🎉'))
                                    <span style="color: #34d399; font-weight: 600;">{{ $log }}</span>
                                @elseif(str_starts_with($log, '✖'))
                                    <span style="color: #f87171; font-weight: 800;">{{ $log }}</span>
                                @elseif(str_starts_with($log, '⚠'))
                                    <span style="color: #fb923c; font-weight: 600;">{{ $log }}</span>
                                @else
                                    <span style="color: #cbd5e1;">{{ $log }}</span>
                                @endif
                            </div>
                        @endforeach
                    @endif
                </div>

                @if(!empty($lastUpdateMessage))
                    <div style="padding: 0.75rem 1.25rem; background: #0f172a; border-top: 1px solid #1e293b; display: flex; align-items: center; justify-content: space-between; font-size: 0.775rem;">
                        <span style="color: #94a3b8;">{{ __('Pipeline Status Message:') }}</span>
                        <span style="font-weight: 700; color: {{ $isSuccess ? '#34d399' : '#f87171' }}; font-family: ui-monospace, monospace;">
                            {{ $lastUpdateMessage }}
                        </span>
                    </div>
                @endif
            </div>
        </x-filament::section>

        <!-- 4. CLI & Management Cheatsheet (Native Filament Section) -->
        <x-filament::section collapsed icon="heroicon-o-information-circle">
            <x-slot name="heading">
                <span style="font-weight: 600;">{{ __('CLI Maintenance Cheatsheet & Terminal Shortcuts') }}</span>
            </x-slot>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; font-family: ui-monospace, monospace; font-size: 0.8rem;">
                <div style="padding: 0.75rem; border-radius: 0.5rem; background: rgba(15, 23, 42, 0.04); border: 1px solid rgba(148, 163, 184, 0.15);">
                    <div style="font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">{{ __('RUN SAFE CORE UPDATE (CLI)') }}</div>
                    <code style="color: #0284c7;">php artisan system:update</code>
                </div>

                <div style="padding: 0.75rem; border-radius: 0.5rem; background: rgba(15, 23, 42, 0.04); border: 1px solid rgba(148, 163, 184, 0.15);">
                    <div style="font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">{{ __('MANUAL DATABASE BACKUP') }}</div>
                    <code style="color: #059669;">php artisan backup:run --only-db</code>
                </div>

                <div style="padding: 0.75rem; border-radius: 0.5rem; background: rgba(15, 23, 42, 0.04); border: 1px solid rgba(148, 163, 184, 0.15);">
                    <div style="font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">{{ __('PURGE & REBUILD CACHE') }}</div>
                    <code style="color: #d97706;">php artisan optimize:clear && php artisan optimize</code>
                </div>
            </div>
        </x-filament::section>

    </div>
</x-filament-panels::page>
