<script setup>
import { ref, computed, watchEffect  } from 'vue'

const props = defineProps({
    models: Array,
    selectedModel: String,
})

const isOpen = ref(false)

const currentModel = computed(() =>
    props.models?.find(m => m.id === props.selectedModel)
)
watchEffect(() => {
    console.log('models:', props.models)
    console.log('selectedModel:', props.selectedModel)
    console.log('currentModel:', currentModel.value)
})
</script>

<template>
    <!-- Toggle button -->
    <button 
        @click="isOpen = !isOpen"
        class="fixed right-4 top-4 z-50 px-3 py-2 bg-blue-500 text-white rounded shadow"
    >
        Model Information
    </button>

    <!-- Overlay -->
    <div 
        v-if="isOpen" 
        @click="isOpen = false"
        class="fixed inset-0 bg-black/30 z-40"
    />

    <!-- Sidebar panel -->
    <div 
        :class="isOpen ? 'translate-x-0' : 'translate-x-full'"
        class="fixed right-0 top-0 h-full w-72 bg-white shadow-xl z-50 transition-transform duration-300 p-6 overflow-y-auto"
    >
        <div class="flex justify-between items-center mb-6">
            <h2 class="font-semibold text-gray-700">Model Information</h2>
            <button @click="isOpen = false" class="text-gray-500 hover:text-gray-700">✕</button>
        </div>

        <div class="mb-4 border-b pb-4">
            <p class="font-medium text-gray-700">{{ currentModel.name}}</p>
            <p class="font-small text-gray-700">{{ currentModel.description}}</p>
        </div>
    </div>
</template>