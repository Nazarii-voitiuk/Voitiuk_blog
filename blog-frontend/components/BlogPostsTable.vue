<script setup lang="ts">
import { defineProps, defineEmits, h } from 'vue'
import type { DataTableColumns } from 'naive-ui'

interface Post {
    id: number
    title: string
    published_at: string | null
    user: { name: string }
    category: { title: string }
}

const props = defineProps<{
    posts: Post[]
    loading: boolean
    page: number
    perPage: number
    total: number
}>()

const emit = defineEmits<{
    (e: 'update:page', page: number): void
    (e: 'update:page-size', size: number): void
}>()

const columns: DataTableColumns<Post> = [
    { title: '#', key: 'id', width: 60 },
    { title: 'Автор', key: 'user.name' },
    { title: 'Категорія', key: 'category.title' },
    {
        title: 'Заголовок',
        key: 'title',
        render(row) {
            return h(
                'a',
                { href: `/blog/posts/${row.id}`, class: 'text-blue-600 hover:underline' },
                row.title
            )
        }
    },
    { title: 'Дата публікації', key: 'published_at' }
]
</script>

<template>
    <n-data-table
        :columns="columns"
        :data="props.posts"
        :loading="props.loading"
    />
    <n-pagination
        :page="props.page"
        :page-size="props.perPage"
        :item-count="props.total"
        :page-sizes="[10, 20, 50, 100]"
        show-size-picker
        @update:page="page => emit('update:page', page)"
        @update:page-size="size => emit('update:page-size', size)"
    />
</template>
