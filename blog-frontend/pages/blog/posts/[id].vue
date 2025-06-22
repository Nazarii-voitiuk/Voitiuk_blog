<script setup lang="ts">
import { useRoute, useRouter } from 'vue-router'
import { ref, onMounted } from 'vue'

const route = useRoute()
const router = useRouter()
const postId = route.params.id
const post = ref<any>(null)
const error = ref<string | null>(null)
const activeTab = ref<'main' | 'extra'>('main')
const isEdit = ref(false)
const isSaving = ref(false)

// Для редагування — копія полів
const editFields = ref<any>({
    title: '',
    content_raw: '',
    slug: '',
    excerpt: '',
    category_id: '',
    is_published: false
})

onMounted(async () => {
    await fetchPost()
})

async function fetchPost() {
    try {
        const response = await fetch(`http://localhost:8000/api/posts/${postId}`)
        if (!response.ok) throw new Error('Пост не знайдено')
        const data = await response.json()
        post.value = data
        // Заповнюємо поля для редагування
        Object.assign(editFields.value, {
            title: data.title,
            content_raw: data.content_raw,
            slug: data.slug,
            excerpt: data.excerpt,
            category_id: data.category_id,
            is_published: !!data.is_published
        })
    } catch (err: any) {
        error.value = err.message
    }
}

function formatDate(date: string): string {
    return new Date(date).toLocaleString('uk-UA', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit'
    })
}

// Перемикач режиму редагування
function enableEdit() {
    isEdit.value = true
}

// Зберегти зміни
async function saveEdit() {
    isSaving.value = true
    try {
        const response = await fetch(`http://localhost:8000/api/posts/${postId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(editFields.value)
        })
        const respText = await response.text()  // читаємо raw-відповідь
        if (!response.ok) {
            console.log('Server response:', respText)
            throw new Error(respText || 'Помилка при збереженні')
        }
        isEdit.value = false
        await fetchPost()
    } catch (err: any) {
        error.value = err.message
    }
    isSaving.value = false
}


// Видалити пост
async function deletePost() {
    if (!confirm('Ви впевнені, що хочете видалити пост?')) return
    try {
        const response = await fetch(`http://localhost:8000/api/posts/${postId}`, {
            method: 'DELETE'
        })
        if (!response.ok) throw new Error('Помилка видалення')
        router.push('/BlogPostsUi')// або змініть на свій шлях
    } catch (err: any) {
        error.value = err.message
    }
}
</script>

