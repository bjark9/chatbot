<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import MarkdownIt from 'markdown-it'
import hljs from 'highlight.js'
import 'highlight.js/styles/github-dark.css'
import AskSidebarLayout from './AskSidebarLayout.vue'

const props = defineProps({
    models: Array,
    conversation: Object, // { id, title, messages: [{ id, role, content, is_error }] }
    selectedModel: String,
    messages: {
        type: Array,
        default: () => [],
    },
})

const form = useForm({
    message: '',
    model: props.selectedModel ?? props.models?.[0]?.id ?? '',
    messages: props.messages,
})

const actionUrl = computed(() => {
    return props.conversation?.id ? `/ask/${props.conversation.id}/messages` : '/ask'
})

const submit = () => {
    form.post(actionUrl.value, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('message') // Only clear the message input, keep selected model and history
        },
    })
}

watch(
    () => props.messages,
    (newMessages) => {
        form.messages = newMessages ?? []
    },
    { immediate: true }
)

watch(
    () => props.selectedModel,
    (newModel) => {
        if (newModel) {
            form.model = newModel
        }
    },
    { immediate: true }
)

const messageList = computed(() => {
    return props.conversation?.messages ?? props.messages ?? []
})

const md = new MarkdownIt({
    highlight: (str, lang) => {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(str, { language: lang }).value
            } catch (__) {}
        }
        return ''
    }
})
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Sidebar -->
        <AskSidebarLayout :models="props.models" :selectedModel="form.model" />

        <!-- Main content -->
        <div class="max-w-3xl mx-auto px-4 py-10 flex flex-col gap-6">

            <!-- Model selector -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Model
                </label>
                <select
                    v-model="form.model"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="model in models" :key="model.id" :value="model.id">
                        {{ model.name }}
                    </option>
                </select>
            </div>

            <!-- Conversation messages -->
            <div class="flex flex-col gap-4 overflow-y-auto max-h-[60vh] pb-4">
                <template v-if="messageList.length > 0">
                    <div
                        v-for="(message, index) in messageList"
                        :key="message.id ?? index"
                        class="flex"
                        :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
                    >
                        <div
                            class="max-w-[80%] rounded-lg px-4 py-3 text-sm"
                            :class="{
                                'bg-blue-600 text-white': message.role === 'user',
                                'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100': message.role === 'assistant' && !message.is_error,
                                'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400': message.is_error,
                            }"
                        >
                            <template v-if="message.role === 'user'">
                                {{ message.content }}
                            </template>
                            <div
                                v-else
                                class="prose dark:prose-invert prose-slate max-w-none"
                                v-html="md.render(message.content)"
                            />
                        </div>
                    </div>
                </template>

                <p v-else class="text-sm text-gray-400 text-center">
                    No messages yet. Start the conversation!
                </p>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-3">
                <textarea
                    v-model="form.message"
                    rows="4"
                    placeholder="Ask something..."
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 text-sm shadow-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @keydown.ctrl.enter="submit"
                    @keydown.meta.enter="submit"
                />
                <div class="flex items-center justify-between">
                    <span class="text-xs text-gray-400">Ctrl+Enter to send</span>
                    <button
                        type="submit"
                        :disabled="form.processing || !form.message.trim()"
                        class="py-2 px-6 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors duration-150"
                    >
                        {{ form.processing ? 'Thinking...' : 'Ask' }}
                    </button>
                </div>
            </form>

        </div>
    </div>
</template>