<template>
    <Page ref="pageRef" XLSXname="用户管理" url="c=User&a=user" @success="onPageSuccess">
        <template #btn="myScope">
            <el-button type="danger" size="small" @click="DisableStatus(1, 'status')">禁用</el-button>
            <el-button size="small" @click="DisableStatus(2, 'status')">解禁</el-button>
            <el-button type="danger" size="small" @click="DisableStatus(1, 'icode_status')">验证码禁用</el-button>
            <el-button size="small" @click="DisableStatus(0, 'icode_status')">验证码解禁</el-button>
            <el-button type="danger" v-if="power.yxupdata" @click="DisableStatus(0, 'first_pay_day', 'first_pay_day')"
                size="small">无效</el-button>
            <el-button size="small" v-if="power.yxupdata"
                @click="DisableStatus(1, 'first_pay_day', 'first_pay_day')">有效</el-button>
            <el-button v-if="power.transfer" type="warning" size="small" @click="onTransfer1">同步用户层级</el-button>
            <el-button v-if="power.transfer" type="warning" size="small" @click="onTransfer2">同步单个用户层级</el-button>
            <el-button v-if="power.transfer" type="warning" size="small" @click="onTransfer">转移下级</el-button>
            <el-button v-if="power.transfer" type="warning" size="small" @click="onTransfer3">转移自己</el-button>
            <el-button v-if="power.update && store.state.user.gid <= 71" type="success" size="small"
                @click="add(myScope)">添加账号</el-button>

        </template>

        <template #search="{ params, tdata, doSearch }">
            <span style="font-size: 14px;margin-left: 10px;">翻译：</span>
            <el-select size="small" style="width: 70px;" v-model="params.s_trans" placeholder="否" @change="onTrans">
                <el-option key="0" label="否" value="0"></el-option>
                <el-option key="1" label="是" value="1"></el-option>
            </el-select>
            <el-select size="small" style="width: 100px;" v-model="params.s_has_pay" placeholder="有充值">
                <el-option key="all" label="有充值" value="all">
                </el-option>
                <el-option v-for="(item, idx) in store.state.config.yes_or_no" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
            <el-input size="small" style="width: 260px;margin-left: 10px;" placeholder="输入账号" clearable v-model="params.s_keyword3"
                @keyup.enter="doSearch">
                <template #prepend>查询上级</template>
            </el-input>
            <el-select size="small" style="width: 120px;margin-left: 10px;" v-model="params.s_gid" placeholder="全部分组">
                <el-option key="all" label="全部分组" value="all"></el-option>
                <el-option v-for="(item, idx) in tdata.sys_group" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
            <el-input size="small" style="width: 260px;margin-left: 10px;" placeholder="上级用户账号" clearable v-model="params.s_keyword2"
                @keyup.enter="doSearch">
                <template #prepend>团队搜索</template>
            </el-input>
            <el-input size="small" style="width: 260px;margin-left: 10px;" placeholder="上级用户账号" clearable v-model="params.s_keyword4"
                @keyup.enter="doSearch">
                <template #prepend>直推搜索</template>
            </el-input>
            <div style="height: 10px;"></div>
            <el-input size="small" style="width: 240px;margin-left: 10px;" placeholder="起始金额范围" clearable v-model="params.moneyFrom"
                @keyup.enter="doSearch">
                <template #prepend>起始金额范围</template>
            </el-input>
            <span>&nbsp;至&nbsp;</span>
            <el-input size="small" style="width: 150px;" placeholder="结束金额范围" clearable v-model="params.moneyTo"
                @keyup.enter="doSearch">
            </el-input>
            <span style="margin-left: 10px;color: #909399;">注册时间：</span>
            <el-date-picker size="small" v-model="params.regTimeRange" type="datetimerange" align="right" start-placeholder="开始日期"
                end-placeholder="结束日期"></el-date-picker>
            <span style="font-size: 14px;margin-left: 10px;">状态：</span>
            <el-select size="small" style="width: 90px;" v-model="params.status" placeholder="全部">
                <el-option key="0" label="全部" value="0"></el-option>
                <el-option key="1" label="禁用" value="1"></el-option>
                <el-option key="2" label="正常" value="2"></el-option>
            </el-select>
            <div style="height: 10px;"></div>
            <el-input size="small" style="width: 250px;margin-left: 10px;" placeholder="登录ip" clearable v-model="params.s_loginip"
                @keyup.enter="doSearch">
                <template #prepend>登录ip</template>
            </el-input>
            <el-input size="small" style="width: 250px;margin-left: 10px;" placeholder="上级用户账号" clearable v-model="params.s_regip"
                @keyup.enter="doSearch">
                <template #prepend>注册ip</template>
            </el-input>
            <el-input size="small" style="width: 250px;margin-left: 10px;" placeholder="搜索银行卡" clearable v-model="params.s_bankc"
                @keyup.enter="doSearch">
                <template #prepend>银行卡</template>
            </el-input>
        </template>
        <template #table="myScope">

            <el-table-column prop="checked" :label="'选择'" width="50" fixed>
                <template #default="{ row, $index }">
                    <el-checkbox v-model="selectAllArr[$index]" size="large"
                        @change="onSelectItem($index, $event)"></el-checkbox>
                </template>
            </el-table-column>

            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="account" :label="isTrans ? '账号' : 'account'" width="130"></el-table-column>
            <el-table-column prop="bk_account" :label="isTrans ? '银行卡' : 'bank account'" width="130"></el-table-column>
            <!--        <el-table-column prop="usn" :label="isTrans ? '机构编号':''" width="130"></el-table-column>-->
            <el-table-column prop="phone" :label="isTrans ? '手机号' : 'phone'" width="120"></el-table-column>
            <el-table-column prop="nickname" :label="isTrans ? '昵称' : 'nickname'" width="120"></el-table-column>
            <!-- <el-table-column prop="headimgurl" :label="isTrans ? '头像' : 'head'" width="80" class-name="imgCellBox">
                <template #default="scope">
                    <el-image style="width: 40px;height: 40px;vertical-align: middle;" v-if="scope.row.headimgurl"
                        fit="cover" :src="imgFlag(scope.row.headimgurl)" hide-on-click-modal
                        :preview-src-list="[imgFlag(scope.row.headimgurl)]">
                    </el-image>
                </template>
            </el-table-column> -->
            <el-table-column prop="gname" :label="isTrans ? '分组' : 'gname'" width="100">
                <template #default="scope">
                    <div v-if="isTrans" style="line-height: 20px;">{{ scope.row.gname }}</div>
                    <div v-else style="line-height: 20px;">User</div>
                </template>
            </el-table-column>
            <el-table-column prop="icode" :label="isTrans ? '邀请码' : 'icode'" width="80"></el-table-column>
            <el-table-column prop="pid" :label="isTrans ? '邀请人' : 'pid'" width="120">
                <template #default="scope">
                    <div v-if="scope.row.pid" style="line-height: 20px;">{{ scope.row.p_account }}</div>
                </template>
            </el-table-column>
            <el-table-column prop="pid" :label="isTrans ? '一级代理' : 'pid'" width="120">
                <template #default="scope">
                    <div v-if="scope.row.pidg1" style="line-height: 20px;">{{ scope.row.p1_account }}</div>
                </template>
            </el-table-column>
            <el-table-column prop="pid" :label="isTrans ? '二级代理' : 'pid'" width="120">
                <template #default="scope">
                    <div v-if="scope.row.pidg2" style="line-height: 20px;">{{ scope.row.p2_account }}</div>
                </template>
            </el-table-column>
            <el-table-column prop="lottery" :label="isTrans ? '剩余抽奖' : 'lottery'" width="90"></el-table-column>
            <el-table-column prop="xf" :label="isTrans ? '总消费' : ''" width="100"></el-table-column>

            <el-table-column prop="reg_time" :label="isTrans ? '注册时间' : 'reg_time'" width="100"></el-table-column>
            <el-table-column prop="login_time" :label="isTrans ? '登录时间' : 'login_time'" width="100"></el-table-column>
            <el-table-column prop="login_ip" :label="isTrans ? '登录ip' : 'login_ip'" width="120"></el-table-column>
            <!-- <el-table-column prop="is_google_flag" :label="isTrans ? '谷歌验证' : 'is_google_flag'"
                width="80"></el-table-column> -->
            <el-table-column prop="stop_commission_flag" :label="isTrans ? '停发佣金' : 'stop_commission_flag'"
                width="90"></el-table-column>
            <el-table-column prop="cbank" :label="isTrans ? '可改银行' : 'cbank'" width="90">
                <template #default="scope">
                    <div v-if="scope.row.cbank == 1" style="line-height: 20px;">是</div>
                    <div v-if="scope.row.cbank == 0" style="line-height: 20px;">否</div>
                </template>
            </el-table-column>
            <el-table-column prop="status_flag" :label="isTrans ? '状态' : 'status_flag'" width="70"></el-table-column>
            <el-table-column prop="icode_status_flag" :label="isTrans ? '邀请码状态' : 'icode_status_flag'"
                width="100"></el-table-column>
            <el-table-column :label="isTrans ? '操作' : ''" min-width="300" fixed="right">
                <template #default="scope">
                    <el-popconfirm confirmButtonText='确定' cancelButtonText='取消' icon="el-icon-warning" iconColor="red"
                        title="您确定要进行删除吗？" @confirm="del(scope.$index, scope.row, myScope)" v-if="power.delete">
                        <template #reference>
                            <el-button style="padding: 0px 6px;" type="danger" size="small">删除</el-button>
                        </template>
                    </el-popconfirm>
                    <el-button style="padding: 0px 6px;" size="small" v-if="power.update"
                        @click="edit(scope.$index, scope.row)">编辑</el-button>
                    <el-button style="padding: 0px 6px;" size="small" v-if="power.yxupdata"
                        @click="edit1(scope.$index, scope.row)">{{
        scope.row.first_pay_day == 0 ? '转有效' : '转无效' }}</el-button>
                    <el-button style="padding: 0px 6px;" size="small" v-if="power.kick"
                        @click="kick(scope.$index, scope.row)" type="warning">踢下线</el-button>
                    <router-link v-if="power.wallet" :to="{ name: '资产列表', query: { uid: scope.row.id } }"
                        style="margin-left: 10px;"><el-button style="padding: 0px 6px;" size="small"
                            type="success">资产</el-button></router-link>
                    <!--                <el-button size="small" v-if="power.pay" @click="pay(scope.$index,scope.row)" type="success">充值</el-button>-->
                </template>
            </el-table-column>
        </template>

        <template #summary="{ tdata }">
            <div style="position: absolute;left: 1rem;">
                <el-checkbox v-model="selectAll" size="large" @change="onSelectAll"></el-checkbox>
            </div>
            <span>账号数：{{ tdata.count }}</span>
        </template>

        <template #layer="{ tdata }">
            <!--弹层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="账号">
                        <el-input size="small" v-model="dataForm.account" autocomplete="off"
                            :disabled="configForm.isEdit"></el-input>
                    </el-form-item>
                    <el-form-item label="机构编号" v-if="false && (dataForm.gid == 61 || dataForm.gid == 63)">
                        <el-input size="small" v-model="dataForm.suf_usn" autocomplete="off">
                            <template #prepend>{{ actItem.pre_usn ? actItem.pre_usn : tdata.p_usn }}</template>
                        </el-input>
                    </el-form-item>
                    <el-form-item label="登录密码">
                        <el-input size="small" type="password" v-model="dataForm.password_flag" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="二级密码">
                        <el-input size="small" type="password" v-model="dataForm.password2_flag" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="手机号">
                        <el-input size="small" v-model="dataForm.phone" autocomplete="off" placeholder="选填"></el-input>
                    </el-form-item>
                    <el-form-item label="分组">
                        <el-select size="small" style="width: 120px;" v-model="dataForm.gid" placeholder="选择分组">
                            <el-option key="0" label="选择分组" value="0"></el-option>
                            <el-option v-for="(item, idx) in tdata.sys_group" :key="idx" :label="item" :value="idx">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="邀请人">
                        <el-input size="small" v-model="dataForm.p_account" autocomplete="off" placeholder="邀请人账号-选填"></el-input>
                    </el-form-item>
                    <el-form-item label="昵称">
                        <el-input size="small" v-model="dataForm.nickname" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="头像" style="margin-bottom: 0;height: 65px;">
                        <Upload v-model:file-list="headimgList" width="60px" height="60px"></Upload>
                    </el-form-item>

                    <el-form-item label="可改银行" style="margin-bottom: 0;height: 30px;">
                        <el-radio-group v-model="dataForm.cbank">
                            <el-radio :label="1">是</el-radio>
                            <el-radio :label="0">否</el-radio>
                        </el-radio-group>
                    </el-form-item>

                    <el-form-item label="停发佣金" v-if="store.state.user.gid <= 71" style="margin-bottom: 0;height: 30px;">
                        <el-radio-group v-model="dataForm.stop_commission">
                            <el-radio :label="1">是</el-radio>
                            <el-radio :label="0">否</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <!-- <el-form-item label="谷歌验证" v-if="store.state.user.gid == 1" style="margin-bottom: 0;height: 30px;">
                        <el-radio-group v-model="dataForm.is_google">
                            <el-radio :label="1">开启</el-radio>
                            <el-radio :label="0">关闭</el-radio>
                            <el-radio :label="2">重置密钥</el-radio>
                        </el-radio-group>
                    </el-form-item> -->
                    <el-form-item label="状态" v-if="store.state.user.gid <= 71" style="margin-bottom: 0;">
                        <el-radio-group v-model="dataForm.status">
                            <el-radio :label="2">正常</el-radio>
                            <el-radio :label="1">禁用</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="邀请码状态" v-if="store.state.user.gid <= 71" style="margin-bottom: 0;">
                        <el-radio-group v-model="dataForm.icode_status">
                            <el-radio :label="0">正常</el-radio>
                            <el-radio :label="1">禁用</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="允许登录IP" v-if="store.state.user.gid == 1">
                        <el-input size="small" type="textarea" v-model="dataForm.white_ip" autocomplete="off" rows="3"></el-input>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <input type="hidden" v-model="dataForm.id" />
                        <el-button @click="configForm.visible = false">取消</el-button>
                        <el-button type="primary" @click="save">保存</el-button>
                    </span>
                </template>
            </el-dialog>

            <!--弹出层-充值-->
            <el-dialog :title="configForm2.title" v-model="configForm2.visible" :close-on-click-modal="false"
                :width="configForm2.width">
                <el-form :label-width="configForm2.labelWidth">
                    <el-form-item label="账号">
                        <el-input size="small" v-model="actItem.account" autocomplete="off" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="可用余额">
                        <el-input size="small" v-model="actItem.balance" autocomplete="off" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="冻结余额">
                        <el-input size="small" v-model="actItem.fz_balance" autocomplete="off" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="充值类型" style="margin-bottom: 0;">
                        <el-radio-group v-model="payForm.ptype">
                            <el-radio :label="1">可用余额</el-radio>
                            <el-radio :label="2">冻结余额</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="充值额度" style="margin-bottom: 0;">
                        <el-input size="small" v-model="payForm.money" autocomplete="off"></el-input>
                        <span style="position: relative;top:-2px;color: #ff4400;">充值正数为增加，负数为扣除</span>
                    </el-form-item>
                    <el-form-item label="备注">
                        <el-input size="small" type="textarea" v-model="payForm.remark" autocomplete="off" rows="3"></el-input>
                    </el-form-item>
                    <el-form-item label="二级密码">
                        <el-input size="small" type="password" v-model="payForm.password2_flag" autocomplete="off"></el-input>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="configForm2.visible = false">取消</el-button>
                        <el-button type="primary" @click="savePay">提交</el-button>
                    </span>
                </template>
            </el-dialog>

            <el-dialog title="转移下级" v-model="transferShow" :close-on-click-modal="false" width="600px">
                <el-form :label-width="configForm2.labelWidth">
                    <el-form-item label="转出账号">
                        <el-input size="small" v-model="transferForm.from_account" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="转入账号">
                        <el-input size="small" v-model="transferForm.to_account" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="二级密码">
                        <el-input size="small" type="password" v-model="transferForm.password2" autocomplete="off"></el-input>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="transferShow = false">取消</el-button>
                        <el-button type="primary" @click="onSaveTransfer">提交</el-button>
                    </span>
                </template>
            </el-dialog>


            <el-dialog title="同步会员层级" v-model="transferShow1" :close-on-click-modal="false" width="600px">
                <el-form :label-width="configForm2.labelWidth">
                    <el-form-item label="代理账号">
                        <el-input size="small" v-model="transferForm.from_account" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-form-item label="二级密码">
                        <el-input size="small" type="password" v-model="transferForm.password2" autocomplete="off"></el-input>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="transferShow1 = false">取消</el-button>
                        <el-button type="primary" @click="onSaveTransfer1">提交</el-button>
                    </span>
                </template>
            </el-dialog>


            <el-dialog title="同步单个会员层级" v-model="transferShow2" :close-on-click-modal="false" width="600px">
                <el-form :label-width="configForm2.labelWidth">
                    <el-form-item label="会员账号">
                        <el-input size="small" v-model="transferForm.from_account" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-form-item label="二级密码">
                        <el-input size="small" type="password" v-model="transferForm.password2" autocomplete="off"></el-input>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="transferShow2 = false">取消</el-button>
                        <el-button type="primary" @click="onSaveTransfer2">提交</el-button>
                    </span>
                </template>
            </el-dialog>

            <el-dialog title="转移自己" v-model="transferShow3" :close-on-click-modal="false" width="600px">
                <el-form :label-width="configForm2.labelWidth">
                    <el-form-item label="转出账号">
                        <el-input size="small" v-model="transferForm.from_account" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="转入账号">
                        <el-input size="small" v-model="transferForm.to_account" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="二级密码">
                        <el-input size="small" type="password" v-model="transferForm.password2" autocomplete="off"></el-input>
                    </el-form-item>
                </el-form>
                <template #footer>
                    <span class="dialog-footer">
                        <el-button @click="transferShow3 = false">取消</el-button>
                        <el-button type="primary" @click="onSaveTransfer3">提交</el-button>
                    </span>
                </template>
            </el-dialog>
        </template>

    </Page>
