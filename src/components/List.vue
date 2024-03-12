<template>
    <van-list loading-text="Loading" class="myListBox" v-model:loading="tableData.loading" :finished="tableData.finished"
        :finished-text="finishText" @load="onLoad">
        <template v-for="(item, idx) in tableData.list">
            <slot name="default" :row="item" :$index="idx"></slot>
        </template>
    </van-list>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { List } from 'vant';

export default defineComponent({
    components: {
        [List.name]: List
    }
})
</script>

<script lang="ts" setup>
import { reactive, ref } from "vue";
import http from "../global/network/http";
import { _alert, lang } from "../global/common";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
const emit = defineEmits(['success', 'error', 'before'])

const props = defineProps({
    title: {
        type: String,
        default: ''
    },
    url: {
        type: String,
        default: ''
    },
    autoLoad: { type: Boolean, default: true },
    finishText: { type: String, default: t('没有更多了') }
})

interface DataList {
    count?: number,
    limit?: number,
    list: any[],
    loading: boolean,
    finished: boolean,
    [propName: string]: any
}

interface SearchParams {
    page: number,
    s_keyword: string,
    [propName: string]: any
}

let isRequest = false

const tableData = ref<DataList>({
    count: 0,
    limit: 15,
    loading: false,
    finished: false,
    list: []
})

const searchForm = ref<SearchParams>({
    page: 1,
    s_keyword: ''
})


const getPage = () => {
    if (isRequest) {
        return
    }
    isRequest = true
    emit('before', searchForm.value)
    http({
        url: props.url,
        data: searchForm.value
    }).then((res: any) => {
        setTimeout(() => {
            tableData.value.loading = false;
        }, 100)
        isRequest = false
        if (res.code != 1) {
            tableData.value.finished = true
            emit('error', res)
            _alert({
                type: 'error',
                message: res.msg,
                onClose: () => { }
            })
            return
        }

        searchForm.value.page = res.data.page

        for (let i in res.data) {
            if (i == 'list') {
                for (let j in res.data.list) {
                    tableData.value.list.push(res.data.list[j])
                }
                continue
            }
            tableData.value[i] = res.data[i]
        }

        emit('success', { data: tableData.value, params: searchForm.value })
    })
}

const onLoad = () => {
    if (props.autoLoad || searchForm.value.page > 1) {
        getPage()
    }
}

const doSearch = (params: object | null) => {
    tableData.value.list = []
    searchForm.value.page = 1
    if (typeof params == 'object') {
        for (let i in params) {
            searchForm.value[i] = params[i]
        }
    }
    getPage()
}

const delItem = (idx: number) => {
    tableData.value.list.splice(idx, 1)
    tableData.value.count -= 1
}

defineExpose({
    tableData,
    delItem,
    doSearch
})

</script>