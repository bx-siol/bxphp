<template>
    <van-list loading-text="Loading" class="myListBox" v-model:loading="tableData.loading" :finished="tableData.finished"
              :finished-text="finishText" @load="onLoad">
        <slot name="default" :list="tableData.list"></slot>
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
    import { ref } from "vue";
    import http from "../global/network/http";
    import { _alert, lang } from "../global/common";
    
    const emit = defineEmits(['success', 'error', 'before'])

    const props = defineProps({
        url: {
            type: String,
            default: ''
        },
        autoLoad: { type: Boolean, default: true },
        finishText: { type: String, default: 'No more'}
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
    function getQueryVariable(variable: string) {
        if (variable.indexOf('?') == -1) return
        var query = variable.split('?')[1];
        var vars = query.split("&");
        var pairarr = [];
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            pairarr.push(pair);
        }
        return pairarr;
    }

    const getPage = () => {
        if (isRequest) {
            return
        }
        isRequest = true
        emit('before', searchForm.value)
        var tc = getQueryVariable(props.url);
        for (const key in tc) {
            const element = tc[key];
            searchForm.value[element[0]] = element[1];
        }
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
                _alert(res.msg)
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
            emit('success', { data: tableData.value, params: searchForm.value, all: res.data })
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
    const delall = (idx: number) => {
        tableData.value.list = []
        tableData.value.count = 0

    }
    const delItem = (idx: number) => {
        tableData.value.list.splice(idx, 1)
        tableData.value.count -= 1
    }

    const ValidMember = (requesturl: string) => {
        tableData.value.list = []
        tableData.value.count = 0
        searchForm.value.page = 1

        emit('before', searchForm.value)
        var tc = getQueryVariable(requesturl);
        for (const key in tc) {
            const element = tc[key];
            searchForm.value[element[0]] = element[1];
        }
        searchForm.value.page = 1;
        http({
            url: requesturl,
            data: searchForm.value,
            type: 'post'
        }).then((res: any) => {
            setTimeout(() => {
                tableData.value.loading = false;
            }, 100)
            isRequest = false
            if (res.code != 1) {
                tableData.value.finished = true
                emit('error', res)
                _alert(res.msg)
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
            emit('success', { data: tableData.value, params: searchForm.value, all: res.data })
        })
    }

    defineExpose({
        tableData,
        delItem,
        doSearch,
        delall,
        ValidMember,
    })

</script>