</template>

<script lang="ts" setup>
import { ref, reactive, onMounted } from 'vue'
import Page from '../../components/Page.vue'
import Upload from '../../components/Upload.vue'
import { useStore } from "vuex";
import { checkPower } from "../../global/user";
import { _alert, getSrcUrl, getZero, showImg } from "../../global/common";
import md5 from "md5";
import http from "../../global/network/http";
import { ElMessageBox } from 'element-plus';
import { useRouter } from "vue-router";

let pageScope: any
let isRequest = false
const store = useStore()
const router = useRouter()
const pageRef = ref()
//权限控制
const power = reactive({
    update: checkPower('User_user_update'),
    delete: checkPower('User_user_delete'),
    kick: checkPower('User_user_kick'),
    pay: checkPower('User_user_pay'),
    wallet: checkPower('Finance_wallet'),
    transfer: checkPower('User_transfer'),
    yxupdata: checkPower('User_user_update_yx'),
})

const configForm = reactive({
    title: '',
    width: '540px',
    labelWidth: '100px',
    top: '3%',
    visible: false,
    isEdit: false
})

const actItem = ref<any>({})
const dataForm = reactive<any>({
    id: 0,
    account: '',
    usn: '',
    suf_usn: '',
    password: '',
    password_flag: '',
    password2: '',
    password2_flag: '',
    phone: '',
    gid: '',
    p_account: '',
    nickname: '',
    headimgurl: '',
    is_google: 0,
    stop_commission: 0,
    status: 2,
    icode_status: 0,
    white_ip: '',
    cbank: 0,
})

