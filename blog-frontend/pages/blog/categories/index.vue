<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import CategoryForm from '~/components/CategoryForm.vue'
import CategoryTable from '~/components/CategoryTable.vue'
import { $fetch } from 'ofetch'

interface Category {
    id?: number
    title: string
    slug: string
    parent_id: number|null
    description: string
}

const categories = ref<Category[]>([])
const newCat     = ref<Category>({ title:'', slug:'', parent_id:0, description:'' })
const editCat    = ref<Category>({ title:'', slug:'', parent_id:0, description:'' })
const openAdd    = ref(false)
const openEdit   = ref(false)
const loading    = ref(false)

async function load() {
    loading.value = true
    try {
        categories.value = await $fetch('http://localhost:8000/api/blog/categories')
    } catch (error) {
        console.error('Помилка завантаження категорій:', error)
    } finally {
        loading.value = false
    }
}

async function addCategory(cat: Category) {
    try {
        await $fetch('http://localhost:8000/api/blog/categories', {
            method: 'POST',
            body: cat
        })

        newCat.value = { title:'', slug:'', parent_id:0, description:'' }
        openAdd.value = false
        await load()

        console.log('Категорію успішно створено!')

    } catch (error: any) {
        console.error('Помилка створення категорії:', error)

        if (error.status === 422 && error.data?.errors) {
            console.error('Детальні помилки валідації:')
            Object.entries(error.data.errors).forEach(([field, messages]) => {
                console.error(`${field}:`, messages)
            })
        }
    }
}

function startEdit(cat: Category) {
    editCat.value = { ...cat }
    openEdit.value = true
}

async function updateCategory(cat: Category) {
    if (!cat.id) return

    try {
        await $fetch(`http://localhost:8000/api/blog/categories/${cat.id}`, {
            method: 'PUT',
            body: cat
        })

        openEdit.value = false
        await load()

        console.log('Категорію успішно оновлено!')

    } catch (error: any) {
        console.error('Помилка оновлення категорії:', error)

        if (error.status === 422 && error.data?.errors) {
            console.error('Детальні помилки валідації:')
            Object.entries(error.data.errors).forEach(([field, messages]) => {
                console.error(`${field}:`, messages)
            })
        }
    }
}

async function confirmDelete(id: number) {
    if (!confirm('Видалити категорію?')) return

    try {
        await $fetch(`http://localhost:8000/api/blog/categories/${id}`, {
            method: 'DELETE'
        })
        await load()

        console.log('Категорію успішно видалено!')

    } catch (error) {
        console.error('Помилка видалення категорії:', error)
    }
}

onMounted(load)
</script>

<template>
    <div class="p-6 max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">Управління категоріями</h1>
            <n-button @click="openAdd = !openAdd" type="primary" size="large">
                {{ openAdd ? 'Сховати форму' : 'Створити категорію' }}
            </n-button>
        </div>

        <!-- Форма додавання -->
        <div v-if="openAdd" class="mb-6">
            <CategoryForm
                :modelValue="newCat"
                @save="addCategory"
                @cancel="openAdd = false"
            />
        </div>

        <!-- Таблиця -->
        <CategoryTable
            :categories="categories"
            :loading="loading"
            @edit="startEdit"
            @delete="confirmDelete"
        />

        <!-- Редагування (модалка) -->
        <n-modal v-model:show="openEdit" title="Редагувати категорію" style="width: 600px;">
            <n-card>
                <CategoryForm
                    :modelValue="editCat"
                    :isEditing="true"
                    @save="updateCategory"
                    @cancel="openEdit = false"
                />
            </n-card>
        </n-modal>
    </div>
</template>
