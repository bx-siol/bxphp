<template>
    <div class="divs">
        <Page url="c=Finance&a=paylog" ref="pageRef" :need-time="true" :row-class="payFinish"
            :page-layout="isTrans ? 'prev,pager,next' : 'prev, pager, next, jumper'">
            <template #search="{ params, tdata, doSearch }">

                <span style="font-size: 14px;margin-left: 10px;">收款信息：</span>
                <el-switch v-model="viewAccountInfo" size="large" active-text="显示" inactive-text="隐藏" />

                <span style="font-size: 14px;margin-left: 20px;">金额范围：</span>
                <el-input style="width: 108px;" placeholder="起始金额" clearable v-model="params.s_money_from"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;">&nbsp;&nbsp;到&nbsp;&nbsp;</span>
                <el-input style="width: 108px;" placeholder="结束金额" clearable v-model="params.s_money_to"
                    @keyup.enter="doSearch">
                </el-input>

                <span style="font-size: 14px;margin-left: 10px;">翻译：</span>
                <el-select style="width: 70px;" v-model="params.s_trans" placeholder="否" @change="onTrans">
                    <el-option key="0" label="否" value="0"></el-option>
                    <el-option key="1" label="是" value="1"></el-option>
                </el-select>

                <span style="font-size: 14px;margin-left: 10px;">推荐人：</span>
                <el-select style="width: 110px;" v-model="params.s_tjr" placeholder="全部">
                    <el-option key="0" label="全部" value="0"></el-option>
                    <el-option key="1" label="二级代理" value="1"></el-option>
                </el-select>

                <div style="height: 0.5rem;"></div>

                <span style="font-size: 14px;margin-left: 20px;">推荐人：</span>
                <el-input style="width: 168px;" placeholder="推荐人" clearable v-model="params.s_spuser"
                    @keyup.enter="doSearch">
                </el-input>

                <span style="font-size:15px;margin-left: 10px;">首充:</span>
                <el-select style="width: 80px;margin-left: 10px;" v-model="params.s_is_first" placeholder="全部">
                    <el-option key="all" label="全部" value="all"></el-option>
                    <el-option key="1" label="是" value="1"></el-option>
                    <el-option key="0" label="否" value="0"></el-option>
                </el-select>

                <el-select style="width: 120px;margin-left: 10px;" v-model="params.s_status" placeholder="全部状态">
                    <el-option key="0" label="全部状态" value="0"></el-option>
                    <el-option v-for="(item, idx) in tdata.paylog_status_arr" :key="idx" :label="item" :value="idx">
                    </el-option>
                </el-select>


                <span style="font-size: 14px;margin-left: 20px;">取消支付</span>
                <el-input style="width: 168px;" placeholder="订单号" clearable v-model="ddh">
                </el-input>
                <el-button v-if="power.update" @click="qxdd()">取消支付</el-button>

                <div style="height: 10px;"></div>




                <span style="font-size: 14px;margin-left: 10px;">支付通道</span>
                <el-input style="width: 180px;" placeholder="支付通道" clearable v-model="params.s_paytype_s"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">通道单号:</span>
                <el-input style="width: 180px;" placeholder="通道单号" clearable v-model="params.s_oldosn"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">用户账号</span>
                <el-input style="width: 180px;" placeholder="用户账号" clearable v-model="params.s_user_account"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">系统单号:</span>
                <el-input style="width: 180px;" placeholder="系统单号" clearable v-model="params.s_osn"
                    @keyup.enter="doSearch">
                </el-input>

                <span style="font-size: 14px;margin-left: 10px;">UTR</span>
                <el-input style="width: 180px;" placeholder="UTR" clearable v-model="params.s_utr"
                    @keyup.enter="doSearch">
                </el-input>

                <el-button type="primary" style="margin-left: 10px;" @click="doSearch">快捷查询</el-button>

                <div style="height: 10px;"></div>
            </template>

            <template #table="myScope">
                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="uid" label="UID" width="100"></el-table-column>
                <el-table-column prop="osn" :label="isTrans ? 'System Order No.' : '系统单号'"
                    width="180"></el-table-column>
                <el-table-column prop="out_osn" :label="isTrans ? 'Channel Order No.' : '通道单号'"
                    width="260"></el-table-column>
                <el-table-column prop="agent1_account" :label="isTrans ? 'First-level agent' : '一级代理'" width="100">
                </el-table-column>
                <el-table-column prop="agent2_account" :label="isTrans ? 'Second-level agent' : '二级代理'" width="120">
                </el-table-column>
                <el-table-column prop="account" :label="isTrans ? 'Account' : '用户账号'" width="140"></el-table-column>
                <!--        <el-table-column prop="nickname" label="用户昵称" width="120"></el-table-column>-->
                <el-table-column prop="zt_account" :label="isTrans ? 'Referer' : '推荐人'" width="120"></el-table-column>

                <el-table-column prop="money" :label="isTrans ? 'Money' : '充值金额'" width="130">
                    <template #default="{ row }">
                        <template v-if="row.receive_type == 4">
                            {{ row.money }} USDT
                        </template>
                        <template v-else>{{ row.money }}</template>
                    </template>
                </el-table-column>
                <el-table-column prop="is_first_flag" :label="isTrans ? 'First pay' : '首次充值'"
                    width="90"></el-table-column>
                <!-- <el-table-column prop="rate" :label="isTrans ? 'Rate' : '汇率'" width="90" v-if="false">
                    <template #default="{ row }">
                        {{ row.rate > 0 ? row.rate : '/' }}
                    </template>
                </el-table-column> -->
                <!-- <el-table-column prop="money" :label="isTrans ? 'Actual' : '实际到账'" width="120" v-if="false">
                    <template #default="{ row }">
                        {{ row.real_money > 0 ? row.real_money : '/' }}
                    </template>
                </el-table-column> -->
                <!--        <el-table-column prop="rate" label="汇率" width="100">
            <template #default="scope">
                <span v-if="scope.row.rate>0">{{scope.row.rate}}</span>
                <span v-else>/</span>
            </template>
        </el-table-column>
        <el-table-column prop="rate" label="实际到账" width="120">
            <template #default="scope">
                <span v-if="scope.row.real_money>0">{{scope.row.real_money}}</span>
                <span v-else>/</span>
            </template>
        </el-table-column>-->
                <el-table-column prop="receive_type" :label="isTrans ? 'Receive Account info' : '收款信息'" min-width="240"
                    v-if="viewAccountInfo">
                    <template #default="{ row }">
                        <div style="line-height: 16px;text-align: left;position: relative;" v-if="row.receive_type > 0">
                            <template v-if="row.receive_type == 4">
                                <div v-if="row.receive_address">USDT</div>
                                <div v-if="row.receive_protocol > 0">{{ row.receive_protocol_flag }}</div>
                                <div v-if="row.receive_address">{{ row.receive_address }}</div>
                                <div v-if="row.receive_qrcode" style="position: absolute;right: 0;top:0;">
                                    <img :src="imgFlag(row.receive_qrcode)" @click="onPreviewImg(row.receive_qrcode)"
                                        style="width: 40px;height: 40px;">
                                </div>
                            </template>
                            <template v-else-if="row.receive_type == 1">
                                <div>{{ row.receive_bank_name }}</div>
                                <div>{{ row.receive_ifsc }}</div>
                                <div>{{ row.receive_realname }}</div>
                                <div>{{ row.receive_account }}</div>
                            </template>
                            <template v-else-if="row.receive_type == 2 || row.receive_type == 3">
                                <div>{{ row.receive_type_flag }}</div>
                                <div>{{ row.receive_realname }}</div>
                                <div>{{ row.receive_account }}</div>
                                <div style="position: absolute;right: 0;top:0;">
                                    <img :src="imgFlag(row.receive_qrcode)" @click="onPreviewImg(row.receive_qrcode)"
                                        style="width: 40px;height: 40px;">
                                </div>
                            </template>
                        </div>
                        <div v-else>/</div>
                    </template>
                </el-table-column>

                <el-table-column prop="pay_remark" :label="isTrans ? 'Payment Account info' : '付款信息'" min-width="160"
                    v-if="viewAccountInfo">
                    <template #default="{ row }">
                        <div style="line-height: 16px;text-align: left;">
                            <!--                    <div v-if="row.pay_realname">{{row.pay_realname}}</div>-->
                            <div v-if="row.pay_remark">{{ row.pay_remark }}</div>
                            <div style="padding-top: 5px;">
                                <el-image v-for="(item, idx) in row.pay_banners" :src="imgFlag(item)"
                                    @click="onPreviewImg(item)"
                                    style="height: 40px;width: 40px;margin-right: 6px;display: inline-block;" />
                            </div>
                        </div>
                    </template>
                </el-table-column>

                <el-table-column prop="create_time" :label="isTrans ? 'Order time' : '下单时间'"
                    width="130"></el-table-column>

                <el-table-column prop="pay_time" :label="isTrans ? 'Pay time' : '支付时间'" width="130"></el-table-column>

                <el-table-column prop="status_flag" :label="isTrans ? 'Status' : '状态'" width="100"></el-table-column>
                <el-table-column prop="pay_type" :label="isTrans ? 'Channel' : '支付通道'" width="130"></el-table-column>
                <!-- <el-table-column prop="check_remark" :label="isTrans ? 'Check remark' : '审核备注'" width="100" v-if="false">
                </el-table-column> -->
                <el-table-column :label="isTrans ? 'Operate' : '操作'" width="260">
                    <template #default="{ row }">
                        <template v-if="power.check">
                            <el-button size="small"
                                v-if="row.pay_type == 'offline' && row.status >= 1 && row.status < 9"
                                @click="check($index, row)" type="success">审核</el-button>
                            <span v-else>/</span>
                        </template>

                        <template v-if="power.check">
                            <el-button size="small"
                                v-if="row.pay_type == 'offline' && row.status >= 1 && row.status < 9"
                                @click="checkbank(1, row)" type="success">到账</el-button>

                        </template>
                        <template v-if="power.check">
                            <el-button size="small"
                                v-if="row.pay_type == 'offline' && row.status >= 1 && row.status < 9 && row.status != 3"
                                @click="checkbank(2, row)" type="danger">未到账</el-button>
                        </template>


                        <template v-if="power.check_onlie">
                            <el-button size="small"
                                v-if="row.pay_type != 'offline' && row.status >= 1 && row.status < 9"
                                @click="checkbank_onlie(1, row)" type="success">到账</el-button>
                        </template>

                        <template v-if="power.check_onlie">
                            <el-button size="small"
                                v-if="row.pay_type != 'offline' && row.status >= 1 && row.status < 9 && row.status != 3"
                                @click="checkbank_onlie(2, row)" type="danger">未到账</el-button>
                        </template>



                        <template v-else>/</template>
                    </template>
                </el-table-column>
            </template>

            <template #summary="{ tdata }">
                <span>{{ isTrans ? 'Records' : '记录' }}：{{ tdata.count }}</span>
                <span>{{ isTrans ? 'Total money' : '总额' }}：{{ tdata.money }}</span>
            </template>

            <template #layer="{ tdata }">
                <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                    :width="configForm.width">
                    <el-form :label-width="configForm.labelWidth">
                        <el-form-item label="单号">
                            <el-input v-model="actItem.osn" autocomplete="off" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="钱包" style="margin-bottom: 0;">
                            充值钱包
                        </el-form-item>
                        <el-form-item label="额度">
                            <el-input v-model="actItem.money" autocomplete="off" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="订单状态" style="margin-bottom: 0;">
                            <el-radio-group v-model="checkForm.status">
                                <el-radio :label="3">未到账</el-radio>
                                <el-radio :label="9">已到账</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="付款凭证" style="margin-bottom: 0;">

                            <div style="padding-top: 5px;">
                                <el-image v-for="(item, idx) in actItem.pay_banners" :src="imgFlag(item)"
                                    @click="onPreviewImg(item)"
                                    style="height: 40px;width: 40px;margin-right: 6px;display: inline-block;" />
                            </div>

                        </el-form-item>
                        <el-form-item label="汇率" style="display: none;">
                            <el-input v-model="checkForm.rate" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="UTR">
                            <el-input v-model="actItem.pay_remark" autocomplete="off"></el-input>
                        </el-form-item>
                        <el-form-item label="备注">
                            <el-input type="textarea" v-model="checkForm.check_remark" autocomplete="off" rows="3">
                            </el-input>
                        </el-form-item>
                    </el-form>
                    <template #footer>
                        <span class="dialog-footer">
                            <el-button @click="configForm.visible = false">取消</el-button>
                            <el-button type="primary" @click="saveCheck">提交</el-button>
                        </span>
                    </template>
                </el-dialog>
            </template>

        </Page>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import Page from '../../components/Page.vue';
