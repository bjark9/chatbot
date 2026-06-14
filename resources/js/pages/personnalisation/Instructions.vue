<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    assistant_instructions: String,
    user_instructions: String,
})

const assistantContent = ref(props.assistant_instructions ?? '')
const userContent = ref(props.user_instructions ?? '')

let debounceTimer = null

function autosave() {
    clearTimeout(debounceTimer)
    debounceTimer = setTimeout(() => {
        router.post('/personnalisation/instructions', {
            assistant_instructions: assistantContent.value,
            user_instructions: userContent.value,
        }, {
            preserveScroll: true,
            preserveState: true,
        })
    }, 1000)
}

watch([assistantContent, userContent], () => {
    autosave()
})
</script>

<template>
    <div class="flex flex-col gap-6 max-w-2xl px-20 py-10">

        <div class="bg-white dark:bg-white/5 border border-black/10 dark:border-white/10 rounded-xl p-5">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                Instructions IA
            </label>
            <textarea
                v-model="assistantContent"
                rows="4"
                placeholder="Enter assistant instructions…"
                class="w-full resize-y text-sm leading-relaxed bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 border border-black/10 dark:border-white/10 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            />
            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Sets the assistant's behavior and tone.</p>
        </div>

        <div class="bg-white dark:bg-white/5 border border-black/10 dark:border-white/10 rounded-xl p-5">
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">
                Who are you?
            </label>
            <textarea
                v-model="userContent"
                rows="4"
                placeholder="Describe yourself…"
                class="w-full resize-y text-sm leading-relaxed bg-transparent text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600 border border-black/10 dark:border-white/10 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            />
            <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">Helps the assistant personalise its responses.</p>
        </div>

        <div class="flex items-center gap-2 text-xs text-gray-400 dark:text-gray-500">
            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
            </svg>
            Saved automatically
        </div>

    </div>
</template>