<template>
    <!-- <Page ref="pageRef" url="c=Finance&a=cashlog" :need-time="true" @success="onPageSuccess"> -->

    <div class="divs">

        <Page :need-time="true" XLSXname="提现管理" url="c=Finance&a=cashlog" ref="pageRef" @success="onPageSuccess">
            <template #btn>
                <!--            <el-button v-if="power.check_all" type="success" size="small" @click="checkAll">一键审核</el-button>-->
            </template>

            <template #search="{ params, tdata, doSearch }">

                <span style="font-size: 14px;margin-left: 10px;">收款账号长度：</span>
                <el-select size="small" style="width: 90px;" v-model="params.s_lt14" placeholder="全部">
                    <el-option key="0" label="全部" value="0"></el-option>
                    <el-option key="1" label="≥14" value="1"></el-option>
                    <el-option key="2" label="<14" value="2"></el-option>
                </el-select>

                <span style="font-size: 14px;margin-left: 10px;">翻译：</span>
                <el-select size="small" style="width: 70px;" v-model="params.s_trans" placeholder="否" @change="onTrans">
                    <el-option key="0" label="否" value="0"></el-option>
                    <el-option key="1" label="是" value="1"></el-option>
                </el-select>
                <span style="font-size: 14px;margin-left: 10px;">代付渠道：</span>
                <el-select size="small" style="width: 120px;" v-model="s_paytype" placeholder="">
                    <el-option v-for="(item, idx) in tdata.dtype" :key="idx" :label="item.type" :value="item.type">
                    </el-option>
                </el-select>
                <span style="font-size: 14px;margin-left: 10px;">代付状态：</span>
                <el-select size="small" style="width: 120px;" v-model="params.s_pay_status" placeholder="全部状态">
                    <el-option key="all" label="全部状态" value="all">
                    </el-option>
                    <el-option v-for="(item, idx) in tdata.pay_status_arr" :key="idx" :label="item" :value="idx">
                    </el-option>
                </el-select>

                <el-button v-if="power.checktk" @click="checkAllShowifsc = true">退款</el-button>
                <span style="font-size: 14px;margin-left: 10px;">审核状态：</span>
                <el-select size="small" style="width: 120px;" v-model="params.s_status" placeholder="全部状态">
                    <el-option key="0" label="全部状态" value="0">
                    </el-option>
                    <el-option v-for="(item, idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                    </el-option>
                </el-select>

                <span style="font-size: 14px;margin-left: 10px;">金额范围：</span>
                <el-input size="small" style="width: 108px;" placeholder="起始金额" clearable v-model="params.s_money_from"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;">&nbsp;&nbsp;到&nbsp;&nbsp;</span>

                <el-input size="small" style="width: 108px;" placeholder="结束金额" clearable v-model="params.s_money_to"
                    @keyup.enter="doSearch">
                </el-input>

                <div style="height: 10px;"></div>
                <span style="font-size: 14px;margin-left: 10px;">提现模式：</span>


                <el-select size="small" style="width: 120px;" v-model="falseflg" placeholder="处理失败">
                    <el-option key="0" label="否" value="0">
                    </el-option>
                    <el-option key="1" label="是" value="1">
                    </el-option>
                </el-select>

                <el-select size="small" style="width: 120px;" v-model="params.txflg" placeholder="全部状态">

                    <el-option key="0" label="关闭" value="0">
                    </el-option>
                    <el-option key="1" label="急速模式" value="1">
                    </el-option>
                    <el-option key="2" label="快速模式" value="2">
                    </el-option>
                    <el-option key="3" label="处理失败" value="3">
                    </el-option>
                </el-select>
                <span style="font-size: 14px;margin-left: 10px;">历史订单：</span>
                <el-input size="small" style="width: 180px;" placeholder="历史订单号" clearable v-model="params.s_oldosn"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">代付渠道：</span>
                <el-input size="small" style="width: 180px;" placeholder="代付渠道" clearable v-model="params.s_paytype_s"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">用户账号：</span>
                <el-input size="small" style="width: 180px;" placeholder="用户账号" clearable
                    v-model="params.s_user_account" @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">订单：</span>
                <el-input size="small" style="width: 180px;" placeholder="订单号" clearable v-model="params.s_osn"
                    @keyup.enter="doSearch">
                </el-input>
                <el-button size="small" type="primary" style="margin-left: 10px;" ref="kjcspageRef"
                    @click="doSearch">快捷查询</el-button>
                <div style="height: 10px;"></div>

                <!-- <span style="font-size: 14px;margin-left: 10px;">代付通道：</span>
            <el-select size="small" style="width: 120px;" v-model="s_paytype" placeholder="代付通道">
                <el-option key="0" label="代付通道" value="0">
                </el-option>
                <el-option v-for="(item, idx) in tdata.paytype_arr" :key="idx" :label="item.name" :value="item.type">
                </el-option>
            </el-select> -->
                <span style="font-size: 14px;margin-left: 10px;">收款账号：</span>
                <el-input size="small" style="width: 180px;" placeholder="收款账号" clearable
                    v-model="params.receive_account" @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">ID：</span>
                <el-input size="small" style="width: 180px;" placeholder="ID" clearable v-model="params.id"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">一级代理：</span>
                <el-input size="small" style="width: 180px;" placeholder="一级代理" clearable v-model="params.pidg1"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">二级代理：</span>
                <el-input size="small" style="width: 180px;" placeholder="二级代理" clearable v-model="params.pidg2"
                    @keyup.enter="doSearch">
                </el-input>
                <span style="font-size: 14px;margin-left: 10px;">上级账号：</span>
                <el-input size="small" style="width: 180px;" placeholder="上级账号" clearable v-model="params.pid"
                    @keyup.enter="doSearch">
                </el-input>
                <div style="height: 10px;"></div>

                <span style="font-size: 14px;margin-left: 10px;">通道到账：</span>
                <el-date-picker size="small" type="date" :style="{ marginLeft: '10px', width: '150px' }" clearable
                    v-model="params.s_start_time2_flag" placeholder="开始时间">
                </el-date-picker>
                <el-date-picker size="small" type="date" :style="{ marginLeft: '10px', width: '150px' }" clearable
                    v-model="params.s_end_time2_flag" placeholder="结束时间">
                </el-date-picker>
                <span style="font-size: 14px;margin-left: 20px;">用户提现：</span>
            </template>

            <template #table="myScope">
                <el-table-column prop="checked" :label="isTrans ? 'Option' : '选择'" width="50" fixed>
                    <template #default="{ row, $index }">
                        <template v-if="power.check && (row.status == 1 || (row.status == 9 && row.pay_status == 3))">
                            <el-checkbox v-model="selectAllArr[$index]" size="large"
                                @change="onSelectItem($index, $event)">
                            </el-checkbox>
                        </template>
                        <template v-else>/</template>
                    </template>
                </el-table-column>

                <el-table-column :label="isTrans ? 'Operate' : '操作'" width="160" fixed>
                    <template #default="{ row, $index }">
                        <template v-if="power.updata">
                            <el-button v-if="!isTrans" size="small" @click="update(row)" type="success">
                                {{ isTrans ? 'Updata' : '修改' }} </el-button>
                        </template>
                        <template v-if="power.check">
                            <template v-if="row.status == 1 || (row.status == 9 && row.pay_status == 3)">
                                <el-button size="small" @click="saveCheck2(row, 9, $index)" type="success">
                                    {{ isTrans ? 'Pass' : '通过' }}</el-button>
                                <el-button size="small" @click="saveCheck2(row, 3, $index)" type="danger">
                                    {{ isTrans ? 'Reject' : '驳回' }}</el-button>

                            </template>
                            <span v-else>/</span>

                        </template>
                        <template v-else-if="power.checkReject">
                            <template v-if="row.status == 1 || (row.status == 9 && row.pay_status == 3)">
                                <el-button size="small" @click="saveCheck2(row, 3, $index)" type="danger">
                                    {{ isTrans ? 'Reject' : '驳回' }}</el-button>
                            </template>
                            <span v-else>/</span>
                        </template>
                        <template v-else>/</template>


                    </template>
                </el-table-column>

                <el-table-column prop="pay_type" :label="isTrans ? 'Channel' : '代付通道'" width="100"
                    fixed></el-table-column>
                <el-table-column prop="pay_status_flag" :label="isTrans ? 'Payment status' : '代付状态'" width="100" fixed>
                    <template #default="{ row }">
                        {{ row.pay_status_flag }}
                        <div @click="onShowPayMsg(row)" v-if="row.pay_status == 3"
                            style="color: #f56c6c;cursor: pointer;">
                            {{ isTrans ? 'View errors' : '查看错误' }}</div>
                        <div @click="getifsc(row)" v-if="row.pay_status == 3" style="color: #f56c6c;cursor: pointer;">
                            {{ isTrans ? 'View errors' : '查看ifsc' }}</div>
                    </template>
                </el-table-column>
                <el-table-column prop="status_flag" :label="isTrans ? 'Order status' : '订单状态'" width="100" fixed>
                </el-table-column>
                <el-table-column prop="pay_msg" :label="isTrans ? 'Error inof' : '错误信息'" width="100" fixed>

                </el-table-column>

                <el-table-column prop="id" label="ID" width="80"></el-table-column>
                <el-table-column prop="agent1_account" :label="isTrans ? 'First-level agent' : '一级代理'" width="120">
                </el-table-column>
                <el-table-column prop="agent2_account" :label="isTrans ? 'Second-level agent' : '二级代理'" width="120">
                </el-table-column>
                <el-table-column prop="osn" :label="isTrans ? 'Order No.' : '订单号'" width="190">
                    <template #default="{ row }">
                        <div v-if="row.oldosn != 0">
                            <span style="color: red;">{{ row.oldosn }}</span>
                            <br>
                            <span style="color: black;">{{ row.osn }}</span>
                        </div>
                        <div v-else>
                            {{ row.osn }}
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="account" :label="isTrans ? 'Account' : '会员账号'" width="140"></el-table-column>
                <!-- <el-table-column prop="nickname" label="用户昵称" width="120"></el-table-column>-->
                <el-table-column prop="zt_account" :label="isTrans ? 'Referer' : '推荐人'" width="120"></el-table-column>

                <el-table-column prop="money" :label="isTrans ? 'Money' : '提现金额'" width="100"></el-table-column>
                <el-table-column prop="fee" :label="isTrans ? 'Fee' : '手续费'" width="80"></el-table-column>
                <el-table-column prop="real_money" :label="isTrans ? 'Actual' : '收到的金额'" width="100"></el-table-column>
                <el-table-column prop="receive_type" :label="isTrans ? 'Bank Account info' : '收款信息'" min-width="300">
                    <template #default="{ row }">
                        <div style="line-height: 16px;text-align: left;position: relative;">
                            <div>{{ row.receive_type_flag }}</div>
                            <div v-if="row.bank_name">BankName：{{ row.bank_name }}</div>
                            <div v-if="row.receive_bank_name">BankNameR：{{ row.receive_bank_name }}</div>
                            <div v-if="row.receive_ifsc">IFSC：{{ row.receive_ifsc }}</div>
                            <div v-if="row.receive_realname">Realname：{{ row.receive_realname }}</div>
                            <div v-if="row.receive_account">Account：{{ row.receive_account }}</div>
                            <div v-if="row.receive_address_type > 0">{{ row.receive_address_type_flag }}</div>
                            <div v-if="row.receive_address_protocol > 0">{{ row.receive_address_protocol_flag }}</div>
                            <div v-if="row.receive_address_channel > 0">{{ row.receive_address_channel_flag }}</div>
                            <div v-if="row.receive_address">Address：{{ row.receive_address }}</div>
                            <div v-if="row.receive_phone">Phone：{{ row.receive_phone }}</div>
                            <div v-if="row.receive_email">Email：{{ row.receive_email }}</div>
                            <div v-if="row.receive_qrcode" style="position: absolute;right: 0;top:0;">
                                <img :src="imgFlag(row.receive_qrcode)" @click="onPreviewImg(row.receive_qrcode)"
                                    style="width: 40px;">
                            </div>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column prop="create_time" :label="isTrans ? 'Order time' : '提交时间'"
                    width="130"></el-table-column>
                <el-table-column prop="check_time" :label="isTrans ? 'Check time' : '审核时间'"
                    width="130"></el-table-column>
                <el-table-column prop="pay_time" :label="isTrans ? 'Pay time' : '到账时间'" width="130"></el-table-column>


            </template>

            <template #summary="{ tdata }">
                <div v-if="power.check_all" class="plActionBox" style="text-align: left; ">
                    <span style="display: inline-block;  text-align: center;">
                        <el-checkbox v-model="selectAll" size="small" @change="onSelectAll"></el-checkbox>
                    </span>
                    <el-button size="small" type="success" @click="onPlAction(9)">{{ isTrans ? 'Batch pass' : '批量通过' }}
                    </el-button>
                    <el-button size="small" type="danger" @click="onPlAction(3)">{{ isTrans ? 'Bulk rejection' : '批量驳回'
                        }}
                    </el-button>

                    <span>{{ isTrans ? 'Records' : '记录' }}：{{ tdata.count }}</span>
                    <span>{{ isTrans ? 'Total money' : '总额' }}：{{ tdata.money }}</span>
                    <span>{{ isTrans ? 'Total money' : '实付总额' }}：{{ tdata.real_money }}</span>
                    <span>{{ isTrans ? 'Total money' : '笔数' }}：{{ tdata.money1 }}</span>
                    <span>{{ isTrans ? 'Total money' : '金额' }}：{{ tdata.real_money1 }}</span>
                </div>

            </template>

            <template #layer="{ tdata }">
                <!--弹出层-->
                <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                    :width="configForm.width">
                    <el-form>
                        <el-form-item label="单号" :label-width="configForm.labelWidth">
                            <el-input size="small" v-model="actItem.osn" autocomplete="off" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="提现额度" :label-width="configForm.labelWidth">
                            <el-input size="small" v-model="actItem.money" autocomplete="off" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="手续费" :label-width="configForm.labelWidth">
                            <el-input size="small" v-model="actItem.fee" autocomplete="off" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="实际到账" :label-width="configForm.labelWidth">
                            <el-input size="small" v-model="actItem.real_money" autocomplete="off" disabled></el-input>
                        </el-form-item>
                        <el-form-item label="订单状态" :label-width="configForm.labelWidth" style="margin-bottom: 0;">
                            <el-radio-group v-model="checkForm.status">
                                <el-radio :label="1">待审核</el-radio>
                                <el-radio :label="3">不通过</el-radio>
                                <el-radio :label="9">已通过</el-radio>
                            </el-radio-group>
                        </el-form-item>
                        <el-form-item label="支付状态" :label-width="configForm.labelWidth" style="margin-bottom: 0;">
                            <el-radio-group v-model="checkForm.pay_status">
                                <el-radio :label="3">失败</el-radio>
                                <el-radio :label="9">已支付</el-radio>
                            </el-radio-group>
                        </el-form-item>


                        <el-form-item label="备注" :label-width="configForm.labelWidth">
                            <el-input size="small" type="textarea" v-model="checkForm.check_remark" autocomplete="off"
                                rows="3">
                            </el-input>
                        </el-form-item>
                    </el-form>
                    <template #footer>
                        <span class="dialog-footer">
                            <el-button size="small" v-if="power.checktk" @click="goWalletLog()"
                                type="primary">账变记录</el-button>
                            <el-button v-if="power.checktk" @click="checktk()">退款</el-button>
                            <el-button @click="configForm.visible = false">取消</el-button>
                            <el-button type="primary" @click="saveCheck">提交</el-button>
                        </span>
                    </template>
                </el-dialog>

                <el-dialog title="一键审核" v-model="checkAllShow" :close-on-click-modal="false" width="500px">
                    <el-form label-width="100">
                        <el-form-item label="选择状态：" style="margin-bottom: 0;">
                            <el-radio-group v-model="checkAllForm.status">
                                <el-radio :label="3">全部不通过</el-radio>
                                <el-radio :label="9">全部通过</el-radio>
                            </el-radio-group>
                        </el-form-item>
                    </el-form>
                    <template #footer>
                        <span class="dialog-footer">
                            <el-button @click="checkAllShow = false">取消</el-button>
                            <el-button type="primary" @click="checkAllSave">提交</el-button>
                        </span>
                    </template>
                </el-dialog>

                <el-dialog :key="ifsckey" title="查看ifsc" v-model="checkAllShow2" :close-on-click-modal="false"
                    width="500px">
                    <div style="width: 100%; height: 100%;">{{ ifscres }}</div>
                    <!-- <iframe id="ifesc" :src="urlifsc" style="height: calc(100% - 50px)" width="100%" frameborder="no" border="0"
                    marginwidth="0" marginheight="0" scrolling="no" allowtransparency="yes"></iframe> -->

                    <template #footer>
                        <span class="dialog-footer">
                            <el-button @click="checkAllShow2 = false">关闭</el-button>
                        </span>
                    </template>
                </el-dialog>


                <el-dialog :key="ifsckey" title="手动退款" v-model="checkAllShowifsc" :close-on-click-modal="false"
                    width="500px">
                    <div style="width: 100%; height: 100%;">
                        <el-form>
                            <el-form-item label="单号" :label-width="configForm.labelWidth">
                                <el-input size="small" v-model="osn" autocomplete="off"></el-input>
                            </el-form-item>
                        </el-form>
                    </div>

                    <template #footer>
                        <span class="dialog-footer">
                            <el-button size="small" v-if="power.checktk" @click="goWalletLog()"
                                type="primary">账变记录</el-button>
                            <el-button v-if="power.checktk" @click="checktk()">退款</el-button>

                            <el-button @click="checkAllShowifsc = false">关闭</el-button>
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

