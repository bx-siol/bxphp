<template>
    <div class="conbox">
        <el-tabs v-model="activeTabName" type="card" @tab-click="handleTabClick">
            <el-tab-pane label="通用设置" name="first">
                <el-form style="width: 50%;" :label-width="configForm.labelWidth">
                    <el-form-item label="联系电话">
                        <el-input size="small" v-model="contact.phone" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="联系地址">
                        <el-input size="small" type="textarea" v-model="contact.address" autocomplete="off" rows="2"></el-input>
                    </el-form-item>
                    <el-form-item label="工作时间">
                        <el-input size="small" v-model="copyright.worktime" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="版权信息">
                        <el-input size="small" v-model="copyright.slogan" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="备案信息">
                        <el-input size="small" v-model="copyright.icp" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="微信二维码">
                        <Upload v-model:file-list="wxCoverList"></Upload>
                    </el-form-item>
                    <el-form-item label="微信号">
                        <el-input size="small" v-model="service.wx.account" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="QQ二维码">
                        <Upload v-model:file-list="qqCoverList"></Upload>
                    </el-form-item>
                    <el-form-item label="QQ号">
                        <el-input size="small" v-model="service.qq.account" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="Telegram">
                        <Upload v-model:file-list="telgCoverList"></Upload>
                    </el-form-item>
                    <el-form-item label="Tele账号" style="margin-bottom: 0;">
                        <el-input size="small" v-model="service.telegram.account" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="客服显示">
                        <el-checkbox-group v-model="dataForm.service.show">
                            <el-checkbox :label="item.id" v-for="item in serviceTypeArr">{{ item.name }}</el-checkbox>
                        </el-checkbox-group>
                    </el-form-item>
                    <el-form-item label="" style="padding-top: 10px;">
                        <el-button type="primary" @click="onCommonSubmit">提交保存</el-button>
                    </el-form-item>
                </el-form>
                <div style="height: 100px;"></div>
            </el-tab-pane>

            <el-tab-pane label="项目设置" name="second">
                <el-form :label-width="configForm.labelWidth2" label-suffix="：">
                    <el-form-item label="首页轮播图">
                        <Upload v-model:file-list="kvCoverList" :limit="3" text-placeholder="请输入链接(选填)" width="240px"
                            height="140px" :need-text="true">
                        </Upload>
                    </el-form-item>
                    <el-form-item label="APP下载地址">
                        <el-input size="small" v-model="app.download" autocomplete="off"></el-input>
                    </el-form-item>
                    <!--                    <el-form-item label="美元兑Rs汇率">
                        <el-input size="small" v-model="project.usd2rs" autocomplete="off" style="width: 400px;"></el-input>
                    </el-form-item>-->
                    <el-form-item label="返佣设置">
                        <el-input size="small" v-model="project.commission" autocomplete="off" style="width: 400px;"></el-input>
                        <span> 格式：1=12,2=5,3=2 表示第1代12%，第2代5%，第3代2%</span>
                    </el-form-item>
                    <el-form-item label="" style="padding-top: 10px;">
                        <el-button type="primary" @click="onPlatformSubmit">提交保存</el-button>
                    </el-form-item>
                </el-form>
                <div style="height: 100px;"></div>
            </el-tab-pane>

            <el-tab-pane label="余额相关" name="third">
                <el-form style="width: 50%;" :label-width="configForm.labelWidth2">
                    <el-form-item label="单笔最小充值">
                        <el-input size="small" v-model="balance.pay.min" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="单笔最大充值">
                        <el-input size="small" v-model="balance.pay.max" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-form-item label="卡单笔最小充值">
                        <el-input size="small" v-model="balance.pay.kmin" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="卡单笔最大充值">
                        <el-input size="small" v-model="balance.pay.kmax" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-form-item label="单笔最小提现">
                        <el-input size="small" v-model="balance.cash.min" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="单笔最大提现">
                        <el-input size="small" v-model="balance.cash.max" autocomplete="off"></el-input>
                    </el-form-item>

                    <el-form-item label="提现手续费">
                        <el-input size="small" style="width: 100px;" placeholder="" v-model="balance.cash.fee.percent">
                        </el-input>
                        <b style="padding-right: 20px;">%</b>
                        <b style="padding-right: 20px;font-size: 1.5rem;">+</b>
                        <el-input size="small" style="width: 100px;" placeholder="" v-model="balance.cash.fee.money">
                        </el-input>
                    </el-form-item>
                    <el-form-item label="手续费模式" style="margin-bottom: 0;">
                        <el-radio-group v-model="balance.cash.fee.mode">
                            <el-radio label="1">从提现中出</el-radio>
                            <el-radio label="2">从余额中出</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="周末可提现" style="margin-bottom: 0;">
                        <el-radio-group v-model="balance.cash.time.weekend">
                            <el-radio label="1">是</el-radio>
                            <el-radio label="0">否</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="可提现时间">
                        <el-time-picker size="small" is-range style="width: 271px;" :editable="false" v-model="cashTimeFlag"
                            @change="onBalanceCashTimeChange" range-separator="至" placeholder="请选择">
                        </el-time-picker>
                    </el-form-item>
                    <el-form-item label="" style="padding-top: 10px;">
                        <el-button type="primary" @click="onSubmitBalance">提交保存</el-button>
                    </el-form-item>
                </el-form>
                <div style="height: 100px;"></div>
            </el-tab-pane>

            <el-tab-pane label="抽奖设置" name="fourth">
                <el-form :label-width="configForm.labelWidth2" label-suffix="：">
                    <el-form-item label="库存余额">
                        <el-input size="small" v-model="lottery.stock_money" autocomplete="off" style="width: 400px;"></el-input>
                    </el-form-item>
                    <el-form-item label="中奖金额">
                        <el-row>
                            <el-col :span="2">
                                <el-input size="small" v-model="lottery.from_money" autocomplete="off"></el-input>
                            </el-col>
                            <el-col :span="1">
                                <span style="padding-left: 40%;">到</span>
                            </el-col>
                            <el-col :span="2">
                                <el-input size="small" v-model="lottery.to_money" autocomplete="off"></el-input>
                            </el-col>
                        </el-row>
                    </el-form-item>
                    <el-form-item label="单个用户">
                        <el-input size="small" v-model="lottery.day_limit" autocomplete="off" style="width: 400px;"
                            placeholder="0或空则不限"></el-input>
                        <span> 次/天 0或空则不限</span>
                    </el-form-item>
                    <el-form-item label="单个用户">
                        <el-input size="small" v-model="lottery.week_limit" autocomplete="off" style="width: 400px;"
                            placeholder="0或空则不限"></el-input>
                        <span> 次/周 0或空则不限</span>
                    </el-form-item>
                    <el-form-item label="状态" style="margin-bottom: 0;">
                        <el-radio-group v-model="lottery.status">
                            <el-radio :label="1">下线</el-radio>
                            <el-radio :label="3">上线</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="抽奖次数额度">
                        <el-input size="small" v-model="lottery.lottery_min" autocomplete="off" style="width: 400px;" placeholder="">
                        </el-input>
                        <span> 达到设置额度增加1次抽奖机会</span>
                    </el-form-item>
                    <el-form-item label="" style="padding-top: 10px;">
                        <el-button type="primary" @click="onLotterySubmit">提交保存</el-button>
                    </el-form-item>
                    <el-form-item label="抽奖地址">
                        {{ lottery.url }}
                    </el-form-item>
                </el-form>
                <div style="height: 100px;"></div>
            </el-tab-pane>

        </el-tabs>
    </div>