const headimgList = ref<any[]>([])
//翻译相关
const isTrans = ref(true)
const onTrans = (ev: any) => {
    if (ev == 1) {
        isTrans.value = false
    } else {
        isTrans.value = true
    }
    pageRef.value.doSearch()
}
const add = (myScope: any) => {
    pageScope = myScope
    headimgList.value = []
    getZero(dataForm)
    dataForm.status = 2
    dataForm.icode_status = 0
    dataForm.gid = ''
    configForm.visible = true
    configForm.title = '添加账号'
    configForm.isEdit = false
}

const edit = (idx: number, item: any) => {
    actItem.value = item
    headimgList.value = [{ src: item.headimgurl }]
    for (let i in dataForm) {
        dataForm[i] = item[i]
    }
    dataForm.gid = dataForm.gid.toString()
    configForm.visible = true
    configForm.title = '编辑账号'
    configForm.isEdit = true
}

const edit1 = (idx: number, item: any) => {
    http({
        url: 'c=User&a=UpdateUserfirst_pay_day',
        data: { "id": item.id }
    }).then((res: any) => {
        _alert(res.msg)
        item.first_pay_day = res.data.first_pay_day
    })
}


const save = () => {
    if (!configForm.isEdit) {
        if (!dataForm.account) {
            _alert('请填写账号')
            return
        }
        if (!dataForm.password_flag) {
            _alert('请填写登录密码')
            return
        }
        if (!dataForm.password2_flag) {
            _alert('请填写二级密码')
            return
        }
        dataForm.password = md5(dataForm.password_flag)
        dataForm.password2 = md5(dataForm.password2_flag)
    } else {
        if (dataForm.password_flag) {
            dataForm.password = md5(dataForm.password_flag)
        }
        if (dataForm.password2_flag) {
            dataForm.password2 = md5(dataForm.password2_flag)
        }
    }
    if (!dataForm.nickname) {
        _alert('请填写昵称')
        return
    }
    if (headimgList.value[0]) {
        dataForm.headimgurl = headimgList.value[0].src
    } else {
        if (configForm.isEdit) {
            _alert('请上传头像')
            return
        }
    }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=User&a=user_update',
        data: dataForm
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        configForm.visible = false  //关闭弹层
        if (!configForm.isEdit) {//添加的重新加载
            pageScope.doSearch()
        } else {//动态更新字段
            for (let i in dataForm) {
                actItem.value[i] = dataForm[i]
            }
            actItem.value.gname = res.data.gname
            if (res.data.is_google_flag) {
                actItem.value.is_google_flag = res.data.is_google_flag
            }
            if (res.data.status_flag) {
                actItem.value.status_flag = res.data.status_flag
            }
            if (res.data.icode_status_flag) {
                actItem.value.icode_status_flag = res.data.icode_status_flag
            }
            if (res.data.usn) {
                actItem.value.usn = res.data.usn
            }
            if (res.data.stop_commission_flag) {
                actItem.value.stop_commission_flag = res.data.stop_commission_flag
            }
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                setTimeout(() => {
                    isRequest = false
                }, 2000)
            }
        })
    })
}

