<script setup lang="ts">
import { defineProps, defineEmits, ref, watch, nextTick } from 'vue'
import { z } from 'zod'
import { NForm, NFormItem, NInput, NInputNumber, NButton } from 'naive-ui'

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

// Ініціалізація та оновлення значень
function updateFormValues() {
    title.value = props.modelValue.title || ''
    slug.value = props.modelValue.slug || ''
    parent_id.value = props.modelValue.parent_id ?? 0
    description.value = props.modelValue.description || ''
    errors.value = {}
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

function submit() {
    errors.value = {}
    const payload = {
        title: title.value,
        slug: slug.value,
        parent_id: parent_id.value,
        description: description.value,
        id: props.modelValue.id
    }

    const res = schema.safeParse(payload)
    if (!res.success) {
        res.error.errors.forEach(e => {
            errors.value[e.path[0]] = e.message
        })
        return
    }
    emit('save', payload)
}

function cancel() {
    emit('cancel')
}
</script>

<template>
    <div class="category-form">
        <n-form @submit.prevent="submit">
            <n-form-item label="Назва" :feedback="errors.title" :show-feedback="!!errors.title">
                <n-input
                    v-model:value="title"
                    placeholder="Введіть назву категорії"
                    :disabled="false"
                    clearable
                />
            </n-form-item>

            <n-form-item label="Slug" :feedback="errors.slug" :show-feedback="!!errors.slug">
                <n-input
                    v-model:value="slug"
                    placeholder="Введіть slug"
                    :disabled="false"
                    clearable
                />
            </n-form-item>

            <n-form-item label="Parent ID" :feedback="errors.parent_id" :show-feedback="!!errors.parent_id">
                <n-input-number
                    v-model:value="parent_id"
                    :min="0"
                    :disabled="false"
                    placeholder="0"
                />
            </n-form-item>

            <n-form-item label="Опис" :feedback="errors.description" :show-feedback="!!errors.description">
                <n-input
                    v-model:value="description"
                    type="textarea"
                    placeholder="Введіть опис категорії"
                    :disabled="false"
                    :autosize="{ minRows: 3, maxRows: 6 }"
                    clearable
                />
            </n-form-item>

            <div class="flex gap-2 mt-4">
                <n-button type="primary" @click="submit">
                    {{ props.isEditing ? 'Зберегти' : 'Додати' }}
                </n-button>
                <n-button @click="cancel">
                    Скасувати
                </n-button>
            </div>
        </n-form>
    </div>
</template>

<style scoped>
.category-form {
    padding: 16px;
}

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
