<x-filament-panels::page>

    {{-- Plan summary card --}}
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
                        ${{ number_format($this->record->price, 2) }}
                        / {{ $this->record->billing_interval }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <x-filament::badge
                    color="{{ $this->record->is_active ? 'success' : 'danger' }}"
                    size="lg"
                >
                    {{ $this->record->is_active ? 'Active' : 'Inactive' }}
                </x-filament::badge>

                <x-filament::badge color="primary" size="lg">
                    {{ $this->record->features()->count() }} feature(s)
                </x-filament::badge>
            </div>
        </div>
    </x-filament::section>

    {{-- Features table --}}
    {{ $this->table }}

</x-filament-panels::page>