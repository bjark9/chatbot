<script setup>
import { useForm } from '@inertiajs/vue3'
import { ask } from '@/actions/App/Http/Controllers/AskController'
import MarkdownIt from 'markdown-it'
import hljs from 'highlight.js'
import 'highlight.js/styles/github-dark.css' // ou un autre thème

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
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(str, { language: lang }).value
            } catch (__) {}
        }
        return '' // use external default escaping
    }
})

// Utilisation : md.render(props.response)

</script>

<template>
    <p>Ecrivez votre texte ici: </p>
    <textarea></textarea>
    <div 
        v-if="props.response" 
        class="prose dark:prose-invert prose-slate max-w-none"
        v-html="md.render(props.response)"
    />
</template>