</template>

<script lang="ts" setup>
import { ref, onMounted, toRefs, reactive, computed } from 'vue'
import { useStore } from "vuex";
import http from "../../global/network/http";
import { getSrcUrl, _alert, showImg, getConfig, setConfig } from "../../global/common";
import dayjs from 'dayjs';
import Upload from '../../components/Upload.vue'

const wxCoverList = ref<any>([])
const qqCoverList = ref<any>([])
const telgCoverList = ref<any>([])
const kvCoverList = ref<any>([])

let isRequest = false
const store = useStore()

const configForm = ref({
    title: '',
    width: '540px',
    labelWidth: '120px',
    labelWidth2: '140px',
    visible: false,
    isEdit: false
})

const tableData = ref({
    bank_arr: [],
    currency_arr: [],
    group_arr: []
})

const getCurrencyName = (cid: number) => {
    let name = ''
    for (let i in tableData.value.currency_arr) {
        let item = tableData.value.currency_arr[i] as any
        if (item.id == cid) {
            name = item.name
            break;
        }
    }
    return name
}

//客服相关
interface Kefu {
    name: string,
    account: string,
    qrcode: string
}

interface Service {
    wx: Kefu,
    qq: Kefu,
    telegram: Kefu,
    show?: string[]
}

//联系方式
interface Contact {
    phone: string,
    address: string
}