export default defineComponent({
    components: {
        Page
    }
})
</script>

<script lang="ts" setup>

import { ref, onMounted, reactive } from 'vue'
import { useStore } from "vuex";
import http from "../../global/network/http";
import { getZero, _alert, getSrcUrl, showImg } from "../../global/common";
import { checkPower } from "../../global/user";
import { useRouter } from "vue-router";
import { ElMessageBox } from 'element-plus';

let isRequest = false
const configForm = ref({
    title: '',
    width: '540px',
    labelWidth: '100px',
    visible: false,
    isEdit: false
})

const store = useStore()
const router = useRouter()

//权限控制
const power = reactive({
    check: checkPower('Finance_cashlog_check'),
    checkReject: checkPower('Finance_cashlog_check_reject'),
    check_all: checkPower('Finance_cashlog_check_all'),
    updata: checkPower('Finance_cashlog_updata'),
    checktk: checkPower('Finance_cashlog_checktk'),
})

let actItem = ref<any>()
const dataForm = ref<any>({
    id: 0
})

const s_paytype = ref('')
const checkForm = ref<any>({
    id: 0,
    status: '',
    check_remark: ''
})

const checkAllShow2 = ref(false)
const check = (idx: number, item: any) => {
    actItem.value = item
    checkForm.value.id = item.id
    configForm.value.visible = true
    configForm.value.title = '提现审核'
}
const falseflg = ref()
const kjcspageRef = ref()
const pageRef = ref()
const selectAllArr = ref([])
const selectAll = ref(false)

