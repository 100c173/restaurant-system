<x-filament-panels::page>

    <div class="max-w-xl mx-auto space-y-4">

        {{-- ── Header banner ── --}}
        <div class="rounded-xl bg-gradient-to-br from-primary-600 to-primary-700 px-4 py-3.5 text-white shadow-md">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 rounded-lg bg-white/15 p-2 mt-0.5">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                              stroke="currentColor" stroke-width="1.8"
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                
                <div>
                    <p class="text-sm font-semibold text-white/90 mb-0.5">How to complete your payment</p>
                    <p class="text-sm text-white/70 leading-relaxed">
                        Pay via <strong class="text-white font-semibold">ShamCash</strong> using the account code or barcode below.
                        Once transferred, enter your transaction number — your subscription will be activated within
                        <strong class="text-white font-semibold">2 hours</strong>.
                    </p>
                </div>
            </div>
        </div>

        <br>

        {{-- ── Grid: Plan summary + Payment details ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Plan summary --}}
            <x-filament::section>
                <div class="space-y-4 h-full">
                    <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                        Selected Plan
                    </p>

                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xl font-bold text-gray-900 dark:text-white leading-tight">
                                {{ $this->plan->name }}
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                Billed {{ $this->plan->billing_interval }}
                            </p>
                        </div>
                        <x-filament::badge color="primary" size="sm">
                            {{ ucfirst($this->plan->billing_interval) }}
                        </x-filament::badge>
                    </div>

                    <div class="pt-3 border-t border-gray-100 dark:border-gray-700/60">
                        <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Total due</p>
                        <p class="text-2xl font-extrabold text-primary-600 dark:text-primary-400 tracking-tight">
                            ${{ number_format($this->plan->price, 2) }}
                        </p>
                    </div>
                </div>
            </x-filament::section>

            <br>

            {{-- ShamCash payment details --}}
            @if ($this->shamCashAccount)
                @php $account = $this->shamCashAccount; @endphp

                <x-filament::section>
                    <div class="space-y-4 h-full">
                        <p class="text-xs font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">
                            ShamCash Account
                        </p>

                        {{-- Barcode (small, centered) --}}
                        <div class="flex justify-center">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-2 shadow-sm w-16 h-16 flex items-center justify-center">
                                <img
                                    src="{{ $account->barcode_image }}"
                                    alt="ShamCash barcode"
                                    class="w-full h-full object-contain"
                                />
                            </div>
                        </div>

                        {{-- Account code --}}
                        <div class="flex items-center justify-between rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-3 gap-3">
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400 dark:text-gray-500 mb-0.5">Account code</p>
                                <p class="text-base font-mono font-bold text-gray-900 dark:text-white tracking-widest truncate">
                                    {{ $account->code }}
                                </p>
                            </div>

                            <button
                                x-data
                                x-on:click="
                                    navigator.clipboard.writeText('{{ $account->code }}');
                                    $tooltip('Copied!', { timeout: 1500 });
                                "
                                class="flex-shrink-0 rounded-lg p-1.5 text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/30 dark:hover:text-primary-400 transition-colors"
                                title="Copy code"
                            >
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none">
                                    <path d="M8 7V5a2 2 0 012-2h7a2 2 0 012 2v7a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v7a2 2 0 002 2h7a2 2 0 002-2v-2"
                                          stroke="currentColor" stroke-width="1.6"
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </x-filament::section>
            @else
                <x-filament::section>
                    <div class="flex flex-col items-center justify-center h-full py-6 text-center gap-2">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" class="text-gray-300 dark:text-gray-600">
                            <path d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"
                                  stroke="currentColor" stroke-width="1.5"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <p class="text-sm text-gray-400 dark:text-gray-500">
                            Payment account not configured.<br>Please contact the admin.
                        </p>
                    </div>
                </x-filament::section>
            @endif

        </div>

        <br>

        {{-- ── Transaction number ── --}}
        <x-filament::section>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5 items-start">

                {{-- Label + hint (left column) --}}
                <div class="sm:col-span-1">
                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        Transaction number
                    </p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1 leading-relaxed">
                        Found in your ShamCash app after the transfer is complete.
                    </p>
                </div>

                {{-- Input (right columns) --}}
                <div class="sm:col-span-2">
                    <x-filament::input.wrapper :valid="! $errors->has('transactionNumber')">
                        <x-filament::input
                            type="text"
                            wire:model="transactionNumber"
                            placeholder="e.g. TXN-20240501-8823"
                            autofocus
                        />
                    </x-filament::input.wrapper>

                    @error('transactionNumber')
                        <p class="text-xs text-danger-500 mt-1.5 flex items-center gap-1">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" class="shrink-0">
                                <path d="M12 9v2m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"
                                      stroke="currentColor" stroke-width="2"
                                      stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

            </div>
        </x-filament::section>

        <br>

        {{-- ── Actions ── --}}
        <div class="flex items-center justify-between pt-1">

            <x-filament::button
                wire:click="back"
                color="gray"
                icon="heroicon-o-arrow-left"
                size="sm"
            >
                Back
            </x-filament::button>

            <x-filament::button
                wire:click="submit"
                wire:loading.attr="disabled"
                color="primary"
                icon="heroicon-o-paper-airplane"
                size="md"
            >
            
                <span wire:loading.remove wire:target="submit">Submit request</span>
                <span wire:loading wire:target="submit">Submitting…</span>
            </x-filament::button>

        </div>

    </div>

</x-filament-panels::page>