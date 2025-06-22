<script setup lang="ts">
import { defineProps, defineEmits, h } from 'vue'
import type { DataTableColumns } from 'naive-ui'
import { NDropdown, NButton, NIcon } from 'naive-ui'
import { MoreOutlined } from '@vicons/antd'

interface Category {
    id:number; title:string; slug:string; parent_id:number|null; description:string
}

const props = defineProps<{ categories:Category[] }>()
const emit  = defineEmits(['edit','delete'])

const columns:DataTableColumns<Category> = [
    { title:'ID', key:'id' },
    { title:'Назва', key:'title' },
    { title:'Slug', key:'slug' },
    { title:'Parent ID', key:'parent_id' },
    { title:'Опис', key:'description' },
    {
        title:'Дії', key:'actions',
        render(row) {
            return h(NDropdown,{
                trigger:'click',
                options:[
                    { label:'Редагувати', key:'edit' },
                    { label:'Видалити',   key:'delete' }
                ],
                onSelect:key => {
                    if (key==='edit')   emit('edit',row)
                    if (key==='delete') emit('delete',row.id)
                }
            }, {
                default:() => h(NButton,null,{
                    icon:()=>h(NIcon,null,{ default:()=>h(MoreOutlined) })
                })
            })
        }
    }
]
</script>

<template>
    <n-data-table :columns="columns" :data="props.categories" />
</template>
