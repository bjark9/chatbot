<script setup>
import { ref, watch } from 'vue'
// axios -> js library that let's frontend make HTTP requests to backend without reloading whole page
// User clicks → axios sends request in background → only the data updates → page stays the same
import axios from 'axios'

const props = defineProps({
    conversations: Array,
})

const selectedId = ref(null)
const messages = ref([])
const loading = ref(false)

function selectConversation(id)
{
    selectedId.value = id
}

// Watch for when the user selects a different conversation
// Triggers automatically when "selectId" changes
watch(selectedId, async (newId) => {
    if (!newId) return

    loading.value = true
    
    // Add try and catch or else the loader spinner stays forever 
    try {
        const response = await axios.get(`/conversations/${newId}`)
        messages.value = response.data.messages
    } catch (e) {
        messages.value = []
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="flex gap-6 p-4">

        <!-- Conversation list (left column) -->
        <div class="w-64 flex flex-col gap-1 h-screen overflow-y-auto">
            <button
                v-for="conversation in conversations"
                :key="conversation.id"
                @click="selectConversation(conversation.id)"
                class="w-full text-left px-4 py-3 rounded-lg text-sm transition-colors"
                :class="selectedId === conversation.id
                    ? 'bg-gray-200 dark:bg-gray-700 font-semibold'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300'"
            >
                {{ conversation.title }}
            </button>
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