import { data } from 'dom7';
export default defineComponent({
    components: {
        Page
    }
})
</script>
<script lang="ts" setup>
import { ref, onMounted, reactive } from 'vue';
import { _alert, getSrcUrl, showImg } from "../../global/common";
import http from "../../global/network/http";
import { checkPower } from "../../global/user";

let isRequest = false
const configForm = ref({
    title: '',
    width: '540px',
    labelWidth: '100px',
    visible: false,
    isEdit: false
})

const viewAccountInfo = ref(false)

//权限控制
const power = reactive({
    check: checkPower('Finance_paylog_check'),
    check_onlie: checkPower('Finance_paylog_check_onlie'),
    update: checkPower('Finance_paylog_update')
})

const pageRef = ref()
let actItem = ref<any>()
const dataForm = ref<any>({
    id: 0
})

const checkForm = ref<any>({
    id: 0,
    rate: '1',
    status: '',
    check_remark: ''
})

const check = (idx: number, item: any) => {
    actItem.value = item
    checkForm.value.id = item.id
    checkForm.value.check_remark = item.check_remark
    configForm.value.visible = true
    configForm.value.title = '充值审核'
}


const checkbank = (type: number, item: any) => {
    actItem.value = item
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Finance&a=bank_check',
        data: { osn: item.osn, type: type }
    }).then((res: any) => {
        _alert(res.msg)
        setTimeout(() => {
            isRequest = false
        }, 1000)
        for (let i in res.data) {
            actItem.value[i] = res.data[i]
        }
    })
}
const checkbank_onlie = (type: number, item: any) => {
    actItem.value = item
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Finance&a=bank_check_onlie',
        data: { osn: item.osn, type: type }
    }).then((res: any) => {
        _alert(res.msg)
        setTimeout(() => {
            isRequest = false
        }, 1000)
        for (let i in res.data) {
            actItem.value[i] = res.data[i]
        }
    })
}