<template>
    <div class="bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto p-6">
            <div v-if="error" class="text-red-600 text-center text-lg">
                {{ error }}
            </div>
            <div v-else-if="!post" class="text-center text-gray-500">
                Завантаження...
            </div>
            <div v-else class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200 rounded-t-lg flex justify-between items-center">
                            <h2 class="text-sm font-medium text-gray-700">Опубліковано</h2>
                            <div>
                                <button
                                    v-if="!isEdit"
                                    class="text-blue-600 text-sm hover:underline mr-3"
                                    @click="enableEdit"
                                >
                                    Редагувати
                                </button>
                                <button
                                    class="text-red-600 text-sm hover:underline"
                                    @click="deletePost"
                                >
                                    Видалити
                                </button>
                            </div>
                        </div>

                        <!-- Tabs -->
                        <div class="border-b border-gray-200">
                            <nav class="flex">
                                <button
                                    @click="activeTab = 'main'"
                                    :class="['px-6 py-3 text-sm font-medium border-r border-gray-200 relative', activeTab === 'main' ? 'text-gray-900 bg-white' : 'text-blue-600 hover:text-blue-800 transition-colors']">
                                    Основні дані
                                    <div v-if="activeTab === 'main'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600"></div>
                                </button>
                                <button
                                    @click="activeTab = 'extra'"
                                    :class="['px-6 py-3 text-sm font-medium relative', activeTab === 'extra' ? 'text-gray-900 bg-white' : 'text-blue-600 hover:text-blue-800 transition-colors']">
                                    Додаткові дані
                                    <div v-if="activeTab === 'extra'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600"></div>
                                </button>
                            </nav>
                        </div>

                        <!-- Tabs Content -->
                        <div class="p-6 space-y-6">
                            <div v-if="activeTab === 'main'">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Заголовок</label>
                                    <input
                                        v-if="isEdit"
                                        v-model="editFields.title"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none"
                                    />
                                    <input
                                        v-else
                                        type="text"
                                        :value="post.title"
                                        disabled
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600 focus:outline-none"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Текст статті</label>
                                    <textarea
                                        v-if="isEdit"
                                        v-model="editFields.content_raw"
                                        rows="12"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none resize-none"
                                    ></textarea>
                                    <textarea
                                        v-else
                                        :value="post.content_raw"
                                        disabled
                                        rows="12"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-600 resize-none focus:outline-none"
                                    ></textarea>
                                </div>
                            </div>
                            <div v-else>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Псевдонім (slug)</label>
                                    <input
                                        v-if="isEdit"
                                        v-model="editFields.slug"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-md focus:outline-none"
                                    />
                                    <input
                                        v-else
                                        type="text"
                                        :value="post.slug"
                                        disabled
                                        class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600"
                                    />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Короткий текст (excerpt)</label>
                                    <textarea
                                        v-if="isEdit"
                                        v-model="editFields.excerpt"
                                        rows="4"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-md focus:outline-none resize-none"
                                    ></textarea>
                                    <textarea
                                        v-else
                                        :value="post.excerpt"
                                        disabled
                                        rows="4"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600 resize-none"
                                    ></textarea>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Категорія</label>
                                    <input
                                        v-if="isEdit"
                                        v-model="editFields.category_id"
                                        type="text"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-md focus:outline-none"
                                    />
                                    <input
                                        v-else
                                        type="text"
                                        :value="post.category ? post.category.title : `Категорія #${post.category_id}`"
                                        disabled
                                        class="w-full px-3 py-2 border border-gray-200 rounded-md bg-gray-50 text-gray-600"
                                    />
                                </div>
                                <div class="flex items-center gap-2 pt-2">
                                    <input
                                        v-if="isEdit"
                                        type="checkbox"
                                        v-model="editFields.is_published"
                                    />
                                    <input
                                        v-else
                                        type="checkbox"
                                        :checked="!!post.is_published"
                                        disabled
                                    />
                                    <label class="text-sm text-gray-700">Опубліковано</label>
                                </div>
                            </div>
                        </div>
                        <!-- Bottom Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 rounded-b-lg flex justify-between items-center">
                            <div />
                            <div>
                                <button
                                    v-if="isEdit"
                                    class="bg-blue-600 text-white px-4 py-2 text-sm font-medium rounded-md hover:bg-blue-700 transition-colors"
                                    :disabled="isSaving"
                                    @click="saveEdit"
                                >
                                    {{ isSaving ? 'Збереження...' : 'Зберегти' }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sidebar -->
                <div class="space-y-4">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-4">
                            <button
                                v-if="isEdit"
                                class="w-full bg-gray-400 text-white px-4 py-2 text-sm font-medium rounded-md hover:bg-gray-500 transition-colors"
                                @click="isEdit = false"
                            >
                                Скасувати
                            </button>
                        </div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <div class="space-y-3 text-sm">
                            <div>
                                <span class="text-gray-600">ID: </span>
                                <span class="font-medium">{{ post.id }}</span>
                            </div>
                            <div>
                                <div class="text-gray-600 font-medium mb-1">Створено</div>
                                <div class="text-gray-800">{{ formatDate(post.created_at) }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600 font-medium mb-1">Змінено</div>
                                <div class="text-gray-800">{{ formatDate(post.updated_at) }}</div>
                            </div>
                            <div>
                                <div class="text-gray-600 font-medium mb-1 text-xs">Опубліковано</div>
                                <div class="text-gray-800">{{ formatDate(post.published_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
