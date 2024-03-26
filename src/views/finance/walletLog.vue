<template>
    <Page :url="pageUrl" :need-time="true">
        <template #title="{ tdata, title }">
            <template v-if="!tdata.wallet">
                <el-breadcrumb-item>{{ title }}</el-breadcrumb-item>
            </template>
            <template v-else>
                <el-breadcrumb-item @click="router.go(-1)" style="cursor: pointer;"><i
                        class="el-icon-arrow-left"></i>返回</el-breadcrumb-item>
                <el-breadcrumb-item>
                    <el-image :src="getSrcUrl(tdata.user.headimgurl)"
                        style="width: 30px;height: 30px;vertical-align: middle;" />
                    <span> &nbsp;{{ tdata.user.account }}</span>
                    <span>（{{ tdata.user.nickname }}）</span>
                </el-breadcrumb-item>
                <el-breadcrumb-item>
                    <el-image :src="getSrcUrl(tdata.wallet.icon)"
                        style="width: 30px;height: 30px;vertical-align: middle;" />
                    <span> &nbsp;{{ tdata.wallet.currency }}</span>
                    <span>（{{ tdata.wallet.waddr }}）</span>
                </el-breadcrumb-item>
            </template>
        </template>
        <template #search="{ params, tdata }">
            <el-select size="small" style="width: 120px;margin-left: 10px;" v-model="params.s_cid" placeholder="全部币种"
                v-if="!tdata.wallet">
                <el-option key="0" label="全部币种" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.currency_arr" :key="idx" :label="item.name" :value="item.id">
                </el-option>
            </el-select>
            <el-select size="small" style="width: 140px;margin-left: 10px;" v-model="params.s_type" placeholder="全部账变类型">
                <el-option key="0" label="全部账变类型" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.balance_type" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
        </template>
        <template #table>
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="account" label="账号" width="140"></el-table-column>
            <el-table-column prop="nickname" label="昵称" width="140"></el-table-column>
            <el-table-column prop="name" label="钱包类型" width="200">
                <template #default="scope">
                    <div style="text-align: left;padding: 0 30px;">
                        <el-image style="width: 30px;height: 30px;vertical-align: middle;" fit="cover"
                            :src="getSrcUrl(scope.row.icon + '?v=0.1')" hide-on-click-modal
                            :preview-src-list="[getSrcUrl(scope.row.icon)]">
                        </el-image>&nbsp;
                        <span>{{ scope.row.currency }}</span>
                    </div>
                </template>
            </el-table-column>
            <el-table-column prop="type_flag" label="账变类型" width="160"></el-table-column>
            <el-table-column prop="money" label="额度" width="140"></el-table-column>
            <el-table-column prop="ori_balance" label="变更前" width="140"></el-table-column>
            <el-table-column prop="new_balance" label="变更后" width="140"></el-table-column>
            <el-table-column prop="create_time" label="时间" width="160"></el-table-column>
            <el-table-column prop="remark" label="备注"></el-table-column>
        </template>

        <template #summary="{ tdata }">
            <span>记录：{{ tdata.count }}</span>
            <span>额度：{{ tdata.money }}</span>
        </template>

    </Page>
</template>

<script lang="ts" setup>
import { ref, onMounted } from 'vue'
import Page from '../../components/Page.vue'
import { useRoute, useRouter } from "vue-router";
import { getSrcUrl } from '../../global/common'

const router = useRouter()
const route = useRoute()
const pageUrl = ref('c=Finance&a=walletLog')
if (route.query.wid) {
    pageUrl.value += '&wid=' + route.query.wid
}

onMounted(() => {

})

</script>