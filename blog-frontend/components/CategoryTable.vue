<script setup lang="ts">
import { defineProps, defineEmits, h, computed } from 'vue'
import type { DataTableColumns } from 'naive-ui'
import { NDropdown, NButton, NIcon, NTag } from 'naive-ui'
import { MoreOutlined } from '@vicons/antd'

interface Category {
    id: number
    title: string
    slug: string
    parent_id: number|null
    description: string
}

const props = defineProps<{
    categories: Category[]
    loading?: boolean
}>()
const emit = defineEmits(['edit','delete'])

const columns: DataTableColumns<Category> = [
    {
        title: 'ID',
        key: 'id',
        width: 60
    },
    {
        title: 'Назва',
        key: 'title',
        render(row) {
            return h('span', { class: 'font-medium' }, row.title)
        }
    },
    {
        title: 'Slug',
        key: 'slug',
        render(row) {
            return h('code', { class: 'px-2 py-1 bg-gray-100 rounded text-sm' }, row.slug)
        }
    },
    {
        title: 'Батьківська',
        key: 'parent_id',
        width: 120,
        render(row) {
            if (row.parent_id === null || row.parent_id === 0) {
                return h(NTag, { type: 'info', size: 'small' }, { default: () => 'Коренева' })
            }
            return h(NTag, { type: 'default', size: 'small' }, { default: () => `ID: ${row.parent_id}` })
        }
    },
    {
        title: 'Опис',
        key: 'description',
        render(row) {
            if (!row.description) {
                return h('span', { class: 'text-gray-400 italic' }, 'Без опису')
            }
            return h('span', row.description.length > 50
                ? row.description.substring(0, 50) + '...'
                : row.description
            )
        }
    },
    {
        title: 'Дії',
        key: 'actions',
        width: 80,
        render(row) {
            return h(NDropdown, {
                trigger: 'click',
                options: [
                    { label: 'Редагувати', key: 'edit' },
                    { label: 'Видалити', key: 'delete' }
                ],
                onSelect: (key: string) => {
                    if (key === 'edit') emit('edit', row)
                    if (key === 'delete') emit('delete', row.id)
                }
            }, {
                default: () => h(NButton, { size: 'small' }, {
                    icon: () => h(NIcon, null, { default: () => h(MoreOutlined) })
                })
            })
        }
    }
]
</script>

<template>
    <n-data-table
        :columns="columns"
        :data="props.categories"
        :loading="props.loading"
        striped
        :scroll-x="800"
    />
</template>
