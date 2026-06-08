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
    <div class="px-3 py-3">
    <p>Instructions IA</p>
        <textarea v-model="assistantContent" rows="4" cols="50" />
    </div>
    <br/>

    <div class="px-3 py-3">
    <p>Who are you?</p>
        <textarea v-model="userContent" rows="4" cols="50" />
    </div>
</template>