const del = (idx: number, item: any, myScope: any) => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=User&a=user_delete',
        data: { id: item.id }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        //更新数据集
        myScope.delItem(idx)
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                setTimeout(() => {
                    isRequest = false
                }, 2000)
            }
        })
    })
}
//-----------------多选--------------------
const tableData = ref<any>({
    list: []
})
const selectAllArr = ref([])
const selectAll = ref(false)
const onPageSuccess = (td: any) => {
    tableData.value = td
    selectAllArr.value = []
    for (let i in td.list) {
        selectAllArr.value[i] = false;
    }
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
const getActionIds = () => {
    let ids = []
    for (let i in selectAllArr.value) {
        if (selectAllArr.value[i]) {
            ids.push(tableData.value.list[i].id)
        }
    }
    return ids
}
//状态禁用和解禁，验证码禁用和解禁,转有效无效
const DisableStatus = (status: number, field: string, bs: string) => {
    let idxs = getActionIdxs()
    if (idxs.length < 1) {
        _alert('至少需要选择一项')
        return
    }
    let ids = getActionIds()
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=User&a=DisableStatus',
        data: {
            ids: ids,
            status: status,
            field: field,
            bs: bs
        }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        isRequest = false;
        pageRef.value.doSearch();
        selectAll.value = false;
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {

            }
        })
    })
}
//------------------------------------------------

