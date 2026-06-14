<script setup>
import { ref, watch } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    conversations: Array,
    selectedId: [String, Number],
    messages: Array,
})

const loading = ref(false)

function selectConversation(id)
{
    router.get(`/conversations/${id}`, {}, {
        preserveState: true, // Prevents full component re-render
        preserveScroll: true // Keeps your sidebar scroll position
    })
}

function deleteConversation(id) {
    router.delete(`/conversations/${id}`)
}

</script>

<template>
    <div class="flex gap-6 p-4">

        <!-- Conversation list (left column) -->
        <div class="w-64 flex flex-col gap-2 h-screen overflow-y-auto">
            <div 
                v-for="conversation in conversations" 
                :key="conversation.id"
                class="flex items-center justify-between p-2 rounded-lg transition-colors group"
                :class="selectedId === conversation.id ? 'bg-gray-200 dark:bg-gray-700' : 'hover:bg-gray-100 dark:hover:bg-gray-800'"
            >
                <button
                    @click="selectConversation(conversation.id)"
                    class="text-left text-sm flex-1 font-medium text-gray-700 dark:text-gray-300"
                    :class="{ 'font-semibold': selectedId === conversation.id }"
                >
                    {{ conversation.title }}
                </button>

                <button
                    @click.stop="deleteConversation(conversation.id)"
                    class="bg-red-200 text-red-700 hover:bg-red-300 text-xs px-2 py-1 rounded"
                >
                    Delete
                </button>
            </div>
        </div>

        <!-- Divider -->
        <div class="w-px bg-gray-200 dark:bg-gray-700"></div>

        <!-- Messages (right column) -->
        <div class="flex-1 overflow-y-auto flex flex-col gap-3">
            <p v-if="loading" class="text-sm text-gray-400">Loading messages...</p>

            <template v-else-if="messages.length > 0">
                <div
                    v-for="message in messages"
                    :key="message.id"
                    class="p-3 rounded-lg text-sm"
                    :class="message.role === 'user'
                        ? 'bg-blue-100 dark:bg-blue-900 text-right ml-12'
                        : 'bg-gray-100 dark:bg-gray-700 text-left mr-12'"
                >
                    <span class="block font-semibold text-xs mb-1 text-gray-500">
                        {{ message.role === 'user' ? 'You' : 'AI' }}
                    </span>
                    {{ message.content }}
                </div>
            </template>

            <p v-else-if="selectedId && !loading" class="text-sm text-gray-400">
                No messages in this conversation yet.
            </p>

            <p v-else class="text-sm text-gray-400">
                Select a conversation to view messages.
            </p>
        </div>

    </div>
</template>