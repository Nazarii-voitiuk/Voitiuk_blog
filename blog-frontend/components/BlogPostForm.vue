<script setup lang="ts">
import { defineProps, defineEmits, ref, watch, nextTick, onMounted, computed } from 'vue'
import { z } from 'zod'
import { NForm, NFormItem, NInput, NSelect, NButton, NSwitch, NAlert } from 'naive-ui'
import { $fetch } from 'ofetch' // Import $fetch

const props = defineProps<{ modelValue: any, isEditing?: boolean }>()
const emit  = defineEmits(['save', 'cancel'])

const title = ref('')
const content_raw = ref('')
const slug = ref('')
const excerpt = ref('')
const category_id = ref<number | null>(null)
const is_published = ref(false)
const errors = ref<Record<string, string>>({})
const serverErrors = ref<Record<string, string[]>>({})

// Категорії для селекту
const categories = ref<any[]>([])

async function fetchCategories() {
    try {
        categories.value = await $fetch('http://localhost:8000/api/blog/categories')
    } catch (error) {
        console.error('Помилка завантаження категорій:', error)
    }
}

function updateFormValues() {
    title.value = props.modelValue.title || ''
    content_raw.value = props.modelValue.content_raw || ''
    slug.value = props.modelValue.slug || ''
    excerpt.value = props.modelValue.excerpt || ''
    category_id.value = props.modelValue.category_id ?? null
    is_published.value = !!props.modelValue.is_published
    errors.value = {}
    serverErrors.value = {}
}

watch(() => props.modelValue, () => {
    nextTick(() => {
        updateFormValues()
    })
}, { immediate: true, deep: true })

onMounted(() => {
    fetchCategories()
})

// Zod схема валідації
const schema = z.object({
    title: z.string().min(2, 'Заголовок має містити мінімум 2 символи').max(255, 'Заголовок занадто довгий'),
    slug: z.string()
        .min(2, 'Slug має містити мінімум 2 символи')
        .max(255, 'Slug занадто довгий')
        .regex(/^[a-z0-9-]+$/, 'Slug може містити лише малі літери, цифри та тире'),
    content_raw: z.string().optional(),
    excerpt: z.string().max(500, 'Короткий опис занадто довгий').optional(),
    category_id: z.number({ invalid_type_error: "Оберіть категорію" }).min(1, 'Оберіть категорію'),
    is_published: z.boolean()
})

// Функція для генерації унікального slug
async function generateUniqueSlug(baseSlug: string): Promise<string> {
    let uniqueSlug = baseSlug
    let counter = 1

    while (true) {
        try {
            // Перевіряємо чи існує slug
            const response = await $fetch(`http://localhost:8000/api/blog/posts/check-slug`, {
                method: 'POST',
                body: { slug: uniqueSlug }
            })

            if (!response.exists) {
                return uniqueSlug
            }

            // Якщо існує, додаємо номер
            uniqueSlug = `${baseSlug}-${counter}`
            counter++

        } catch (error) {
            console.error('Помилка перевірки slug:', error)
            return uniqueSlug
        }
    }
}

// Автогенерація slug з заголовка
watch(title, async (newTitle) => {
    if (newTitle && !props.isEditing) {
        const baseSlug = newTitle
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .trim()

        if (baseSlug) {
            slug.value = await generateUniqueSlug(baseSlug)
        }
    }
})

async function submit() {
    errors.value = {}
    serverErrors.value = {}

    const payload = {
        title: title.value.trim(),
        slug: slug.value.trim(),
        content_raw: content_raw.value?.trim() || '',
        excerpt: excerpt.value?.trim() || '',
        category_id: category_id.value,
        is_published: is_published.value
    }

    console.log('Payload перед валідацією:', payload)

    // Клієнтська валідація з Zod
    const result = schema.safeParse(payload)

    if (!result.success) {
        console.log('Помилки Zod валідації:', result.error.errors)
        result.error.errors.forEach(error => {
            errors.value[error.path[0]] = error.message
        })
        return
    }

    // Якщо slug не унікальний, генеруємо новий
    if (!props.isEditing) {
        payload.slug = await generateUniqueSlug(payload.slug)
    }

    console.log('Payload після Zod валідації:', payload)
    emit('save', payload)
}

function cancel() {
    emit('cancel')
}

// Опції для селекту категорій
const categoryOptions = computed(() =>
    categories.value.map(cat => ({
        label: cat.title,
        value: cat.id
    }))
)

// Показуємо помилки сервера
const hasServerErrors = computed(() => Object.keys(serverErrors.value).length > 0)
</script>

<template>
    <div class="blogpost-form p-4 bg-white rounded-lg shadow">
        <!-- Показуємо помилки сервера -->
        <n-alert v-if="hasServerErrors" type="error" class="mb-4">
            <div>
                <h4 class="font-semibold mb-2">Помилки валідації:</h4>
                <ul class="list-disc list-inside">
                    <li v-for="(messages, field) in serverErrors" :key="field">
                        <strong>{{ field }}:</strong> {{ messages.join(', ') }}
                    </li>
                </ul>
            </div>
        </n-alert>

        <n-form @submit.prevent="submit">
            <n-form-item label="Заголовок" :feedback="errors.title" :show-feedback="!!errors.title">
                <n-input
                    v-model:value="title"
                    placeholder="Введіть заголовок поста"
                    clearable
                />
            </n-form-item>

            <n-form-item label="Slug" :feedback="errors.slug" :show-feedback="!!errors.slug">
                <n-input
                    v-model:value="slug"
                    placeholder="url-slug-poста"
                    clearable
                />
                <template #feedback>
                    <span class="text-xs text-gray-500">Використовуйте лише малі літери, цифри та тире. Slug буде автоматично згенерований як унікальний.</span>
                </template>
            </n-form-item>

            <n-form-item label="Категорія" :feedback="errors.category_id" :show-feedback="!!errors.category_id">
                <n-select
                    v-model:value="category_id"
                    :options="categoryOptions"
                    placeholder="Оберіть категорію"
                    clearable
                />
            </n-form-item>

            <n-form-item label="Короткий опис" :feedback="errors.excerpt" :show-feedback="!!errors.excerpt">
                <n-input
                    v-model:value="excerpt"
                    type="textarea"
                    placeholder="Короткий опис поста (до 500 символів)"
                    :autosize="{ minRows: 2, maxRows: 4 }"
                    clearable
                />
            </n-form-item>

            <n-form-item label="Текст статті" :feedback="errors.content_raw" :show-feedback="!!errors.content_raw">
                <n-input
                    v-model:value="content_raw"
                    type="textarea"
                    placeholder="Основний текст статті"
                    :autosize="{ minRows: 8, maxRows: 20 }"
                    clearable
                />
            </n-form-item>

            <n-form-item label="Опубліковано">
                <n-switch v-model:value="is_published">
                    <template #checked>Опубліковано</template>
                    <template #unchecked>Чернетка</template>
                </n-switch>
            </n-form-item>

            <div class="flex gap-3 mt-6">
                <n-button type="primary" @click="submit" size="large">
                    {{ props.isEditing ? 'Зберегти зміни' : 'Створити пост' }}
                </n-button>
                <n-button @click="cancel" size="large">
                    Скасувати
                </n-button>
            </div>
        </n-form>
    </div>
</template>
