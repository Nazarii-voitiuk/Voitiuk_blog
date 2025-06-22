<script setup lang="ts">
import { defineProps, defineEmits, ref, watch, nextTick, computed } from 'vue'
import { z } from 'zod'
import { NForm, NFormItem, NInput, NInputNumber, NButton, NAlert } from 'naive-ui'
import { $fetch } from 'ofetch'

interface Category {
    id?: number
    title: string
    slug: string
    parent_id: number|null
    description: string
}

const props = defineProps<{ modelValue: Category; isEditing?: boolean }>()
const emit  = defineEmits(['save', 'cancel'])

// Локальні реактивні змінні
const title = ref('')
const slug = ref('')
const parent_id = ref<number>(0)
const description = ref('')
const errors = ref<Record<string, string>>({})
const serverErrors = ref<Record<string, string[]>>({})

// Ініціалізація та оновлення значень
function updateFormValues() {
    title.value = props.modelValue.title || ''
    slug.value = props.modelValue.slug || ''
    parent_id.value = props.modelValue.parent_id ?? 0
    description.value = props.modelValue.description || ''
    errors.value = {}
    serverErrors.value = {}
}

// Викликаємо при монтуванні та зміні modelValue
watch(() => props.modelValue, () => {
    nextTick(() => {
        updateFormValues()
    })
}, { immediate: true, deep: true })

const schema = z.object({
    title: z.string().min(2, 'Мінімум 2 символи'),
    slug: z.string().min(2, 'Мінімум 2 символи').regex(/^[a-z0-9-]+$/, 'Лише a–z, цифри та тире'),
    parent_id: z.number({ invalid_type_error: "Має бути числом" }).min(0, 'Мінімум 0'),
    description: z.string().max(255, 'Максимум 255 символів').or(z.literal(''))
})

// Функція для генерації унікального slug
async function generateUniqueSlug(baseSlug: string): Promise<string> {
    let uniqueSlug = baseSlug
    let counter = 1

    while (true) {
        try {
            // Перевіряємо чи існує slug
            const response = await $fetch(`http://localhost:8000/api/blog/categories/check-slug`, {
                method: 'POST',
                body: { slug: uniqueSlug, exclude_id: props.modelValue.id }
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
        parent_id: parent_id.value,
        description: description.value.trim(),
        id: props.modelValue.id
    }

    console.log('Payload перед валідацією:', payload)

    const res = schema.safeParse(payload)
    if (!res.success) {
        console.log('Помилки Zod валідації:', res.error.errors)
        res.error.errors.forEach(e => {
            errors.value[e.path[0]] = e.message
        })
        return
    }

    // Якщо slug не унікальний, генеруємо новий
    if (!props.isEditing) {
        payload.slug = await generateUniqueSlug(payload.slug)
    }

    console.log('Payload після валідації:', payload)
    emit('save', payload)
}

function cancel() {
    emit('cancel')
}

// Показуємо помилки сервера
const hasServerErrors = computed(() => Object.keys(serverErrors.value).length > 0)
</script>

<template>
    <div class="category-form p-4 bg-white rounded-lg shadow">
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
            <n-form-item label="Назва" :feedback="errors.title" :show-feedback="!!errors.title">
                <n-input
                    v-model:value="title"
                    placeholder="Введіть назву категорії"
                    clearable
                />
            </n-form-item>

            <n-form-item label="Slug" :feedback="errors.slug" :show-feedback="!!errors.slug">
                <n-input
                    v-model:value="slug"
                    placeholder="url-slug-категорії"
                    clearable
                />
                <template #feedback>
                    <span class="text-xs text-gray-500">Використовуйте лише малі літери, цифри та тире. Slug буде автоматично згенерований як унікальний.</span>
                </template>
            </n-form-item>

            <n-form-item label="Parent ID" :feedback="errors.parent_id" :show-feedback="!!errors.parent_id">
                <n-input-number
                    v-model:value="parent_id"
                    :min="0"
                    placeholder="0 (для кореневої категорії)"
                />
                <template #feedback>
                    <span class="text-xs text-gray-500">0 для кореневої категорії, або ID батьківської категорії</span>
                </template>
            </n-form-item>

            <n-form-item label="Опис" :feedback="errors.description" :show-feedback="!!errors.description">
                <n-input
                    v-model:value="description"
                    type="textarea"
                    placeholder="Введіть опис категорії"
                    :autosize="{ minRows: 3, maxRows: 6 }"
                    clearable
                />
            </n-form-item>

            <div class="flex gap-3 mt-6">
                <n-button type="primary" @click="submit" size="large">
                    {{ props.isEditing ? 'Зберегти зміни' : 'Створити категорію' }}
                </n-button>
                <n-button @click="cancel" size="large">
                    Скасувати
                </n-button>
            </div>
        </n-form>
    </div>
</template>

<style scoped>
.category-form :deep(.n-input__input-el) {
    pointer-events: auto !important;
    user-select: text !important;
}

.category-form :deep(.n-input) {
    pointer-events: auto !important;
}

.category-form :deep(.n-input--disabled) {
    pointer-events: none !important;
}
</style>
