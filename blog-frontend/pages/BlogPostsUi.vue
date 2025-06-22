<script setup lang="ts">
import { ref, watch, onMounted, h } from 'vue'
import type { DataTableColumns } from 'naive-ui'
import BlogPostForm from '~/components/BlogPostForm.vue'
import { $fetch } from 'ofetch'

interface Post {
    id: number
    title: string
    published_at: string | null
    created_at: string
    is_published: boolean
    user: { name: string }
    category: { title: string }
}

const posts   = ref<Post[]>([])
const loading = ref(false)
const page    = ref(1)
const perPage = ref(10)
const total   = ref(0)

// Для додавання
const openAdd = ref(false)
const newPost = ref({
    title: '',
    content_raw: '',
    slug: '',
    excerpt: '',
    category_id: null,
    is_published: false
})

// Реф для передачі помилок сервера в форму
const formRef = ref<InstanceType<typeof BlogPostForm> | null>(null)

// Функція для форматування дати
function formatDate(dateString: string | null) {
    if (!dateString) return 'Не опубліковано'

    const date = new Date(dateString)
    return date.toLocaleString('uk-UA', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const columns: DataTableColumns<Post> = [
    { title: '#', key: 'id', width: 60 },
    { title: 'Автор', key: 'user.name', width: 120 },
    { title: 'Категорія', key: 'category.title', width: 150 },
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
    {
        title: 'Статус',
        key: 'is_published',
        width: 100,
        render(row) {
            return h(
                'span',
                {
                    class: row.is_published
                        ? 'px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs'
                        : 'px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs'
                },
                row.is_published ? 'Опубліковано' : 'Чернетка'
            )
        }
    },
    {
        title: 'Дата створення',
        key: 'created_at',
        width: 160,
        render(row) {
            return formatDate(row.created_at)
        }
    },
    {
        title: 'Дата публікації',
        key: 'published_at',
        width: 160,
        render(row) {
            return formatDate(row.published_at)
        }
    }
]

async function fetchPosts() {
    loading.value = true
    try {
        const res = await $fetch('http://localhost:8000/api/blog/posts', {
            params: { page: page.value, per_page: perPage.value }
        })
        posts.value   = res.data
        total.value   = res.total
        page.value    = res.current_page
        perPage.value = Number(res.per_page)
    } catch (error) {
        console.error('Помилка завантаження постів:', error)
    } finally {
        loading.value = false
    }
}

// Додавання поста
async function addPost(payload: any) {
    try {
        await $fetch('http://localhost:8000/api/blog/posts', {
            method: 'POST',
            body: payload
        })

        // Успішно створено
        openAdd.value = false
        Object.assign(newPost.value, {
            title: '',
            content_raw: '',
            slug: '',
            excerpt: '',
            category_id: null,
            is_published: false
        })
        await fetchPosts()

        console.log('Пост успішно створено!')

    } catch (error: any) {
        console.error('Повна помилка:', error)
        console.error('Статус помилки:', error.status)
        console.error('Дані помилки:', error.data)

        // Якщо це помилка валідації (422), показуємо деталі
        if (error.status === 422 && error.data?.errors) {
            console.error('Детальні помилки валідації:')
            Object.entries(error.data.errors).forEach(([field, messages]) => {
                console.error(`${field}:`, messages)
            })
        }

        console.error('Payload який відправляли:', payload)
    }
}

function onPageChange(newPage: number) {
    page.value = newPage
}

function onPageSizeChange(newSize: number) {
    perPage.value = newSize
    page.value = 1
}

onMounted(fetchPosts)
watch([page, perPage], fetchPosts)
</script>

<template>
    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Управління постами</h1>
            <n-button @click="openAdd = !openAdd" type="primary" size="large">
                {{ openAdd ? 'Сховати форму' : 'Створити пост' }}
            </n-button>
        </div>

        <!-- Форма додавання -->
        <div v-if="openAdd" class="mb-6">
            <BlogPostForm
                ref="formRef"
                :modelValue="newPost"
                @save="addPost"
                @cancel="openAdd = false"
            />
        </div>

        <!-- Таблиця постів -->
        <client-only>
            <n-data-table
                :columns="columns"
                :data="posts"
                :loading="loading"
                :scroll-x="1200"
                striped
            />
            <div class="mt-6 flex justify-center">
                <n-pagination
                    v-model:page="page"
                    v-model:page-size="perPage"
                    :item-count="total"
                    :page-sizes="[10, 20, 50, 100]"
                    show-size-picker
                    @update:page="onPageChange"
                    @update:page-size="onPageSizeChange"
                />
            </div>
        </client-only>
    </div>
</template>
