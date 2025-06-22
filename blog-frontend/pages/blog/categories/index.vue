<script setup lang="ts">
import { ref, onMounted } from 'vue'
import CategoryForm from '~/components/CategoryForm.vue'
import CategoryTable from '~/components/CategoryTable.vue'

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

async function load() {
    categories.value = await $fetch('http://localhost:8000/api/blog/categories')
}

async function addCategory(cat:Category) {
    await $fetch('http://localhost:8000/api/blog/categories', { method:'POST', body:cat })
    newCat.value = { title:'', slug:'', parent_id:0, description:'' }
    openAdd.value = false
    await load()
}

function startEdit(cat:Category) {
    editCat.value = { ...cat }
    openEdit.value = true
}

async function updateCategory(cat:Category) {
    if (!cat.id) return
    await $fetch(`http://localhost:8000/api/blog/categories/${cat.id}`, {
        method: 'PUT',
        body: cat
    })
    openEdit.value = false
    await load()
}

async function confirmDelete(id:number) {
    if (!confirm('Видалити категорію?')) return
    await $fetch(`http://localhost:8000/api/blog/categories/${id}`, { method:'DELETE' })
    await load()
}

onMounted(load)
</script>

<template>
    <div class="p-6 max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-4">Категорії</h1>
        <n-button @click="openAdd = !openAdd" type="primary" class="mb-4">
            {{ openAdd ? 'Сховати форму' : 'Додати категорію' }}
        </n-button>

        <!-- Додавання -->
        <CategoryForm
            v-if="openAdd"
            :modelValue="newCat"
            @save="addCategory"
            @cancel="openAdd = false"
        />

        <!-- Таблиця -->
        <CategoryTable
            :categories="categories"
            @edit="startEdit"
            @delete="confirmDelete"
        />

        <!-- Редагування (модалка) -->
        <n-modal v-model:show="openEdit" title="Редагувати категорію">
            <CategoryForm
                :modelValue="editCat"
                :isEditing="true"
                @save="updateCategory"
                @cancel="openEdit = false"
            />
        </n-modal>
    </div>
</template>