//分页成功
const onPageSuccess = (td: any) => {
    selectAllArr.value = []
    if (!power.check) {
        return
    }
    for (let i in td.list) {
        let row = td.list[i]
        if (row.status == 1 || (row.status == 9 && row.pay_status == 3)) {
            selectAllArr.value[i] = false
        }
    }
}

const update = (item: any,) => {
    returncashitem.value = item;
    checkForm.value.status = '';
    checkForm.value.pay_status = '';
    actItem.value = item;
    configForm.value.visible = true;
    checkForm.value.id = item.id;
    console.log(item);
}
const urlifsc = ref('')
const ifsckey = ref(0)
const ifscres = ref('')
const returncashitem = ref();
const osn = ref();
const goWalletLog = () => {
    // router.push({name:'Finance_walletLog',params:{wid:item.id}})
    if (returncashitem.value)
        http({
            url: 'c=Finance&a=getwid',
            params: {
                uid: returncashitem.value.uid,
            }
        }).then((res: any) => {
            returncashitem.value = [];
            router.push({ name: 'Finance_walletLog', query: { wid: res.data.wid } })
        });
    else if (osn.value)
        http({
            url: 'c=Finance&a=getwid',
            params: {
                osn: osn.value
            }
        }).then((res: any) => {
            returncashitem.value = [];
            router.push({ name: 'Finance_walletLog', query: { wid: res.data.wid } })
        });
    else _alert('请先输入订单号')
}
const checktk = () => {
    if (returncashitem.value)
        http({
            url: 'c=Finance&a=checktk',
            params: {
                osn: returncashitem.value.osn
            }
        }).then((res: any) => {
            returncashitem.value = res.data
        });
    else if (osn.value) {
        http({
            url: 'c=Finance&a=checktk',
            params: {
                osn: osn.value
            }
        }).then((res: any) => {
            _alert(res.msg)
            returncashitem.value = res.data
        });
    } else _alert('请先选择提现记录')
}
const getifsc = (ifsc: any) => {
    ifsckey.value++;
    checkAllShow2.value = true;
    http({
        url: 'c=Finance&a=Getifsc',
        params: {
            ifsc: ifsc.receive_ifsc
        }
    }).then((res: any) => {
        ifscres.value = res.data
    });
    //urlifsc.value =  + ifsc.receive_ifsc;

}
const saveCheck = () => {
    if (!checkForm.value.status) {
        _alert('请选择状态')
        return
    }
    if (!checkForm.value.pay_status) {
        _alert('请选择支付状态')
        return
    }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }


    http({
        url: 'c=Finance&a=cashlog_check2',
        data: checkForm.value
    }).then((res: any) => {
        _alert(res.msg)
        isRequest = false
        if (res.code != 1) {
            return
        }
        configForm.value.visible = false
        checkForm.value.status = ''
        checkForm.value.check_remark = ''
        for (let i in res.data) {
            actItem.value[i] = res.data[i]
        }
    })
}

