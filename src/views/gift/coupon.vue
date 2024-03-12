<template>
    <Page url="c=Gift&a=coupon" ref="pageRef">
        <template #btn="myScope">
            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add">添加券</el-button>
        </template>

        <template #search="{ params, tdata, doSearch }">
            <el-select style="width: 110px;margin-left: 10px;" v-model="params.s_status" placeholder="所有状态">
                <el-option key="0" label="所有状态" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
        </template>

        <template #table>
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="name" label="名称" min-width="160"></el-table-column>

            <el-table-column prop="cover" label="图标" width="160">
                <template #default="{ row }">
                    <img v-if="row.cover" :src="imgFlag(row.cover)" @click="onPreviewImg(row.cover)"
                        style="height: 40px;vertical-align: middle;">
                    <span v-else>/</span>
                </template>
            </el-table-column>

            <el-table-column prop="discount" label="折扣%" min-width="80"></el-table-column>

            <el-table-column prop="money" label="面值" width="120"></el-table-column>
            <el-table-column prop="stock_num" label="库存数量" width="120"></el-table-column>

            <el-table-column prop="receive_num" label="已领取数量" width="120"></el-table-column>
            <el-table-column prop="goods" label="可用产品" width="120"></el-table-column>
            <el-table-column prop="remark" label="备注" width="140"></el-table-column>
            <el-table-column prop="type_flag" label="类型" width="70"></el-table-column>
            <el-table-column prop="status_flag" label="状态" width="70"></el-table-column>

            <el-table-column prop="effective_time_flag" label="有效截止时间" width="140"></el-table-column>
            <el-table-column prop="create_time" label="创建时间" width="70"></el-table-column>

            <el-table-column label="操作" width="160">
                <template #default="scope">
                    <el-popconfirm confirmButtonText='确定' cancelButtonText='取消' icon="el-icon-warning" iconColor="red"
                        title="您确定要进行删除吗？" @confirm="del(scope.$index, scope.row)" v-if="power.delete">
                        <template #reference>
                            <el-button type="danger" size="small">删除</el-button>
                        </template>
                    </el-popconfirm>
                    <el-button size="small" v-if="power.update" @click="edit(scope.$index, scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </template>

        <template #summary="{ tdata }">
            <span>记录数：{{ tdata.count }}</span>
            <!-- <span>领取总数：{{ tdata.receive_quantity }}</span>
            <span>领取总额：{{ tdata.receive_money }}</span> -->
        </template>

        <template #layer="{ tdata }">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened">
                <el-form :label-width="configForm.labelWidth">


                    <el-form-item label="券类型">
                        <el-radio-group v-model="dataForm.type">
                            <el-radio label="1"> 折扣券</el-radio>
                            <el-radio label="2"> 兑换券</el-radio>
                        </el-radio-group>
                    </el-form-item>
                    <el-form-item label="名称">
                        <el-input v-model="dataForm.name" autocomplete="off" placeholder="任意填写，便于区分红包用途"></el-input>
                    </el-form-item>
                    <el-form-item v-if="dataForm.type == '1'" label="折扣">
                        <el-input v-model="dataForm.discount" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder="" style="width: 180px;"></el-input>
                        <span> %</span>
                    </el-form-item>

                    <el-form-item v-if="dataForm.type == '2'" label="面值金额">
                        <el-input v-model="dataForm.money" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder="" style="width: 180px;"></el-input>

                    </el-form-item>

                    <el-form-item label="库存数量">
                        <el-input v-model="dataForm.stock_num" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder=""></el-input>

                    </el-form-item>

                    <el-form-item label="可用产品">
                        <el-select multiple collapse-tags v-model="dataForm.gids" placeholder="请选择">
                            <el-option key="0" label="请选择" value="0"></el-option>
                            <el-option v-for="(item ) in tdata.goods_arr" :key="item.id" :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>

                    </el-form-item>


                    <el-form-item label="期限">
                        <el-input v-model="dataForm.qx" style="width: 180px;" autocomplete="off" placeholder=""></el-input>
                        <span>多少天后到期</span>
                    </el-form-item>



                    <el-form-item label="截止有效期">

                        <el-date-picker v-model="dataForm.effective_time" type="datetime" placeholder="截止有效期">
                        </el-date-picker>


                    </el-form-item>



                    <el-form-item label="图标">
                        <MyUpload2 v-model:file-list="iconList" width="80px" height="80px"
                            style="line-height: initial;"></MyUpload2>
                    </el-form-item>

                    <el-form-item label="备注">
                        <el-input v-model="dataForm.remark" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder=""></el-input>

                    </el-form-item>




                    <el-form-item label="状态" style="margin-bottom: 0;">
                        <el-radio-group v-model="dataForm.status">
                            <el-radio :label="idx" v-for="(item, idx) in tdata.status_arr">{{ item }}</el-radio>
                        </el-radio-group>
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

