<script setup>

const props = defineProps(
    {
        conversations : Array,
    }
)

</script>

<template>
    <select 
        v-model="form.model"
        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
    >
        <option v-for="conersation in conversations" :key="conversation.id" :value="conversation.id">
            {{ conversation.title }}
        </option>
    </select>
</template>