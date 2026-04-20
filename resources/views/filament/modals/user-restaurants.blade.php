<div class="flex flex-wrap gap-2 p-2">
    @forelse ($restaurants as $restaurant)
        <x-filament::badge color="info">
            {{ $restaurant->name }}
        </x-filament::badge>
    @empty
        <p class="text-sm text-gray-500">No restaurants assigned.</p>
    @endforelse
</div>