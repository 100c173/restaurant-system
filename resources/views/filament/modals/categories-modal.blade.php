<div class="space-y-2">
    @forelse ($categories as $category)
        <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">

            <div class="flex items-center gap-2.5">
                {{-- Image or fallback icon --}}
                @if ($category->img_path)
                    <img
                        src="{{ $category->img_path}}" 
                        alt="{{ $category->name }}"
                        class="h-8 w-8 flex-shrink-0 rounded-full object-cover"
                    />
                @endif

                {{-- Name + Description --}}
                <div class="min-w-0">
                    <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">
                        {{ $category->name }}
                    </p>
                    @if ($category->description)
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            {{ $category->description }}
                        </p>
                    @endif
                </div>
            </div>

            {{-- Status badge --}}
            @if ($category->is_active)
                <x-filament::badge color="success" class="flex-shrink-0">Active</x-filament::badge>
            @else
                <x-filament::badge color="danger" class="flex-shrink-0">Inactive</x-filament::badge>
            @endif

        </div>
    @empty
        <div class="py-6 text-center text-gray-400 dark:text-gray-500">
            <x-heroicon-o-tag class="mx-auto mb-2 h-7 w-7" />
            <p class="text-sm">No categories assigned to this menu yet.</p>
        </div>
    @endforelse
</div>