//单项提交
const saveCheck2 = (item: any, status: number, index: number) => {
    actItem.value = item
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    if (status == 9 && s_paytype.value == "") {
        _alert("请选择代付通道")
        isRequest = false
        return
    }
    http({
        url: 'c=Finance&a=cashlog_check',
        data: {
            id: item.id,
            status: status,
            check_remark: '',
            s_paytype: s_paytype.value
        }
    }).then((res: any) => {
        _alert(res.msg)
        isRequest = false
        if (res.code != 1) {
            return
        }
        for (let i in res.data) {
            actItem.value[i] = res.data[i]
        }

        let newIdxArr = []
        for (let i in selectAllArr.value) {
            if (i != index) {
                newIdxArr[i] = selectAllArr.value[i]
            }
        }
        selectAllArr.value = newIdxArr
        initSelectAll()
    })
}

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

const onPreviewImg = (src: string) => {
    showImg(getSrcUrl(src))
}
const checkAllShowifsc = ref(false)
const checkAllShow = ref(false)
const checkAllForm = reactive({
    status: 0
})

const checkAll = () => {
    checkAllShow.value = true
}

const checkAllSave = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Finance&a=cashlog_check_all',
        data: {
            status: checkAllForm.status
        }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                checkAllShow.value = true
                router.go(0)
            }
        })
    })
}

