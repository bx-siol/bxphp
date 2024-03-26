<template>
    <Page url="c=Finance&a=banklog">
        <template #btn="myScope">
            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus"
                @click="add(myScope)">添加收款方式</el-button>
        </template>

        <template #search="{ params, tdata }">
            <el-select size="small" style="width: 120px;margin-left: 10px;" v-model="params.s_type" placeholder="全部类型" v-if="false">
                <el-option key="0" label="全部类型" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.type_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
            <el-select size="small" style="width: 120px;margin-left: 10px;" v-model="params.s_status" placeholder="全部状态">
                <el-option key="0" label="全部状态" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
        </template>

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="acc" label="所属用户" width="140" v-if="false">
                <template #default="{ row }">
                    <span v-if="row.acc">{{ row.acc }}</span>
                    <span v-else>平台</span>
                </template>
            </el-table-column>
            <el-table-column prop="nickname" label="昵称" width="140" v-if="false">
                <template #default="{ row }">
                    <span v-if="row.nickname">{{ row.nickname }}</span>
                    <span v-else>/</span>
                </template>
            </el-table-column>
            <el-table-column prop="account" label="收款信息" width="420">
                <template #default="scope">
                    <template v-if="scope.row.type == 1">
                        <div style="text-align: left;">
                            <div>银行：<b>{{ scope.row.bank_name }}</b></div>
                            <!--                        {{scope.row.province_name}}/{{scope.row.city_name}}-->
                            <div v-if="scope.row.routing">路由：{{ scope.row.routing }}</div>
                            <div v-if="scope.row.ifsc">clabe：{{ scope.row.ifsc }}</div>
                            <div>姓名：{{ scope.row.realname }}</div>
                            <div>账号：{{ scope.row.account }}</div>
                            <div v-if="scope.row.upi">UPI：{{ scope.row.upi }}</div>
                        </div>
                    </template>
                    <template v-else-if="scope.row.type < 4">
                        <div style="text-align: left;position: relative;">
                            <div style="font-weight: bold;">{{ scope.row.type_flag }}</div>
                            <div>姓名：{{ scope.row.realname }}</div>
                            <div>账号：{{ scope.row.account }}</div>
                            <el-image v-if="scope.row.qrcode"
                                style="width: 65px;height: 65px;position: absolute;right: 0;top: 0;" fit="cover"
                                :src="getSrcUrl(scope.row.qrcode)" hide-on-click-modal
                                :preview-src-list="[getSrcUrl(scope.row.qrcode)]">
                            </el-image>
                        </div>
                    </template>
                    <template v-else-if="scope.row.type == 4">
                        <div style="text-align: left;position: relative;">
                            <div style="font-weight: bold;">{{ scope.row.type_flag }}</div>
                            <div>协议：{{ scope.row.protocal_flag }}</div>
                            <div>钱包：{{ scope.row.address }}</div>
                            <el-image v-if="scope.row.qrcode"
                                style="width: 65px;height: 65px;position: absolute;right: 0;top: 0;" fit="cover"
                                :src="getSrcUrl(scope.row.qrcode)" hide-on-click-modal
                                :preview-src-list="[getSrcUrl(scope.row.qrcode)]">
                            </el-image>
                        </div>
                    </template>
                </template>
            </el-table-column>
            <!--        <el-table-column prop="routing" label="路由号" width="130" class-name="imgCellBox">
            <template #default="scope">
                <span v-if="scope.row.routing">{{scope.row.routing}}</span>
                <span v-else>/</span>
            </template>
        </el-table-column>-->
            <el-table-column prop="sort" label="排序(大->小)"></el-table-column>
            <el-table-column prop="create_time" label="创建时间"></el-table-column>
            <el-table-column prop="remark" label="备注"></el-table-column>
            <el-table-column prop="status_flag" label="状态">
                <template #default="{ row }">
                    <el-switch v-model="row.status_switch" inactive-text="下线" active-text="上线"
                        @change="onSwitch($event, row, 'status')" />
                </template>
            </el-table-column>
            <el-table-column label="操作" width="160">
                <template #default="scope">
                    <el-popconfirm confirmButtonText='确定' cancelButtonText='取消' icon="el-icon-warning" iconColor="red"
                        title="您确定要进行删除吗？" @confirm="del(scope.$index, scope.row, myScope)" v-if="power.delete">
                        <template #reference>
                            <el-button type="danger" size="small">删除</el-button>
                        </template>
                    </el-popconfirm>
                    <el-button size="small" v-if="power.update"
                        @click="edit(scope.$index, scope.row, myScope)">编辑</el-button>
                </template>
            </el-table-column>
        </template>

        <template #layer="{ tdata }">
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="类型" style="margin-bottom: 0;" v-if="false">
                        <el-radio-group v-model="dataForm.type">
                            <el-radio :label="idx" v-for="(item, idx) in tdata.type_arr">{{ item }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <!-- <el-form-item label="银行" v-show="dataForm.type==1">
                    <el-select size="small" style="width: 220px;" v-model="dataForm.bank_id" placeholder="请选择" ref="bankNode">
                        <el-option key="0" label="请选择" value="0"></el-option>
                        <el-option
                                v-for="(item,idx) in tdata.bank_arr"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id">
                        </el-option>
                    </el-select>
                </el-form-item> -->

                    <el-form-item label="银行" v-show="dataForm.type == 1">
                        <el-input size="small" v-model="dataForm.bank_name" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>

                    <el-form-item label="省" v-show="dataForm.type == 1" v-if="false">
                        <el-select size="small" style="width: 160px;" v-model="dataForm.province_id" placeholder="请选择"
                            @change="onProvinceChange" ref="provinceNode">
                            <el-option key="0" label="请选择" value="0"></el-option>
                            <el-option v-for="(item, idx) in tdata.province_arr" :key="item.id" :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>
                        <span style="padding:0 10px;">市</span>
                        <el-select size="small" style="width: 160px;" v-model="dataForm.city_id" placeholder="请选择" ref="cityNode">
                            <el-option key="0" label="请选择" value="0"></el-option>
                            <el-option v-for="(item, idx) in cityArr" :key="item.id" :label="item.name" :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="clabe" v-show="dataForm.type == 1">
                        <el-input size="small" v-model="dataForm.ifsc" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="姓名" v-if="dataForm.type < 4">
                        <el-input size="small" v-model="dataForm.realname" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="账户" v-if="dataForm.type < 4">
                        <el-input size="small" v-model="dataForm.account" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="UPI" v-show="dataForm.type == 1">
                        <el-input size="small" v-model="dataForm.upi" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="协议" v-show="dataForm.type == 4">
                        <el-radio-group v-model="dataForm.protocal">
                            <el-radio :label="idx" v-for="(item, idx) in tdata.protocal_arr">{{ item }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="钱包地址" v-if="dataForm.type >= 4">
                        <el-input size="small" v-model="dataForm.address" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="收款码" style="margin-bottom: 0" v-show="dataForm.type > 1">
                        <Upload v-model:file-list="qrcodeList"></Upload>
                    </el-form-item>
                    <el-form-item label="排序值">
                        <el-input size="small" v-model="dataForm.sort" autocomplete="off" placeholder=""></el-input>
                        <span>从大到小</span>
                    </el-form-item>
                    <el-form-item label="状态" style="margin-bottom: 0;">
                        <el-radio-group v-model="dataForm.status">
                            <el-radio :label="idx" v-for="(item, idx) in tdata.status_arr">{{ item }}</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="备注说明">
                        <el-input size="small" type="textarea" v-model="dataForm.remark" autocomplete="off" rows="3"></el-input>
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
        </template>

    </Page>
</template>

<script lang="ts" setup>
import { ref, onMounted, reactive, computed } from 'vue';
import { useStore } from "vuex";
import http from "../../global/network/http";
import { getZero, _alert, getSrcUrl } from "../../global/common";
import { checkPower } from '../../global/user';
import md5 from 'md5';
import { ElMessageBox } from "element-plus";
import Page from '../../components/Page.vue';
import Upload from '../../components/Upload.vue';

let isRequest = false
const store = useStore()
let pageScope: any

//权限控制
const power = reactive({
    update: checkPower('Finance_banklog_update'),
    delete: checkPower('Finance_banklog_delete')
})

const configForm = reactive({
    title: '',
    width: '580px',
    labelWidth: '100px',
    top: '3%',
    visible: false,
    isEdit: false
})

const actItem = ref<any>({})

interface DataFormTpl {
    id?: number,
    routing?: string,
    ifsc?: string,
    upi?: string,
    province_id?: number,
    city_id?: number,
    bank_id?: number,
    status?: string,
    type?: string,
    protocal?: string,
    account?: string,
    realname?: string,
    address?: string,
    sort?: number,
    qrcode?: string,
    remark?: string,
    bank_name?: string,
}
const dataForm = ref<DataFormTpl>({})

const qrcodeList = ref<any>([])
const cityArr = ref<any>([])

const add = (myScode: any) => {
    pageScope = myScode
    dataForm.value = {
        type: '1',
        protocal: '3',
        status: '1',
        sort: 1000,
        bank_name: '',
    }
    cityArr.value = []
    qrcodeList.value = []
    configForm.visible = true
    configForm.title = '添加收款方式'
    configForm.isEdit = false
}

const edit = (idx: number, item: any, myScope: any) => {
    pageScope = myScope
    actItem.value = item
    dataForm.value = {
        id: item.id,
        type: item.type.toString(),
        protocal: item.protocal.toString(),
        status: item.status.toString(),
        bank_id: item.bank_id,
        province_id: item.province_id,
        city_id: item.city_id,
        routing: item.routing,
        ifsc: item.ifsc,
        upi: item.upi,
        account: item.account,
        realname: item.realname,
        address: item.address,
        sort: item.sort,
        qrcode: item.qrcode,
        remark: item.remark,
        bank_name: item.bank_name,
    }

    if (item.type == 1 && item.province_id > 0) {
        http({
            url: 'a=getPc',
            data: { id: item.province_id }
        }).then((res: any) => {
            cityArr.value = res.data.list
            // dataForm.city_id=item.city_id
        })
    }
    if (item.qrcode) {
        qrcodeList.value = [{ src: item.qrcode }]
    }
    configForm.visible = true
    configForm.title = '编辑收款方式'
    configForm.isEdit = true
}

const bankNode = ref()
const provinceNode = ref()
const cityNode = ref()

const save = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    if (qrcodeList.value[0]) {
        dataForm.value.qrcode = qrcodeList.value[0].src
    } else {
        dataForm.value.qrcode = ''
    }
    http({
        url: 'c=Finance&a=banklog_update',
        data: dataForm.value
    }).then((res: any) => {
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        configForm.visible = false  //关闭弹层
        if (!configForm.isEdit) {//添加的重新加载
            pageScope.doSearch()
        } else {//动态更新字段
            Object.assign(actItem.value, dataForm.value) //合并更新
            actItem.value.type_flag = res.data.type_flag
            actItem.value.status_flag = res.data.status_flag
            actItem.value.status_switch = res.data.status_switch
            if (dataForm.value.bank_id && dataForm.value.bank_id > 0) {
                actItem.value.bank_name = bankNode.value.options.get(dataForm.value.bank_id).label
            }
            if (dataForm.value.province_id && dataForm.value.province_id > 0) {
                actItem.value.province_name = provinceNode.value.options.get(dataForm.value.province_id).label
                actItem.value.city_name = cityNode.value.options.get(dataForm.value.city_id).label
            }

        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => { }
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
        url: 'c=Finance&a=banklog_delete',
        data: { id: item.id }
    }).then((res: any) => {
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }

        myScope.delItem(idx)    //更新数据集

        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => { }
        })
    })
}

const onSwitch = (ev: any, item: any, fieldName: string) => {
    let value = ev ? 2 : 1
    http({
        url: 'a=changeTableVal',
        data: {
            table: 'cnf_banklog',
            id_name: 'id',
            id_value: item.id,
            field: fieldName,
            value: value
        }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        item[fieldName] = value
        item[fieldName + '_switch'] = value == 2 ? true : false
    })
}

const onProvinceChange = (val: number) => {
    http({
        url: 'a=getPc',
        data: { id: val }
    }).then((res: any) => {
        cityArr.value = res.data.list
        delete dataForm.value.city_id
    })
}

</script>