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

// Watch for when the user selects a different conversation
// Triggers automatically when "selectId" changes
watch(selectedId, async (newId) => {
    if (!newId) return

    loading.value = true
    const response = await axios.get(`/conversations/${newId}`)
    messages.value = response.data.messages  // ② grab messages from the response
    loading.value = false
})

console.log(props.conversations)
</script>

<template>

    <!-- Conversations dropdown button -->
    <!-- v-model = binds selected conversation to variable "selectedId" -->
    <select 
        v-model="selectedId" 
        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
        <option v-for="conversation in conversations" :key="conversation.id" :value="conversation.id">
            {{ conversation.title }}
        </option>
    </select>

    <p v-if="loading" class="text-sm text-gray-400 mt-4">Loading messages...</p>

    <!-- Messages -->
    <div v-else-if="messages.length > 0" class="mt-4 flex flex-col gap-3">
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
    </div>

    <p v-else-if="selectedId && !loading" class="text-sm text-gray-400 mt-4">
        No messages in this conversation yet.
    </p>

</template>