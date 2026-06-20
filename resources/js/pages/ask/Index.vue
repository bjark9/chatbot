<script setup>
import { ref, computed, watch } from 'vue'
import { useForm, router } from '@inertiajs/vue3'
import MarkdownIt from 'markdown-it'
import hljs from 'highlight.js'
import 'highlight.js/styles/github-dark.css'
import AskSidebarLayout from './AskSidebarLayout.vue'
import { useStream } from '@laravel/stream-vue'; // Gère la connexion SSE et la récpetion des chunks

// State
const temperature = ref(1.0);
const reasoningEffort = ref<'low' | 'medium' | 'high' | null>(null);

// Envoyé par AskController
const props = defineProps({
    models: Array,
    conversation: Object, // { id, title, messages: [{ id, role, content, is_error }] }
    selectedModel: String,
    messages: {
        type: Array,
        default: () => [],
    },
})

// Soumission HTTP, la gestion des erreurs de validation Laravel, et l'état de chargement déjà intégrés
const form = useForm({
    message: '',
    model: props.selectedModel ?? props.models?.[0]?.id ?? '',
    messages: props.messages,
})

/**
 * useStream hook - Le hook concatène automatiquement dans `data`
 * Le backend envoie du texte avec marqueurs [REASONING]...[/REASONING]
 * Appelle 'ask-stream' -> le flux de réponse IA est géré par AskStreamController
 */
const { data, isStreaming, send} = useStream(
    '/ask-stream',
    {
        onData: () => {
            // Callback appelé pour chaque chunk reçu
        },
        onFinish: () => {
            // data resets itselfs automatically on the next send() call            
            // Trigger Inertia reload to pull the new assistant message into messageList
            router.reload({ 
                only: ['conversation']
            })
        },
        onError: (err) => {
            console.error('Erreur streaming:', err);
        },
    },
);

const actionUrl = computed(() => {
    return props.conversation?.id ? `/ask/${props.conversation.id}/messages` : '/ask'
})

const submit = () => {
    if (!form.message.trim()) return;

    // Capture the message text before resetting or posting
    const userPrompt = form.message;

    // STEP 1: Immediately tell Inertia to save the USER'S question to the database
    form.post(actionUrl.value, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('message'); // Clear the input field immediately
            
            // STEP 2: The user's question is now safely in 'messageList'. 
            // NOW start the stream to get the AI's response.
            send({
                message: userPrompt,
                model: form.model,
                temperature: temperature.value,
                reasoning_effort: reasoningEffort.value,
                conversation_id: props.conversation?.id
            });
        },
    });
};

/**
 * Extrait le contenu principal (sans le reasoning)
 */
const streamedContent = computed(() => {
    if (!data.value) return '';
    // Enlever les blocs [REASONING]...[/REASONING]
    return data.value
        .replace(/\[REASONING\][\s\S]*?\[\/REASONING\]/g, '')
        .trim();
});

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

            <!--
                <div class="main-content">
                    <div v-html="md.render(streamedContent)"></div>
                </div>
            -->   

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
                
            <div v-if="messageList.length > 0" class="flex flex-col gap-4">
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
            </div>

            <p v-else-if="!isStreaming" class="text-sm text-gray-400 text-center">
                No messages yet. Start the conversation!
            </p>

            <div 
                v-if="streamedContent && messageList[messageList.length - 1]?.role !== 'assistant'" 
                class="flex justify-start"
            >
                <div class="max-w-[80%] rounded-lg px-4 py-3 text-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100">
                    <div
                        class="prose dark:prose-invert prose-slate max-w-none"
                        v-html="md.render(streamedContent)"
                    />
                </div>
            </div>
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
                    <input type="file">
                </div>
            </form>

        </div>
    </div>
</template>