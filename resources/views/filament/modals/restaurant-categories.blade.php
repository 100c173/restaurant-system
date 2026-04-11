<div class="flex flex-wrap gap-2 p-2">
    @forelse ($categories as $category)
        <x-filament::badge color="info">
            {{ $category->name }}
        </x-filament::badge>
    @empty
        <p class="text-sm text-gray-500">No categories assigned.</p>
    @endforelse
</div>