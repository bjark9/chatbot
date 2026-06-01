<script setup>
import { useForm } from '@inertiajs/vue3'
import { ask } from '@/actions/App/Http/Controllers/AskController'
import MarkdownIt from 'markdown-it'
import hljs from 'highlight.js'
import 'highlight.js/styles/github-dark.css'
import AskSidebarLayout from './AskSidebarLayout.vue'

const props = defineProps({
    models: Array,
    selectedModel: String,
    message: String,
    response: String,
    error: String,
})

const form = useForm({
    message: props.message ?? '',
    model: props.selectedModel,
})

const submit = () => {
    form.post(ask())
}

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
        <AskSidebarLayout :models="props.models" :selectedModel="form.model"  />

        <!-- Main content -->
        <div class="max-w-3xl mx-auto px-4 py-10 space-y-6">

            <!-- Model selector -->
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Model
                </label>
                <select 
                    v-model="form.model"
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                    <option v-for="model in models" :key="model.name" :value="model.id">
                        {{ model.name }}
                    </option>
                </select>
            </div>

            <!-- Form -->
            <form @submit.prevent="submit" class="space-y-3">
                <textarea 
                    v-model="form.message"
                    rows="5"
                    placeholder="Ask something..."
                    class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-4 py-3 text-sm shadow-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
                <button 
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2 px-4 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-medium rounded-lg transition-colors duration-150"
                >
                    {{ form.processing ? 'Thinking...' : 'Ask' }}
                </button>
            </form>

            <!-- Response -->
            <div 
                v-if="props.response"
                class="prose dark:prose-invert prose-slate max-w-none bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 shadow-sm"
                v-html="md.render(props.response)"
            />

            <!-- Error -->
            <div 
                v-if="props.error"
                class="flex items-start gap-3 text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4 text-sm"
            >
                <span class="font-medium">Error:</span> {{ props.error }}
            </div>

        </div>
    </div>
</template>