const onShowPayMsg = (item: any) => {
    ElMessageBox.alert(item.pay_msg, {
        confirmButtonText: '关闭',
        callback: (action) => {

        }
    })
}

//全选
const onSelectAll = (ev: Boolean) => {
    for (let i in selectAllArr.value) {
        selectAllArr.value[i] = ev
    }
}

//单选
const onSelectItem = (idx: number, ev: Boolean) => {
    if (!ev) {
        selectAll.value = false
    } else {
        initSelectAll()
    }
}

const initSelectAll = () => {
    let isAll = true
    console.log(selectAllArr.value)
    if (selectAllArr.value.length < 1) {
        isAll = false
    } else {
        for (let i in selectAllArr.value) {
            if (!selectAllArr.value[i]) {
                isAll = false
                break
            }
        }
    }
    selectAll.value = isAll
}

const getActionIdxs = () => {
    let idxs = []
    for (let i in selectAllArr.value) {
        if (selectAllArr.value[i]) {
            idxs.push(i)
        }
    }
    return idxs
}

const getActionIdxById = (id: number) => {
    let idx = -1
    for (let i in pageRef.value.tableData.list) {
        if (pageRef.value.tableData.list[i].id == id) {
            idx = i
        }
    }
    return idx
}

const getActionIds = () => {
    let ids = []
    for (let i in selectAllArr.value) {
        if (selectAllArr.value[i]) {
            ids.push(pageRef.value.tableData.list[i].id)
        }
    }
    return ids
}

//批量操作
const onPlAction = (status: number) => {
    // pageRef.value.multipleTable.toggleAllSelection()
    let idxs = getActionIdxs()
    if (idxs.length < 1) {
        _alert('至少需要选择一项')
        return
    }
    if (status == 9 && s_paytype.value == "") {
        _alert("请选择代付通道")
        return
    }
    let ids = getActionIds()
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Finance&a=cashlog_check_all',
        data: {
            falseflg: falseflg.value,
            ids: ids,
            status: status,
            s_paytype: s_paytype.value
        }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                // pageRef.value.doSearch()
                for (let i in res.data.list) {
                    let resItem = res.data.list[i]
                    let index = getActionIdxById(resItem.id)
                    for (let j in resItem.data) {
                        pageRef.value.tableData.list[index][j] = resItem.data[j]
                    }
                }

                selectAll.value = false
                selectAllArr.value = []
                isRequest = false
            }
        })
    })
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

onMounted(() => {
    document.onkeydown = (e) => {
        //事件对象兼容 
        let e1 = e || event || window.event || arguments.callee.caller.arguments[0]
        checkAllShow2.value = false;
    }
})
</script>