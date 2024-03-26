<template>
     
    <div class="conbox">
        <div class="consearch">
            <el-button type="success" v-for="item in searchArr" @click="onClickSearchItem(item)">{{ item.name }}
            </el-button>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable v-model="sStartTime" type="date"
                placeholder="开始日期">
            </el-date-picker>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable v-model="sEndTime" type="date"
                placeholder="结束日期">
            </el-date-picker>
            <el-button type="primary" style="margin-left: 10px;" @click="onSearch">查询</el-button>
        </div>

        <div class="indexDefaultBox">
            <el-row>
                <el-col :span="6">
                    <el-card class="box-card">
                        <template #header>余额相关</template>
                        <template #default>
                            <div>充值钱包：{{ tableData.re_balance }}</div>
                            <div>余额钱包：{{ tableData.ba_balance }}</div>
                            <div>&nbsp;</div>
                        </template>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="box-card">
                        <template #header>投资相关</template>
                        <template #default>
                            <div>总投资订单：{{ tableData.invest_count }}</div>
                            <div>总投资额度：{{ tableData.invest_money }}</div>
                            <div>总订单分红：{{ tableData.reward_money }}</div>
                        </template>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="box-card">
                        <template #header>充值相关</template>
                        <template #default>
                            <div>总充值：{{ tableData.total_pay_money }}</div>
                            <div>今日充值：{{ tableData.today_pay_money }}</div>
                            <div>今日首充：{{ tableData.today_first_pay }}</div>
                        </template>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="box-card">
                        <template #header>提现相关</template>
                        <template #default>
                            <div>总提现：{{ tableData.total_cash_money }}</div>
                            <div>今日提现：{{ tableData.today_cash_money }}</div>
                            <div>提现待审核：{{ tableData.uncheck_cash_money }}</div>
                        </template>
                    </el-card>
                </el-col>
            </el-row>
            <el-row>
                <el-col :span="6">
                    <el-card class="box-card">
                        <template #header>抽奖相关</template>
                        <template #default>
                            <div>中奖总额：{{ tableData.total_lottery_money }}</div>
                            <div>今日中奖：{{ tableData.today_lottery_money }}</div>
                            <div>&nbsp;</div>
                        </template>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="box-card">
                        <template #header>红包领取</template>
                        <template #default>
                            <div>红包总额：{{ tableData.total_redpack_money }}</div>
                            <div>今日领取：{{ tableData.today_redpack_money }}</div>
                            <div>&nbsp;</div>
                        </template>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="box-card">
                        <template #header>会员相关</template>
                        <template #default>
                            <div>总会员数：{{ tableData.total_member }}</div>
                            <div>今日注册：{{ tableData.today_member }}</div>
                            <div>有效会员：{{ tableData.effective_member }}</div>
                        </template>
                    </el-card>
                </el-col>
                <el-col :span="6">
                    <el-card class="box-card">
                        <template #header>动态验证码</template>
                        <template #default>
                            <div style="font-weight: bold;">{{ tableData.sms_code }}</div>
                            <div>&nbsp;</div>
                            <div>&nbsp;</div>
                        </template>
                    </el-card>
                </el-col>
            </el-row>

            <div style="height: 100px;"></div>

        </div>

    </div>

</template>

<script lang="ts" setup>
import { onMounted, ref } from 'vue'
import { useStore } from "vuex";
import http from "../../global/network/http";
import dayjs from "dayjs";
import { _alert } from "../../global/common";
const store = useStore()

const tableData = ref({})
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
    doSearch()
}

const sStartTime = ref('')
const sEndTime = ref('')

const doSearch = () => {
    let s_start_time = dayjs(new Date(sStartTime.value)).format('YYYY-MM-DD')
    let s_end_time = dayjs(new Date(sEndTime.value)).format('YYYY-MM-DD')
    http({
        url: 'c=Default&a=getData',
        data: {
            type: searchItem.value.type,
            start_time: s_start_time,
            end_time: s_end_time
        }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        tableData.value = res.data
    })
}

const onSearch = () => {
    searchItem.value.type = 0
    doSearch()
}

onMounted(() => {
    if (!store.state.user.gid || store.state.user.gid == 92) {
        location.href = '/h5/'
        return
    }
    onSearch()
    setTimeout(() => { store.dispatch('loadingFinish'); }, store.state.loadingTime)
})

</script>
<style>
.indexDefaultBox .box-card {
    margin: 1rem 1rem;
    font-size: 1.5rem;
}
</style>