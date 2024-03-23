<template>
    <div class="conbar">
        <el-breadcrumb separator="/"
            style="padding-left:12px;padding-top: 2px;line-height: 40px;display: inline-block;">
            <el-breadcrumb-item>UTR 操作</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <div class="conbox">

        <el-input style="width: 320px;margin-left: 10px;" placeholder="请输入UTR" clearable v-model="utr"
            @keyup.enter="doSearch">
            <template #prepend>UTR<i class="el-icon-search"></i></template>
        </el-input>
        <el-button type="primary" style="margin-left: 10px;" @click="doSearch">查询</el-button>

    </div>
</template>

<script lang="ts">
import { defineComponent, reactive } from 'vue';
export default defineComponent({

})
</script>
<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useStore } from "vuex";
import http from "../../global/network/http";
import { _alert } from "../../global/common";
const store = useStore()
const utr = ref('');

const doSearch = () => {
    http({
        url: 'c=Finance&a=utr',
        data: {
            utr: utr.value,
            pay_type: 'jwpay',
        }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
    })
}

const onSearch = () => {

    http({
        url: 'c=Finance&a=ptype',
        data: {
            page: 1,
            szie: 200
        }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
    })
}

onMounted(() => {
    onSearch()
    setTimeout(() => { store.dispatch('loadingFinish'); }, store.state.loadingTime)
})

</script>