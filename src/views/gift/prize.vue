<template>
    <Page url="c=Gift&a=prize" ref="pageRef">
        <!-- <template #btn="myScope">
            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add">添加红包</el-button>
        </template> -->

        <!-- <template #search="{ params, tdata, doSearch }">
            <el-select size="small" style="width: 110px;margin-left: 10px;" v-model="params.s_status" placeholder="所有状态">
                <el-option key="0" label="所有状态" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
        </template> -->

        <template #table>
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="name" label="名称" min-width="180"></el-table-column>
            <el-table-column prop="type_flag" label="类型" min-width="60"></el-table-column>
            <el-table-column prop="cover" label="图标" width="160">
                <template #default="{ row }">
                    <img v-if="row.cover" :src="imgFlag(row.cover)" @click="onPreviewImg(row.cover)"
                        style="height: 40px;vertical-align: middle;">
                    <span v-else>/</span>
                </template>
            </el-table-column>


            <el-table-column prop="probability" label="中奖概率" width="120"></el-table-column>
            <el-table-column prop="from_money" label="最小金额" width="120"></el-table-column>
            <el-table-column prop="to_money" label="最大金额" width="120"></el-table-column>
            <el-table-column prop="goods_name" label="产品名称" width="120"></el-table-column>
            <el-table-column prop="coupon_name" label="奖券名称" width="140"></el-table-column>
            <el-table-column prop="remark" label="备注" width="140"></el-table-column>


            <el-table-column label="操作" width="160">
                <template #default="scope">
                    <el-button size="small" v-if="power.update" @click="edit(scope.$index, scope.row)">编辑</el-button>
                </template>
            </el-table-column>
        </template>

        <template #summary="{ tdata }">
            <span>记录数：{{ tdata.count }}</span>
        </template>

        <template #layer="{ tdata }">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="奖品名称">
                        <el-input size="small" v-model="dataForm.name" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>

                    <el-form-item label="图标">
                        <MyUpload2 v-model:file-list="coverList" width="80px" height="80px"
                            style="line-height: initial;"></MyUpload2>
                    </el-form-item>

                    <el-form-item label="中奖概率">
                        <el-input size="small" v-model="dataForm.probability" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder="" style="width: 180px;"></el-input>
                        <span>%</span>
                    </el-form-item>

                    <el-form-item label="奖品类型">

                        <el-radio-group v-model="dataForm.type">
                            <el-radio :label="idx" v-for="(item, idx) in tdata.type_arr">{{ item }}</el-radio>
                        </el-radio-group>


                    </el-form-item>

                    <el-form-item v-if="dataForm.type == '1'" label="金额区间" style="margin-bottom: 0;">
                        <el-input size="small" style="width: 110px;margin-left: 10px;" v-model="dataForm.from_money"
                            autocomplete="off" placeholder="最小金额"></el-input> 到
                        <el-input size="small" style="width: 110px;margin-left: 10px;" v-model="dataForm.to_money" autocomplete="off"
                            placeholder="最大金额"></el-input>
                    </el-form-item>

                    <el-form-item v-if="dataForm.type == '2'" label="产品" style="margin-bottom: 0;">
                        <el-select size="small" style="width: 310px;margin-left: 10px;" v-model="dataForm.gid" placeholder="请选择">
                            <el-option key="0" label="请选择" value="0"></el-option>
                            <el-option v-for="(item ) in tdata.goods_arr" :key="item.id" :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>


                    <el-form-item v-if="dataForm.type == '3'" label="中奖描述" style="margin-bottom: 0;">
                        <el-input size="small" v-model="dataForm.remark" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder="" style="width: 310px;"></el-input>
                    </el-form-item>


                    <el-form-item v-if="dataForm.type == '4'" label="中奖描述" style="margin-bottom: 0;">
                        <el-input size="small" v-model="dataForm.remark" :disabled="configForm.isEdit" autocomplete="off"
                            placeholder="" style="width: 310px;"></el-input>
                    </el-form-item>


                    <el-form-item v-if="dataForm.type == '5'" label="奖券" style="margin-bottom: 0;">


                        <el-select size="small" style="width: 310px;margin-left: 10px;" v-model="dataForm.coupon_id"
                            placeholder="请选择">
                            <el-option key="0" label="请选择" value="0"></el-option>
                            <el-option v-for="(item ) in tdata.coupon_arr" :key="item.id" :label="item.name"
                                :value="item.id">
                            </el-option>
                        </el-select>

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
    update: checkPower('Gift_prize_update'),
    delete: checkPower('Gift_prize_delete')
})

const add = () => {


    dataForm.coupon_id = ''
    dataForm.coupon_name = ""
    dataForm.cover = ""
    dataForm.from_money = ""
    dataForm.gid = 0
    dataForm.goods_name = ""
    dataForm.id = 0
    dataForm.name = ""
    dataForm.probability = 5
    dataForm.remark = ""
    dataForm.to_money = 0
    dataForm.type = '0'
    dataForm.type_flag = ""
    dataForm.covers = []
    coverList.value = []
    iconList.value = []

}

const edit = (idx: number, item: any) => {
    actItem.value = item

    dataForm.coupon_id = item.coupon_id.toString()
    dataForm.coupon_name = item.coupon_name
    dataForm.cover = item.cover
    dataForm.from_money = item.from_money
    dataForm.gid = item.gid.toString()
    dataForm.goods_name = item.goods_name
    dataForm.id = item.id.toString()
    dataForm.name = item.name
    dataForm.probability = item.probability
    dataForm.remark = item.remark
    dataForm.to_money = item.to_money
    dataForm.type = item.type.toString()
    dataForm.type_flag = item.type_flag

    console.log(dataForm.type);
    iconList.value = []
    coverList.value = []
    coverList.value.push({ src: item.cover })


    configForm.visible = true
    configForm.title = '编辑奖项'
    configForm.isEdit = false
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
    // if (iconList.value[0]) {
    //     dataForm.icon = iconList.value[0].src
    // }
    dataForm.cover = coverList.value[0].src
    // for (let i in coverList.value) {
    //     dataForm.cover.push(coverList.value[i].src)
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
        url: 'c=Gift&a=prize_update',
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
        url: 'c=Gift&a=prize_delete',
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