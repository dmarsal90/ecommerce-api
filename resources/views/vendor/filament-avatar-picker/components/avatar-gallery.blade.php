<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $files = \Illuminate\Support\Facades\Storage::disk('public')->files('avatars');
        
        $avatars = collect($files)
            ->filter(fn($file) => preg_match('/\.(png|jpg|jpeg|webp)$/i', $file))
            ->sortBy(function ($file) {
                $filename = strtolower(basename($file));
                if (str_starts_with($filename, '3d')) return 1;
                if (str_starts_with($filename, 'bluey') || str_starts_with($filename, 'notion')) return 3;
                return 2;
            })
            ->map(function ($file) {
                return [
                    'path' => $file,
                    'url' => \Illuminate\Support\Facades\Storage::disk('public')->url($file),
                ];
            })
            ->values();
    @endphp

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5);
            border-radius: 20px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background-color: rgba(107, 114, 128, 0.8);
        }
    </style>
    <div x-data="{ 
        state: $wire.$entangle('{{ $getStatePath() }}'),
        allAvatars: @js($avatars),
        limit: 100,
        get visibleAvatars() { return this.allAvatars.slice(0, this.limit); },
        loadMore() { this.limit += 50; }
    }">
        <div 
            style="min-height: 250px; max-height: 50vh; overflow-y: auto; overflow-x: hidden; display: grid; grid-template-columns: repeat(auto-fill, minmax(70px, 1fr)); gap: 12px; justify-content: center; align-content: start;"
            class="p-2 sm:p-4 custom-scrollbar"
            @scroll="if ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 20) loadMore()"
        >
            <button 
                type="button" 
                @click="state = null; $dispatch('update-avatar-state', { remove_avatar: true, preset_avatar: null })"
                :style="state === null || state === '' 
                    ? 'aspect-ratio: 1/1; width: 100%; max-width: 100px; border-style: solid; border-width: 4px; border-color: #ef4444; transform: scale(1.05); box-shadow: 0px 4px 10px rgba(239, 68, 68, 0.3); transition: all 0.2s; background: #fef2f2;'
                    : 'aspect-ratio: 1/1; width: 100%; max-width: 100px; border: 1px solid #e5e7eb; transition: all 0.2s; background: #f9fafb;'"
                class="relative rounded-full overflow-hidden shrink-0 focus:outline-none mx-auto flex items-center justify-center"
            >
                <div class="text-center">
                    <svg class="mx-auto text-3xl text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-medium">Remove</p>
                </div>
            </button>
            
            <template x-for="avatar in visibleAvatars" :key="avatar.path">
                <button 
                    type="button" 
                    @click="state = avatar.path; $dispatch('update-avatar-state', { preset_avatar: avatar.path, remove_avatar: false })"
                    :style="state === avatar.path 
                        ? 'aspect-ratio: 1/1; width: 100%; max-width: 100px; border-style: solid; border-width: 4px; border-color: #f97316; border-color: var(--primary-600); border-color: rgb(var(--primary-600)); transform: scale(1.05); box-shadow: 0px 4px 10px rgba(0,0,0,0.2); transition: all 0.2s;' 
                        : 'aspect-ratio: 1/1; width: 100%; max-width: 100px; border: 1px solid #e5e7eb; transition: all 0.2s;'"
                    class="relative rounded-full overflow-hidden shrink-0 focus:outline-none mx-auto"
                >
                    <img :src="avatar.url" alt="Avatar" class="w-full h-full object-cover">
                </button>
            </template>
        </div>
        
        <template x-if="allAvatars.length === 0">
            <div class="text-sm text-gray-500 text-center py-4">{{ __('filament-avatar-picker::avatar.no_avatars') }}</div>
        </template>
    </div>
</x-dynamic-component>