//踢用户下线
const kick = (idx: number, item: any) => {
    ElMessageBox.confirm('您确定要执行该操作么', '操作提示', {
        type: 'warning',
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        beforeClose: (action: string, instance, done) => {
            if (action == 'cancel') {
                done()
                return
            }
            instance.confirmButtonLoading = true
            http({
                url: 'c=User&a=user_kick',
                data: { id: item.id }
            }).then((res: any) => {
                _alert(res.msg)
                setTimeout(() => {
                    instance.confirmButtonLoading = false
                }, 2000)
                if (res.code != 1) {
                    return
                }
                done()
            })
        }
    }).catch(() => { })
}

//####充值相关####
const configForm2 = reactive({
    title: '',
    width: '540px',
    labelWidth: '100px',
    top: '3%',
    visible: false
})
const payForm = reactive({
    id: 0,
    ptype: 1,
    money: '',
    remark: '',
    password2: '',
    password2_flag: ''
})
const pay = (idx: number, item: any) => {
    actItem.value = item
    payForm.id = item.id
    configForm2.visible = true
    configForm2.title = '充值'
}

const savePay = () => {
    if (!payForm.money) {
        _alert('请填写充值额度')
        return
    }
    if (!payForm.password2_flag) {
        _alert('请填写二级密码')
        return
    }
    payForm.password2 = md5(payForm.password2_flag)
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=User&a=user_pay',
        data: payForm
    }).then((res: any) => {
        _alert(res.msg)
        setTimeout(() => {
            isRequest = false
        }, 3000)
        if (res.code != 1) {
            return
        }
        configForm2.visible = false
        payForm.password2 = ''
        payForm.password2_flag = ''
        payForm.money = ''
        payForm.remark = ''
        for (let i in res.data) {
            actItem.value[i] = res.data[i]
        }
    })
}

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

