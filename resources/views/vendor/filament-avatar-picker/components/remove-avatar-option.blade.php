<div x-data="{ removeAvatar: {{ $hasAvatar ? 'true' : 'false' }} }">
    <button
        type="button"
        @click="removeAvatar = !removeAvatar"
        :style="removeAvatar
            ? 'aspect-ratio: 1/1; width: 100%; max-width: 100px; border-style: solid; border-width: 4px; border-color: #ef4444; transform: scale(1.05); box-shadow: 0px 4px 10px rgba(239, 68, 68, 0.3); transition: all 0.2s; background: #fef2f2;'
            : 'aspect-ratio: 1/1; width: 100%; max-width: 100px; border: 1px solid #e5e7eb; transition: all 0.2s; background: #f9fafb;'"
        class="relative rounded-full overflow-hidden shrink-0 focus:outline-none mx-auto flex items-center justify-center"
        x-ref="removeAvatarButton"
        x-init="$dispatch('update-avatar-state', { remove_avatar: removeAvatar })"
    >
        <div class="text-center">
            <svg class="mx-auto text-3xl text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="width: 48px; height: 48px;">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 font-medium">Remove</p>
        </div>
        <input type="hidden" name="remove_avatar" :value="removeAvatar" x-ref="hiddenInput">
    </button>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('avatarGallery', () => ({
                state: '',
                init() {
                    this.$watch('state', (value) => {
                        if (value) {
                            this.$dispatch('update-avatar-state', { preset_avatar: value, remove_avatar: false });
                            const removeButton = document.querySelector('[x-ref="removeAvatarButton"]');
                            if (removeButton) {
                                removeButton.__x.$data.removeAvatar = false;
                            }
                        }
                    });
                }
            }));
        });
    </script>
</div>