<template>
<Page url="c=Sys&a=log" :need-time="true">
    <template #table>
        <el-table-column prop="id" label="ID" width="80"></el-table-column>
        <el-table-column prop="nickname" label="用户" width="140"></el-table-column>
        <el-table-column prop="opt_name" label="操作类型" width="160"></el-table-column>
        <el-table-column prop="sql_str" label="操作详情" min-width="400"></el-table-column>
        <el-table-column prop="create_time" label="时间" width="160"></el-table-column>
        <el-table-column prop="create_ip" label="IP" width="140"></el-table-column>
    </template>
</Page>
</template>

<script lang="ts" setup>
    import {ref, onMounted} from 'vue'
    import Page from '../../components/Page.vue'

    onMounted(()=>{

    })

</script>