const ddh = ref()

const qxdd = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Finance&a=qxdd',
        data: { osn: ddh.value }
    }).then((res: any) => {
        _alert(res.msg)
        setTimeout(() => {
            isRequest = false
        }, 2000)
    })
}


const saveCheck = () => {
    if (!checkForm.value.status) {
        _alert('请选择状态')
        return
    }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Finance&a=paylog_check',
        data: checkForm.value
    }).then((res: any) => {
        _alert(res.msg)
        setTimeout(() => {
            isRequest = false
        }, 3000)
        if (res.code != 1) {
            return
        }
        configForm.value.visible = false
        checkForm.value.check_remark = ''
        for (let i in res.data) {
            actItem.value[i] = res.data[i]
        }
    })
}

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

const payFinish = ({ row, rowIndex }) => {
    if (row.status == 9) {
        return 'paylog-finish'
    }
    return ''
}

const onPreviewImg = (src: string) => {
    showImg(getSrcUrl(src))
}

//翻译相关
const isTrans = ref(false)
const onTrans = (ev: any) => {
    if (ev == 1) {
        isTrans.value = true
    } else {
        isTrans.value = false
    }
    pageRef.value.doSearch()
}

</script>
<style>
.el-table .paylog-finish .el-table__cell {
    color: red !important;
}
</style>



<!-- <style scoped>


.el-pagination.is-background .el-pager li:not(.disabled).active {
    color: aliceblue;
}

.el-input-group__prepend {
    background: none;
}

.divs {
    background-image: url('../../assets/11.jpg');
    background-repeat: no-repeat;
    background-size: 100%;
    width: 100%;
    height: 100%;
    background-position: center;
    background-size: cover;
    background-repeat: no-repeat;
}

:root {
    --el-color-white: #00000000 !important;
    --el-background-color-base: #2196f3 !important;
}

.el-table .el-table__cell {
    color: aliceblue;
}
</style> -->