<template>
    <div class="agentBox">
        <Page url="c=User&a=agent" ref="pageRef" :need-time="true">
            <template #search="{ params, tdata, doSearch }">
                <!--            <el-input size="small"
                    style="width: 280px;margin-left: 10px;margin-right: 10px;"
                    placeholder="上级用户账号"
                    clearable
                    v-model="params.s_keyword2"
                    @keyup.enter="doSearch">
                <template #prepend>团队搜索</template>
            </el-input>-->

                <el-button type="success" v-for="item in searchArr" @click="onClickSearchItem(item)">{{ item.name
                }}</el-button>
            </template>

            <template #table="myScope">
                <!--            <el-table-column prop="id" label="ID" width="80"></el-table-column>-->
                <el-table-column prop="account" label="账号" width="140"></el-table-column>
                <!--            <el-table-column prop="nickname" label="昵称" width="120"></el-table-column>-->
                <!--            <el-table-column prop="headimgurl" label="头像" width="80" class-name="imgCellBox">
                <template #default="scope">
                    <el-image
                            style="width: 40px;height: 40px;vertical-align: middle;"
                            v-if="scope.row.headimgurl"
                            fit="cover"
                            :src="imgFlag(scope.row.headimgurl)"
                            hide-on-click-modal
                            :preview-src-list="[imgFlag(scope.row.headimgurl)]">
                    </el-image>
                </template>
            </el-table-column>-->
                <!--            <el-table-column prop="gname" label="等级" width="110"></el-table-column>-->
                <el-table-column prop="zcz" label="总充值"></el-table-column>
                <el-table-column prop="ztx" label="总提现"></el-table-column>
                <el-table-column prop="zjy" label="总结余"></el-table-column>
                <el-table-column prop="jrcz" label="今日充值"></el-table-column>
                <el-table-column prop="jrtx" label="今日提现"></el-table-column>
                <el-table-column prop="jrcj" label="今日抽奖"></el-table-column>
                <el-table-column prop="jrhb" label="今日红包"></el-table-column>
                <el-table-column prop="yxhy" label="有效会员"></el-table-column>
                <el-table-column prop="jrsc" label="今日首充"></el-table-column>
                <el-table-column prop="jrzc" label="今日注册"></el-table-column>
            </template>

            <template #summary="{ tdata }">
                <span>代理数：{{ tdata.count }}</span>
            </template>

        </Page>
    </div>
</template>

<script lang="ts">
import { defineComponent, ref, onMounted } from 'vue';
import Page from '../../components/Page.vue';

export default defineComponent({
    components: {
        Page
    }
})
</script>

<script lang="ts" setup>

import { getSrcUrl } from "../../global/common";
import { ref } from "vue";

const pageRef = ref()

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

const searchArr = [
    { type: 1, name: '今天' },
    { type: 2, name: '昨天' },
    { type: 3, name: '最近7天' },
    { type: 4, name: '最近30天' },
    { type: 5, name: '上月' },
    { type: 6, name: '本月' }
]

const searchItem = ref({
    type: 0
})

const onClickSearchItem = (item: any) => {
    searchItem.value = item
    pageRef.value.doSearch2({
        type: searchItem.value.type
    })
}


</script>

<style>
.agentBox .el-table th.el-table__cell>.cell,
.agentBox .el-table td.el-table__cell div {
    font-size: 22px;
    padding-top: 20px;
    padding-bottom: 20px;
}

.agentBox .el-table td.el-table__cell,
.agentBox .el-table th.el-table__cell.is-leaf {
    border: 0;
}
</style>