interface Copyright {
    slogan: string,
    icp: string,
    worktime: string
}

//首页轮播图
interface IndexKv {
    name?: string,
    cover: string,
    path?: string,
    url?: string
}

//余额相关
interface PayRule {
    min: number,
    max: number,
    kmin: number,
    kmax: number
}

interface CashFeeRule {
    percent: number,
    money: number,
    mode: any
}

interface CashTimeRule {
    from: string,
    to: string,
    weekend: any
}

interface CashRule {
    min: number,
    max: number,
    fee: CashFeeRule,
    time: CashTimeRule
}

interface Balance {
    pay: PayRule,
    cash: CashRule
}

//数据模板
interface DataFormTpl {
    service: Service,
    contact: Contact,
    copyright: Copyright,
    indexKv: IndexKv[],
    balance: Balance,
    [propName: string]: any
}

const serviceTypeArr = [
    { id: 'wx', name: '微信' },
    { id: 'qq', name: 'QQ' },
    { id: 'telegram', name: 'Telegram' }
]
const dataForm = reactive<DataFormTpl>({
    service: {
        wx: { name: '微信', account: '', qrcode: '' },
        qq: { name: 'QQ', account: '', qrcode: '' },
        telegram: { name: 'Telegram', account: '', qrcode: '' },
        show: []
    },
    contact: {
        phone: '',
        address: ''
    },
    copyright: {
        slogan: '',
        icp: '',
        worktime: ''
    },
    indexKv: [],
    app: {
        download: ''
    },
    project: {
        usd2rs: '',
        commission: ''
    },
    lottery: {},
    balance: {
        pay: {
            min: 0,
            max: 50000,
            kmax: 50000,
            kmin: 0
        },
        cash: {
            min: 100,
            max: 50000,
            fee: {
                percent: 0,
                money: 0,
                mode: 1
            },
            time: {
                from: '',
                to: '',
                weekend: 1
            }
        }
    }
})

const { service, contact, copyright, indexKv, app, project, lottery, balance } = toRefs(dataForm)
const cashTimeFlag = ref<any>([])

//提交保存-通用设置
const onCommonSubmit = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    if (wxCoverList.value[0]) {
        dataForm.service.wx.qrcode = wxCoverList.value[0].src
    } else {
        dataForm.service.wx.qrcode = ''
    }
    if (qqCoverList.value[0]) {
        dataForm.service.qq.qrcode = qqCoverList.value[0].src
    } else {
        dataForm.service.qq.qrcode = ''
    }
    if (telgCoverList.value[0]) {
        dataForm.service.telegram.qrcode = telgCoverList.value[0].src
    } else {
        dataForm.service.telegram.qrcode = ''
    }
    http({
        url: 'c=Sys&a=pset_update',
        data: {
            service: dataForm.service,
            contact: dataForm.contact,
            copyright: dataForm.copyright
        }
    }).then((res: any) => {
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => { }
        })
    }).catch(() => {
        //
    })
}

//提交保存-项目设置
const onPlatformSubmit = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    dataForm.indexKv = []
    for (let i in kvCoverList.value) {
        dataForm.indexKv.push({ cover: kvCoverList.value[i].src, url: kvCoverList.value[i].text })
    }

    http({
        url: 'c=Sys&a=pset_update',
        data: {
            indexKv: dataForm.indexKv,
            app: dataForm.app,
            project: dataForm.project
        }
    }).then((res: any) => {
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => { }
        })
    }).catch(() => {

    })
}

