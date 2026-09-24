@php
    $options = $getTreeOptions();
    $isDisabled = $isDisabled();
    $isSearchable = $isSearchable();
    $statePath = $getStatePath();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    class="fi-fo-tree-select-wrp"
>
    <div
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
            options: @js($options),
            expandedOptionValues: @js(collect($options)->where('depth', 0)->pluck('value')->values()),
            isOpen: false,
            search: '',

            init() {
                if (! this.selectedOption) {
                    return;
                }

                this.expandedOptionValues = [
                    ...new Set([...this.expandedOptionValues, ...this.selectedOption.ancestorValues]),
                ];
            },

            get selectedOption() {
                return this.options.find((option) => String(option.value) === String(this.state)) ?? null;
            },

            get visibleOptions() {
                const needle = this.search.trim().toLocaleLowerCase();

                if (needle !== '') {
                    const visibleOptionValues = new Set();

                    this.options
                        .filter((option) => option.search.includes(needle))
                        .forEach((option) => {
                            visibleOptionValues.add(option.value);
                            option.ancestorValues.forEach((ancestorValue) => visibleOptionValues.add(ancestorValue));
                        });

                    return this.options.filter((option) => visibleOptionValues.has(option.value));
                }

                return this.options.filter((option) =>
                    option.ancestorValues.every((ancestorValue) => this.isExpanded(ancestorValue)),
                );
            },

            open() {
                if (@js($isDisabled)) {
                    return;
                }

                this.isOpen = true;
                this.$nextTick(() => this.$refs.search?.focus());
            },

            close() {
                this.isOpen = false;
                this.search = '';
            },

            choose(option) {
                if (option.disabled) {
                    return;
                }

                this.state = option.value;
                this.close();
            },

            choosePlaceholder() {
                this.state = null;
                this.close();
            },

            isExpanded(optionValue) {
                return this.expandedOptionValues.some((value) => String(value) === String(optionValue));
            },

            toggle(optionValue) {
                if (this.isExpanded(optionValue)) {
                    this.expandedOptionValues = this.expandedOptionValues.filter(
                        (value) => String(value) !== String(optionValue),
                    );

                    return;
                }

                this.expandedOptionValues.push(optionValue);
            },
        }"
        x-on:click.outside="close()"
        x-on:keydown.escape.stop="close()"
        class="relative fi-tree-select-container"
        {{ $getExtraAttributeBag() }}
    >
        {{-- Trigger Input Wrapper --}}
        <x-filament::input.wrapper
            :disabled="$isDisabled"
            :valid="! $errors->has($statePath)"
            class="fi-fo-tree-select cursor-pointer transition duration-75"
        >
            <div
                id="{{ $getId() }}"
                role="combobox"
                aria-haspopup="tree"
                aria-controls="{{ $getId() }}-tree"
                x-bind:aria-expanded="isOpen"
                x-on:click="isOpen ? close() : open()"
                class="flex w-full min-h-[2.5rem] items-center justify-between gap-x-2 px-3 py-1.5 text-start cursor-pointer select-none"
            >
                <div class="flex items-center gap-x-2 min-w-0 flex-1">
                    <x-filament::icon
                        icon="heroicon-o-folder"
                        class="size-4 shrink-0 text-gray-400 dark:text-gray-500"
                    />

                    <span
                        class="truncate text-sm font-medium"
                        x-bind:class="selectedOption ? 'text-gray-950 dark:text-white' : 'text-gray-400 dark:text-gray-500 font-normal'"
                        x-text="selectedOption?.selectedLabel ?? @js($getPlaceholder())"
                    ></span>
                </div>

                <div class="flex items-center gap-x-1 shrink-0">
                    <button
                        x-show="state !== null && state !== '' && ! @js($isDisabled)"
                        x-on:click.stop="choosePlaceholder()"
                        type="button"
                        title="پاک کردن انتخاب"
                        class="rounded-md p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/10 transition"
                    >
                        <x-filament::icon icon="heroicon-m-x-mark" class="size-3.5" />
                    </button>

                    <div class="text-gray-400 dark:text-gray-500 transition-transform duration-200" x-bind:class="{ 'rotate-180': isOpen }">
                        <x-filament::icon icon="heroicon-m-chevron-down" class="size-4" />
                    </div>
                </div>
            </div>
        </x-filament::input.wrapper>

        {{-- Dropdown Panel --}}
        <div
            x-cloak
            x-show="isOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1 scale-98"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-1 scale-98"
            class="fi-tree-dropdown absolute z-50 mt-1.5 w-full overflow-hidden rounded-xl border border-gray-200 dark:border-white/15 bg-white dark:bg-gray-900 shadow-2xl ring-1 ring-black/5 dark:ring-white/10"
        >
            {{-- Search Bar --}}
            @if ($isSearchable)
                <div class="border-b border-gray-100 dark:border-white/10 bg-gray-50/70 dark:bg-white/5 p-2">
                    <div class="relative flex items-center">
                        <x-filament::icon
                            icon="heroicon-m-magnifying-glass"
                            class="absolute start-3 size-4 text-gray-400 dark:text-gray-500 pointer-events-none"
                        />
                        <input
                            x-ref="search"
                            x-model.debounce.150ms="search"
                            type="search"
                            placeholder="{{ $getSearchPrompt() ?? 'جستجو در دسته‌بندی‌ها...' }}"
                            autocomplete="off"
                            class="w-full rounded-lg border-0 bg-white dark:bg-gray-800 py-1.5 ps-9 pe-8 text-sm text-gray-900 dark:text-white ring-1 ring-inset ring-gray-200 dark:ring-white/10 placeholder:text-gray-400 focus:ring-2 focus:ring-primary-600 dark:focus:ring-primary-500 focus:outline-none"
                        />
                        <button
                            x-show="search.length > 0"
                            x-on:click="search = ''; $refs.search.focus()"
                            type="button"
                            class="absolute end-2.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                        >
                            <x-filament::icon icon="heroicon-m-x-mark" class="size-4" />
                        </button>
                    </div>
                </div>
            @endif

            {{-- Tree Items List --}}
            <div
                id="{{ $getId() }}-tree"
                role="tree"
                aria-label="{{ $getTreeLabel() }}"
                class="fi-tree-scroll-area max-h-72 overflow-y-auto overscroll-contain p-1.5 space-y-0.5"
            >
                {{-- Root / Placeholder Item --}}
                @if ($canSelectPlaceholder())
                    <div
                        role="treeitem"
                        x-on:click="choosePlaceholder()"
                        x-bind:class="String(state ?? '') === ''
                            ? 'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300 font-semibold'
                            : 'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5'"
                        class="flex w-full items-center gap-x-2.5 rounded-lg px-2.5 py-1.5 text-start text-sm cursor-pointer transition"
                    >
                        <x-filament::icon
                            icon="heroicon-o-home"
                            class="size-4 shrink-0 text-gray-400 dark:text-gray-500"
                        />
                        <span class="truncate">{{ $getPlaceholder() }}</span>

                        <x-filament::icon
                            icon="heroicon-m-check"
                            x-show="String(state ?? '') === ''"
                            class="ms-auto size-4 shrink-0 text-primary-600 dark:text-primary-400"
                        />
                    </div>
                @endif

                {{-- Hierarchy Tree Rows --}}
                <template x-for="option in visibleOptions" :key="`${typeof option.value}:${option.value}`">
                    <div
                        role="treeitem"
                        x-bind:aria-level="option.depth + 1"
                        x-bind:aria-expanded="option.hasChildren ? isExpanded(option.value) : null"
                        x-bind:aria-disabled="option.disabled"
                        x-bind:class="{
                            'bg-primary-50 text-primary-700 dark:bg-primary-500/15 dark:text-primary-300 font-semibold': String(option.value) === String(state),
                            'text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5': ! option.disabled && String(option.value) !== String(state),
                            'cursor-not-allowed opacity-50 text-gray-400 dark:text-gray-600': option.disabled,
                        }"
                        x-bind:style="'padding-inline-start: ' + (option.depth * 1.25 + 0.25) + 'rem;'"
                        class="flex items-center rounded-lg py-1 pe-2 text-sm transition"
                    >
                        {{-- Branch Guide for Sub-categories --}}
                        <template x-if="option.depth > 0">
                            <span class="me-1 text-xs text-gray-400 dark:text-gray-600 select-none">↳</span>
                        </template>

                        {{-- Expand/Collapse Arrow --}}
                        <button
                            x-show="option.hasChildren"
                            type="button"
                            x-on:click.stop="toggle(option.value)"
                            x-bind:aria-label="isExpanded(option.value) ? @js(__('Collapse')) : @js(__('Expand'))"
                            class="grid size-6 shrink-0 place-items-center rounded-md text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-200/60 dark:hover:bg-white/10 transition"
                        >
                            <x-filament::icon
                                icon="heroicon-m-chevron-left"
                                x-bind:class="isExpanded(option.value) ? '-rotate-90' : 'rtl:rotate-0 ltr:rotate-180'"
                                class="size-3.5 transition-transform duration-150"
                            />
                        </button>

                        <span x-show="! option.hasChildren" aria-hidden="true" class="size-6 shrink-0"></span>

                        {{-- Item Content --}}
                        <div
                            x-on:click="choose(option)"
                            x-bind:class="option.disabled ? 'cursor-not-allowed' : 'cursor-pointer'"
                            class="flex min-w-0 flex-1 items-center gap-x-2 py-1 px-1.5 text-start select-none"
                        >
                            <x-filament::icon
                                x-bind:icon="option.hasChildren ? (isExpanded(option.value) ? 'heroicon-o-folder-open' : 'heroicon-o-folder') : 'heroicon-o-tag'"
                                class="size-4 shrink-0 text-primary-500/80"
                            />

                            <span class="min-w-0 flex-1 truncate" x-text="option.label"></span>

                            <span
                                x-show="option.description"
                                class="shrink-0 text-xs text-gray-400 dark:text-gray-500"
                                x-text="option.description"
                            ></span>

                            <x-filament::icon
                                icon="heroicon-m-check"
                                x-show="String(option.value) === String(state)"
                                class="size-4 shrink-0 text-primary-600 dark:text-primary-400"
                            />
                        </div>
                    </div>
                </template>

                {{-- Empty Search Results --}}
                <div
                    x-show="visibleOptions.length === 0"
                    class="flex flex-col items-center justify-center gap-y-2 py-8 text-center text-sm text-gray-400 dark:text-gray-500"
                >
                    <x-filament::icon icon="heroicon-o-magnifying-glass" class="size-6 text-gray-300 dark:text-gray-600" />
                    <span x-text="search === '' ? @js((string) $getNoOptionsMessage()) : @js((string) $getNoSearchResultsMessage())"></span>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>

<style>
.fi-tree-dropdown {
    background-color: #ffffff !important;
    color: #111827 !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important;
}

.dark .fi-tree-dropdown {
    background-color: #111827 !important;
    color: #f3f4f6 !important;
    border-color: rgba(255, 255, 255, 0.12) !important;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.6), 0 8px 10px -6px rgba(0, 0, 0, 0.5) !important;
}

.fi-tree-scroll-area::-webkit-scrollbar {
    width: 5px;
}
.fi-tree-scroll-area::-webkit-scrollbar-track {
    background: transparent;
}
.fi-tree-scroll-area::-webkit-scrollbar-thumb {
    background: rgba(156, 163, 175, 0.4);
    border-radius: 9999px;
}
.dark .fi-tree-scroll-area::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
}
</style>