<script lang="ts">
import { defineComponent } from 'vue';
import Page from '../../components/Page.vue';
import MyUpload2 from '../../components/Upload.vue';

export default defineComponent({
    components: {
        Page, MyUpload2
    }
})
</script>

<script lang="ts" setup>
import { ref, reactive, onMounted, getCurrentInstance, nextTick } from 'vue';
import { useStore } from "vuex";
import http from "../../global/network/http";
import { getZero, _alert, getSrcUrl, showImg } from "../../global/common";
import { checkPower } from '../../global/user';
import md5 from 'md5';
import { ElMessageBox } from "element-plus";


let isRequest = false
const store = useStore()
const insObj = getCurrentInstance()
const pageRef = ref()
const editor = ref()

const configForm = reactive({
    title: '',
    width: '600px',
    labelWidth: '100px',
    // top:'1%',
    visible: false,
    isEdit: false
})

const coverList = ref<any>([])
const iconList = ref<any>([])
const actItem = ref<any>()

const dataForm = reactive<any>({
    id: 0,
    name: '',
    icon: '',
    total_money: '',
    quantity: '',
    status: '1',
    covers: []
})

//权限控制
const power = reactive({
    update: checkPower('Gift_coupon_update'),
    delete: checkPower('Gift_coupon_delete')
})

const add = () => {
    dataForm.id = 0
    dataForm.name = ''
    dataForm.total_money = ''
    dataForm.quantity = ''
    dataForm.icon = ''
    dataForm.status = '1'
    dataForm.type = '1'
    dataForm.covers = []
    dataForm.cover = ''
    dataForm.gids = [];
    dataForm.qx = 0;
    // coverList.value = []
    iconList.value = []
    configForm.visible = true
    configForm.title = '添加券'
    configForm.isEdit = false
}

const edit = (idx: number, item: any) => {
    actItem.value = item
    dataForm.id = item.id
    dataForm.discount = item.discount
    iconList.value = []

    // id: 49
    // name: sfja
    // discount: 1.00

    // money: 0.00
    // stock_num: 1
    // type: 1
    // status: 3
    // effective_time: 2023-01-18T16:00:00.000Z
    // remark:
    // cover: uploads/2023/01/18/63c6f36859743.png  

    iconList.value.push({ src: item.cover })
    dataForm.name = item.name
    dataForm.money = item.money
    dataForm.stock_num = item.stock_num
    dataForm.status = item.status.toString()
    dataForm.type = item.type.toString()
    dataForm.effective_time = item.effective_time
    dataForm.remark = item.remark
    dataForm.gids = item.gids
    dataForm.qx = item.qx
    configForm.visible = true
    configForm.title = '编辑券'
    // configForm.isEdit = true
}

//弹层打开后回调
const dialogOpened = () => {
    /*        if(insObj){
                editor.value=insObj.refs['editor']
            }
            if(configForm.isEdit) {
                editor.value.setHtml(actItem.value.content)
            }else{
                editor.value.clear()
            }*/
}

//弹层关闭后
const onDialogClosed = () => {
    // editor.value.clear()
}

const save = () => {
    // dataForm.content=editor.value.getHtml()
    if (iconList.value[0]) {
        dataForm.cover = iconList.value[0].src
    }
    // dataForm.covers = []
    // for (let i in coverList.value) {
    //     dataForm.covers.push(coverList.value[i].src)
    // }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    let pdata = {}
    for (let i in dataForm) {
        pdata[i] = dataForm[i]
    }
    http({
        url: 'c=Gift&a=coupon_update',
        data: pdata
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        configForm.visible = false  //关闭弹层
        if (!configForm.isEdit) {//添加的重新加载
            pageRef.value.doSearch()
        } else {//动态更新字段
            for (let i in dataForm) {
                actItem.value[i] = dataForm[i]
            }
            actItem.value.status_flag = res.data.status_flag
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                isRequest = false
            }
        })
    })
}

const del = (idx: number, item: any) => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    http({
        url: 'c=Gift&a=coupon_delete',
        data: { id: item.id }
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        //更新数据集
        pageRef.value.delItem(idx)

        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                isRequest = false
            }
        })
    })
}

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

const onPreviewImg = (src: string) => {
    showImg(getSrcUrl(src))
}

onMounted(() => {

})
</script>