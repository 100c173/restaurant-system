<x-filament-panels::page>

    {{-- Feature summary card --}}
    <x-filament::section>
        <div class="flex flex-wrap items-center justify-between gap-6">
            <div class="flex items-center gap-4">

                <div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ $this->record->name }}
                    </p>
                    <p class="text-sm text-gray-400 dark:text-gray-500">
                        <code class="font-mono">{{ $this->record->code }}</code>
                        &nbsp;&middot;&nbsp;
                        <x-filament::badge color="{{ match($this->record->type) {
                            'boolean' => 'warning',
                            'limit'   => 'info',
                            default   => 'gray',
                        } }}" size="sm">
                            {{ ucfirst($this->record->type) }}
                        </x-filament::badge>
                    </p>
                    @if ($this->record->description)
                        <p class="text-xs text-gray-400 mt-1">{{ $this->record->description }}</p>
                    @endif
                </div>
            </div>

            <x-filament::badge color="primary" size="lg">
                {{ $this->record->plans()->count() }} plan(s)
            </x-filament::badge>
        </div>
    </x-filament::section>

    {{-- Plans table --}}
    {{ $this->table }}

</x-filament-panels::page>