onMounted(() => {

})

const transferShow = ref(false)
const transferShow1 = ref(false)
const transferShow2 = ref(false)
const transferShow3 = ref(false)
const transferForm = reactive({
    from_account: '',
    to_account: '',
    password2: ''
})

const onTransfer = () => {
    transferShow.value = true
}
const onTransfer1 = () => {
    transferShow1.value = true
}
const onTransfer2 = () => {
    transferShow2.value = true
}
const onTransfer3 = () => {
    transferShow3.value = true
}
const onSaveTransfer = () => {
    if (isRequest) {
        return false
    } else {
        isRequest = true
    }
    http({
        url: 'c=User&a=transferAct',
        data: {
            from_account: transferForm.from_account,
            to_account: transferForm.to_account,
            password2: md5(transferForm.password2)
        }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        transferShow.value = false
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                router.go(0)
            }
        })
    })
}
const onSaveTransfer1 = () => {
    if (isRequest) {
        return false
    } else {
        isRequest = true
    }
    http({
        url: 'c=User&a=transferAct1',
        data: {
            from_account: transferForm.from_account,
            password2: md5(transferForm.password2)
        }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        transferShow1.value = false
        var deft = [];
        if (res.data.arr2.length > res.data.arr1.length)
            deft = res.data.arr2.concat(res.data.arr1).filter(v => !res.data.arr2.includes(v))
        else
            deft = res.data.arr1.concat(res.data.arr2).filter(v => !res.data.arr1.includes(v))

        var strarr = '';
        for (let index = 0; index < deft.length; index++) {
            const element = deft[index];
            if (index < deft.length - 1)
                strarr += element + ',';
            else
                strarr += element;
        }
        alert(strarr)
        http({
            url: 'c=User&a=transferAct2',
            data: {
                deft: strarr
            }
        }).then((res: any) => {
            if (res.code != 1) {
                isRequest = false
                _alert(res.msg)
                return
            }
            transferShow1.value = false
            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => {
                }
            })
        })
    })
}

const onSaveTransfer2 = () => {
    if (isRequest) {
        return false
    } else {
        isRequest = true
    }
    http({
        url: 'c=User&a=transferAct3',
        data: {
            from_account: transferForm.from_account,
            password2: md5(transferForm.password2)
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
                isRequest = false
                //transferShow2.value = false
            }
        })
    })
}

const onSaveTransfer3 = () => {
    if (isRequest) {
        return false
    } else {
        isRequest = true
    }
    http({
        url: 'c=User&a=transferActOwn',
        data: {
            from_account: transferForm.from_account,
            to_account: transferForm.to_account,
            password2: md5(transferForm.password2)
        }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        transferShow.value = false
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                router.go(0)
            }
        })
    })
}

</script>