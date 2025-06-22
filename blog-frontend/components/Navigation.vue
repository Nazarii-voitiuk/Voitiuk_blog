<template>
    <nav class="bg-gray-50 px-6 py-3 flex gap-6">
        <NuxtLink to="/BlogPosts" class="hover:underline" :class="{ 'font-bold underline': $route.path === '/BlogPosts' }">Plain Table</NuxtLink>
        <NuxtLink to="/BlogPostsUi" class="hover:underline" :class="{ 'font-bold underline': $route.path === '/BlogPostsUi' }">UI Table</NuxtLink>
        <NuxtLink to="/blog/categories" class="hover:underline" :class="{ 'font-bold underline': $route.path === '/blog/categories' }">Categories</NuxtLink>
    </nav>
</template>


<script setup lang="ts">
import { useRoute } from 'vue-router'
const route = useRoute()
</script>