//////////////////////////////////////项目相关//////////////////////////////////////////////
const indexBottomStartTimeFlag = ref();
const onIndexBottomStartTimeChange = (item: any) => {
    let start_time = dayjs(new Date()).format('YYYY-MM-DD HH:mm:ss')
    if (item) {
        start_time = dayjs(new Date(item)).format('YYYY-MM-DD HH:mm:ss')
    }
    dataForm.indexBottom.start_time = start_time
}

//////////////////////////////////////红包相关//////////////////////////////////////////////
const onLotterySubmit = () => {
    http({
        url: 'c=Sys&a=lottery_update',
        data: {
            status: dataForm.lottery.status,
            stock_money: dataForm.lottery.stock_money,
            from_money: dataForm.lottery.from_money,
            to_money: dataForm.lottery.to_money,
            day_limit: dataForm.lottery.day_limit,
            week_limit: dataForm.lottery.week_limit,
            lottery_min: dataForm.lottery.lottery_min
        }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        _alert(res.msg)
    })
}

//////////////////////////////////////余额相关//////////////////////////////////////////////
//时间切换-格式化
const onBalanceCashTimeChange = (item: any) => {
    if (!item) {
        dataForm.balance.cash.time.from = '00:00:00'
        dataForm.balance.cash.time.to = '23:59:59'
        return
    }
    let from = dayjs(new Date(item[0])).format('HH:mm:ss')
    let to = dayjs(new Date(item[1])).format('HH:mm:ss')
    dataForm.balance.cash.time.from = from.toString()
    dataForm.balance.cash.time.to = to.toString()
}

const onSubmitBalance = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Sys&a=pset_update',
        data: { balance: dataForm.balance }
    }).then((res: any) => {
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => { }
        })
    }).catch(() => {

    })
}

//tab切换回调
const handleTabClick = (tab: any) => {
    let config = getConfig()
    if (config) {
        config.tab = tab.props.name
        setConfig(config)
    }
}
const activeTabName = ref(store.state.config.tab)

//初始化
const init = () => {
    http({
        url: 'c=Sys&a=pset',
        data: {}
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }

        tableData.value.group_arr = res.data.group_arr
        tableData.value.bank_arr = res.data.bank_arr
        tableData.value.currency_arr = res.data.currency_arr

        if (res.data.service) {
            Object.assign(dataForm.service, res.data.service)
        }
        if (res.data.contact) {
            Object.assign(dataForm.contact, res.data.contact)
        }
        if (res.data.copyright) {
            Object.assign(dataForm.copyright, res.data.copyright)
        }
        if (res.data.indexKv) {
            Object.assign(dataForm.indexKv, res.data.indexKv)
        }

        if (res.data.app) {
            Object.assign(dataForm.app, res.data.app)
        }
        if (res.data.project) {
            Object.assign(dataForm.project, res.data.project)
        }

        if (res.data.lottery) {
            Object.assign(dataForm.lottery, res.data.lottery)
        }

        if (res.data.balance) {
            Object.assign(dataForm.balance, res.data.balance)
            cashTimeFlag.value = [new Date('2021-01-01 ' + dataForm.balance.cash.time.from), new Date('2021-01-01 ' + dataForm.balance.cash.time.to)]
        }

        //具体项目相关

        if (dataForm.service.wx.qrcode) {
            wxCoverList.value.push({ src: dataForm.service.wx.qrcode })
        }
        if (dataForm.service.qq.qrcode) {
            qqCoverList.value.push({ src: dataForm.service.qq.qrcode })
        }
        if (dataForm.service.telegram.qrcode) {
            telgCoverList.value.push({ src: dataForm.service.telegram.qrcode })
        }
        if (dataForm.indexKv) {
            for (let i in dataForm.indexKv) {
                kvCoverList.value.push({ src: dataForm.indexKv[i].cover, text: dataForm.indexKv[i].url })
            }
        }

        setTimeout(() => { store.dispatch('loadingFinish'); }, store.state.loadingTime)
    })
}

onMounted(() => {
    init()
})

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

</script>

<style scoped>
.psetSuffixNotice {
    position: absolute;
    right: -20px;
}
</style>