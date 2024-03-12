<template>
    <Page url="c=User&a=statistics" ref="pageRef">
        <template #search="{ params, tdata, doSearch }">

            <span style="font-size: 14px;margin-left: 10px;">有消费：</span>
            <el-select style="width: 90px;" v-model="params.s_xiaofei" placeholder="全部">
                <el-option key="all" label="全部" value="all"></el-option>
                <el-option key="0" label="否" value="0"></el-option>
                <el-option key="1" label="是" value="1"></el-option>
            </el-select>

            <span style="font-size: 14px;margin-left: 10px;">金额范围：</span>
            <el-input style="width: 108px;" placeholder="起始金额" clearable v-model="params.s_money_from"
                @keyup.enter="doSearch">
            </el-input>
            <span style="font-size: 14px;">&nbsp;&nbsp;到&nbsp;&nbsp;</span>
            <el-input style="width: 108px;" placeholder="结束金额" clearable v-model="params.s_money_to"
                @keyup.enter="doSearch">
            </el-input>
            <span style="font-size: 14px;margin-left: 10px;">注册时间：</span>
            <el-date-picker v-model="params.regTimeRange" type="datetimerange" align="right" start-placeholder="开始日期" end-placeholder="结束日期"></el-date-picker>
            <el-input style="width: 280px;margin-left: 10px;" placeholder="上级用户账号" clearable v-model="params.s_keyword3"
                @keyup.enter="doSearch">
                <template #prepend>直推搜索</template>
            </el-input>
            <el-input style="width: 280px;margin-left: 10px;" placeholder="上级用户账号" clearable v-model="params.s_keyword2"
                @keyup.enter="doSearch">
                <template #prepend>团队搜索</template>
            </el-input>
            <el-input style="width: 250px;margin-left: 10px;" placeholder="登录ip" clearable v-model="params.s_loginip"
                @keyup.enter="doSearch">
                <template #prepend>登录ip</template>
            </el-input>
            <el-input style="width: 250px;margin-left: 10px;" placeholder="上级用户账号" clearable v-model="params.s_regip"
                @keyup.enter="doSearch">
                <template #prepend>注册ip</template>
            </el-input>
        </template>

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="account" label="账号" width="130"></el-table-column>
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
            <el-table-column prop="gname" label="等级" width="110"></el-table-column>
            <el-table-column prop="tdyj" label="团队佣金" width="100"></el-table-column>
            <el-table-column prop="tjyj" label="推荐业绩" width="100"></el-table-column>
            <el-table-column prop="zsy" label="总收益" width="100"></el-table-column>
            <el-table-column prop="zxf" label="总消费" width="100"></el-table-column>
            <el-table-column prop="zcz" label="总充值" width="100"></el-table-column>
            <el-table-column prop="ztx" label="总提现" width="100"></el-table-column>
            <el-table-column prop="lirun" label="利润" width="100"></el-table-column>
            <el-table-column prop="pid" label="推荐用户" width="130">
                <template #default="scope">
                    <div v-if="scope.row.pid" style="line-height: 20px;">{{ scope.row.p_account }}</div>
                    <div v-else>/</div>
                </template>
            </el-table-column>
            <el-table-column prop="agent2_account" label="二级代理" width="120"></el-table-column>
            <el-table-column prop="agent1_account" label="一级代理" width="120"></el-table-column>
            <el-table-column prop="tjrs" label="推荐人数" width="100"></el-table-column>
            <el-table-column prop="tdrs" label="团队人数" width="100"></el-table-column>
            <el-table-column prop="czqb" label="充值钱包" width="100"></el-table-column>
            <el-table-column prop="yeqb" label="余额钱包" width="100"></el-table-column>
            <el-table-column prop="first_pay_day" label="首充" width="120"></el-table-column>
            <el-table-column prop="reg_time" label="注册时间" width="140"></el-table-column>
            <el-table-column prop="reg_ip" label="注册ip" width="140"></el-table-column>
            <el-table-column prop="login_ip" label="最后登录ip" width="140"></el-table-column>
            <el-table-column prop="login_time" label="最后登录时间" width="140"></el-table-column>
        </template>

        <template #summary="{ tdata }">
            <span>会员数：{{ tdata.count }}</span>
        </template>

    </Page>
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

const pageRef = ref()

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}


</script>