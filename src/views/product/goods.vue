<template>
    <Page url="c=Product&a=goods" ref="pageRef">
        <template #btn="myScope">
            <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add">添加产品</el-button>
        </template>

        <template #search="{ params, tdata, doSearch }">
            <el-select style="width: 200px;margin-left: 10px;" v-model="params.s_cid" placeholder="所属分类">
                <el-option key="0" label="所属分类" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.category_tree" :key="item.id" :label="item.name" :value="item.id">
                </el-option>
            </el-select>
            <el-select style="width: 110px;margin-left: 10px;" v-model="params.s_status" placeholder="所有状态">
                <el-option key="0" label="所有状态" value="0"></el-option>
                <el-option v-for="(item, idx) in tdata.status_arr" :key="idx" :label="item" :value="idx">
                </el-option>
            </el-select>
        </template>

        <template #table>
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
            <el-table-column prop="gsn" label="序列号" min-width="160"></el-table-column>
            <el-table-column prop="name" label="产品名称" min-width="280"></el-table-column>
            <el-table-column prop="category_name" label="分类" width="120"></el-table-column>
            <!--        <el-table-column prop="guarantors" label="担保机构" width="300"></el-table-column>-->
            <el-table-column prop="days" label="项目期限(天)" width="120"></el-table-column>
            <el-table-column prop="rate" label="每天收益(%)" width="120"></el-table-column>
            <!--        <el-table-column prop="scale" label="项目总额" width="120"></el-table-column>-->
            <el-table-column prop="price" label="单价" width="120"></el-table-column>
            <el-table-column prop="Integral" label="上级赠送积分" width="100"></el-table-column>
            <el-table-column prop="selfintegral" label="赠送积分" width="100"></el-table-column>
            <el-table-column prop="selfbg" label="赠送余额" width="100"></el-table-column>
            <el-table-column prop="price1" label="用户奖励" width="100"></el-table-column>
            <el-table-column prop="price2" label="上级奖励" width="100"></el-table-column>
            <el-table-column prop="cjcs" label="用户抽奖" width="100"></el-table-column>
            <el-table-column prop="sjcjcs" label="上级抽奖" width="100"></el-table-column>
            <el-table-column prop="invest_limit" label="限购数量" width="120">
                <template #default="{ row }">
                    <template v-if="row.invest_limit > 0">{{ row.invest_limit }}</template>
                    <template v-else>不限</template>
                </template>
            </el-table-column>
            <el-table-column prop="invested" label="已购买" width="120"></el-table-column>
            <!--        <el-table-column prop="v_invested" label="虚拟投资" width="120"></el-table-column>-->
            <!--        <el-table-column prop="invest_min" label="最小投资" width="120"></el-table-column>-->
            <el-table-column prop="icon" label="图标" width="70">
                <template #default="{ row }">
                    <img :src="imgFlag(row.icon)" @click="onPreviewImg(row.icon)"
                        style="height: 40px;vertical-align: middle;">
                </template>
            </el-table-column>
            <el-table-column prop="icon" label="相册" min-width="160">
                <template #default="{ row }">
                    <template v-for="vo in row.covers">
                        <!--                    <img :src="imgFlag(vo)" @click="onPreviewImg(vo)" style="width: 70px;height: 40px;vertical-align: middle;margin-right: 5px;">-->
                        <el-image :src="imgFlag(vo)" @click="onPreviewImg(vo)"
                            style="width: 60px;height: 40px;margin: 0 3px;" />
                    </template>
                </template>
            </el-table-column>
            <el-table-column prop="is_hot_flag" label="热门" width="80">
                <template #default="{ row }">
                    <el-switch v-model="row.is_hot_switch" @change="onSwitch($event, row, 'is_hot')" />
                </template>
            </el-table-column>
            <el-table-column prop="goodsindex_flag" label="首页推荐" width="80">
                <template #default="{ row }">
                    <el-switch v-model="row.goodsindex_switch" @change="onSwitch($event, row, 'goodsindex')" />
                </template>
            </el-table-column>
            <el-table-column prop="kc" label="库存" width="70"></el-table-column>
            <el-table-column prop="is_xskc_flag" label="显示库存" width="70"></el-table-column>
            <el-table-column prop="yaoqing" label="需邀请数" width="70"></el-table-column>
            <el-table-column prop="status_flag" label="状态" width="70"></el-table-column>
            <el-table-column prop="sort" label="排序(大->小)" width="120"></el-table-column>
            <el-table-column label="操作" width="160" fixed="right">
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
        </template>

        <template #layer="{ tdata }">
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false"
                :width="configForm.width" :top="configForm.top" @opened="dialogOpened">
                <el-form :label-width="configForm.labelWidth">
                    <el-form-item label="产品名称">
                        <el-input v-model="dataForm.name" autocomplete="off" placeholder=""></el-input>
                    </el-form-item>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="所属分类">
                                <el-select style="width: 300px;" v-model="dataForm.cid" placeholder="选择分类">
                                    <el-option key="0" label="选择分类" value="0"></el-option>
                                    <el-option v-for="(item, idx) in tdata.category_tree" :key="item.id" :label="item.name" :value="item.id">
                                    </el-option>
                                </el-select>
                            </el-form-item>
                        </el-col>
                        <el-col :span="16">
                            <el-form-item label="购买送自己">
                            <el-select style="width: 300px;" v-model="dataForm.gifttoself" placeholder="选择产品">
                                <el-option key="0" label="选择产品" value="0"></el-option>
                                <el-option v-for="(item, idx) in tdata.giftgoods" :key="item.id" :label="item.name" :value="item.id">
                                </el-option>
                            </el-select>
                            <span>&nbsp;&nbsp;设置此参数后 用户购买产品会送一个产品给当前购买的用户</span>
                        </el-form-item>
                        </el-col>                        
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="产品单价">
                                <el-input v-model="dataForm.price" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="16">
                            <el-form-item label="购买送推荐人">
                                <el-select style="width: 300px;" v-model="dataForm.gifttopuser" placeholder="选择产品">
                                    <el-option key="0" label="选择产品" value="0"></el-option>
                                    <el-option v-for="(item, idx) in tdata.giftgoods" :key="item.id" :label="item.name" :value="item.id"></el-option>
                                </el-select>
                                <span>&nbsp;&nbsp;设置此参数后 用户购买产品会送一个产品给当前购买用户的推荐人</span>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="上级赠送积分">
                                <el-input v-model="dataForm.Integral" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span> </span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="赠送积分">
                                <el-input v-model="dataForm.selfintegral" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span> </span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="赠送余额">
                                <el-input v-model="dataForm.selfbg" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span> </span>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="产品期限">
                                <el-input v-model="dataForm.days" autocomplete="off" placeholder="" style="width: 275px;"></el-input>
                                <span>&nbsp;&nbsp;天</span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="16">
                            <el-form-item label="限充值钱包">
                                <el-input v-model="dataForm.buyday" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span>&nbsp;&nbsp;设置此参数后只能使用充值钱包购买该产品。默认为0</span>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="收益率(%)">
                                <el-input v-model="dataForm.rate" autocomplete="off" placeholder="" style="width: 260px;"></el-input>
                                <span>&nbsp;&nbsp;每天</span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="16">
                            <el-form-item label="多久后领取">
                                <el-input v-model="dataForm.dayout" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span>&nbsp;&nbsp;天&nbsp;&nbsp;(设置此参数后只能在达到限定天数后领取收益。默认为0)</span>
                            </el-form-item>
                        </el-col>                        
                    </el-row>
                    <el-row>
                        <el-col :span="12">
                            <el-form-item label="限购数量">
                                <el-input v-model="dataForm.invest_limit" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span>&nbsp;&nbsp;填0或空则不限</span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="复购送自己">
                                <el-input v-model="dataForm.price0" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span>&nbsp;&nbsp;复购送自己金额</span>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="12">
                            <el-form-item label="首购送自己">
                                <el-input v-model="dataForm.price1" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span>&nbsp;&nbsp;首次购买送自己金额</span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="首购送上级">
                                <el-input v-model="dataForm.price2" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span>&nbsp;&nbsp;首次购买送上级金额</span>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="12">
                            <el-form-item label="首购抽奖">
                                <el-input v-model="dataForm.cjcs" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span>&nbsp;&nbsp;首次购买送自己抽奖</span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="首购上级抽奖">
                                <el-input v-model="dataForm.sjcjcs" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                                <span>&nbsp;&nbsp;首次购买送上级抽奖</span>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="12">
                            <el-form-item label="定时上架">
                                <el-date-picker v-model="dataForm.dssj" type="datetime" placeholder="请选择"></el-date-picker>
                                <span>&nbsp;&nbsp;定时上架时间 </span>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="倒计时">
                                <el-date-picker v-model="dataForm.djs" type="datetime" placeholder="请选择"></el-date-picker>
                                <span>&nbsp;&nbsp;倒计时截止时间 </span>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="12">
                            <el-form-item label="担保机构">
                                <el-input v-model="dataForm.guarantors" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="12">
                            <el-form-item label="排序">
                                <el-input v-model="dataForm.sort" autocomplete="off" style="width: 300px;"></el-input>
                                <span>&nbsp;&nbsp;从大到小</span>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-form-item label="图标">
                        <MyUpload v-model:file-list="iconList" width="80px" height="80px" style="line-height: initial;"></MyUpload>
                    </el-form-item>
                    <el-form-item label="相册">
                        <MyUpload v-model:file-list="coverList" :limit="5" width="180px" height="100px" style="line-height: initial;"></MyUpload>
                    </el-form-item>

                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="热门" style="margin-bottom: 0;">
                                <el-radio-group v-model="dataForm.is_hot">
                                    <el-radio :label="idx" v-for="(item, idx) in store.state.config.yes_or_no">{{ item }}</el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="库存">
                                <el-input v-model="dataForm.kc" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="需邀请人数">
                                <el-input v-model="dataForm.yaoqing" autocomplete="off" placeholder="" style="width: 300px;"></el-input>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="显示库存" style="margin-bottom: 0;">
                                <el-radio-group v-model="dataForm.is_xskc">
                                    <el-radio :label="idx" v-for="(item, idx) in store.state.config.yes_or_no">{{ item }}</el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="状态" style="margin-bottom: 0;">
                                <el-radio-group v-model="dataForm.status">
                                    <el-radio :label="idx" v-for="(item, idx) in tdata.status_arr">{{ item }}</el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="礼物" style="margin-bottom: 0;">
                                <el-radio-group v-model="dataForm.gift">
                                    <el-radio :label="idx" v-for="(item, idx) in store.state.config.yes_or_no">{{ item }}</el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-row>
                        <el-col :span="8">
                            <el-form-item label="积分产品" style="margin-bottom: 0;">
                                <el-radio-group v-model="dataForm.pointshop">
                                    <el-radio :label="idx" v-for="(item, idx) in store.state.config.yes_or_no">{{ item }}</el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </el-col>
                        <el-col :span="8">
                            <el-form-item label="首页推荐" style="margin-bottom: 0;">
                                <el-radio-group v-model="dataForm.goodsindex">
                                    <el-radio :label="idx" v-for="(item, idx) in store.state.config.yes_or_no">{{ item }}</el-radio>
                                </el-radio-group>
                            </el-form-item>
                        </el-col>
                    </el-row>
                    <el-form-item label="产品详情">
                        <Editor :height="300" ref="editor"></Editor>
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
import MyUpload from '../../components/Upload.vue';
import Editor from '../../components/Editor.vue';

export default defineComponent({
    components: {
        Editor, MyUpload
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
    width: '1300px',
    labelWidth: '100px',
    top: '1%',
    visible: false,
    isEdit: false
})

const coverList = ref<any>([])
const iconList = ref<any>([])
const actItem = ref<any>()

const dataForm = reactive<any>({
    gifttopuser: 0,
    gifttoself: 0,
    djs: 0,
    dssj: 0,
    id: 0,
    name: '',
    cid: 0,
    gsn: '',
    icon: '',
    days: '',
    rate: '',
    scale: '',
    price: '',
    price1: '',
    price0: '',
    sjcjcs: '',
    cjcs: '',
    price2: '',
    buyday: '0',
    dayout: '0',
    invest_min: '',
    invest_limit: '',
    v_invested: '',
    guarantors: '',
    content: '',
    sort: 1000,
    gift: '0',
    pointshop: '0',
    goodsindex: '0',
    is_hot: '0',
    status: '1',
    covers: [],
    is_xskc: '0',
    kc: 0,
    yaoqing: 0,
    Integral: '0',
    selfintegral: '0',
    selfbg: '0',
})

//权限控制
const power = reactive({
    update: checkPower('Product_goods_update'),
    delete: checkPower('Product_goods_delete')
})

const add = () => {
    dataForm.gifttopuser = ''
    dataForm.gifttoself = ''
    dataForm.id = 0
    dataForm.name = ''
    dataForm.cid = ''
    dataForm.gsn = ''
    dataForm.days = ''
    dataForm.rate = ''
    dataForm.scale = ''
    dataForm.price = ''
    dataForm.price0 = '0'
    dataForm.sjcjcs = '0'
    dataForm.cjcs = '0'
    dataForm.price1 = '0'
    dataForm.price2 = '0'
    dataForm.buyday = '0'
    dataForm.dayout = '0'
    dataForm.invest_min = ''
    dataForm.invest_limit = ''
    dataForm.v_invested = ''
    dataForm.guarantors = ''
    dataForm.icon = ''
    dataForm.content = ''
    dataForm.sort = 1000
    dataForm.status = '1'
    dataForm.gift = '0'
    dataForm.pointshop = '0'
    dataForm.goodsindex = '0'
    dataForm.is_hot = '0'
    dataForm.is_xskc = '0'
    dataForm.kc = 0
    dataForm.covers = []
    coverList.value = []
    iconList.value = []
    configForm.visible = true
    configForm.title = '添加产品'
    configForm.isEdit = false
    dataForm.yaoqing = 0
    dataForm.Integral = '0'
    dataForm.selfintegral = '0'
    dataForm.selfbg = '0'
    dataForm.djs = '0'
    dataForm.dssj = '0'
}

const edit = (idx: number, item: any) => {
    actItem.value = item
    dataForm.id = item.id
    dataForm.icon = item.icon
    iconList.value = []
    coverList.value = []
    for (let i in item.covers) {
        coverList.value.push({ src: item.covers[i] })
    }
    iconList.value.push({ src: item.icon })
    dataForm.name = item.name
    dataForm.cid = item.cid
    dataForm.gsn = item.gsn
    dataForm.is_xskc = item.is_xskc.toString()
    dataForm.kc = item.kc
    dataForm.days = item.days
    dataForm.rate = item.rate
    dataForm.scale = item.scale
    dataForm.price = item.price
    dataForm.price1 = item.price1
    dataForm.price0 = item.price0
    dataForm.djs = item.djs
    dataForm.dssj = item.dssj
    dataForm.gifttopuser = item.gifttopuser
    dataForm.gifttoself = item.gifttoself

    dataForm.sjcjcs = item.sjcjcs
    dataForm.cjcs = item.cjcs
    dataForm.Integral = item.Integral
    dataForm.selfintegral = item.selfintegral
    dataForm.selfbg = item.selfbg
    dataForm.price2 = item.price2
    dataForm.buyday = item.buyday == null ? '0' : item.buyday
    dataForm.dayout = item.dayout == null ? '0' : item.dayout
    dataForm.invest_limit = item.invest_limit
    dataForm.invest_min = item.invest_min
    dataForm.v_invested = item.v_invested
    dataForm.guarantors = item.guarantors
    dataForm.sort = item.sort
    dataForm.yaoqing = item.yaoqing
    dataForm.status = item.status.toString()    
    dataForm.gift = item.gift.toString()    
    dataForm.pointshop = item.pointshop.toString()
    dataForm.goodsindex = item.goodsindex.toString()
    dataForm.is_hot = item.is_hot.toString()
    dataForm.covers = item.covers
    configForm.visible = true
    configForm.title = '编辑产品'
    configForm.isEdit = true
}

//弹层打开后回调
const dialogOpened = () => {
    if (insObj) {
        editor.value = insObj.refs['editor']
    }
    if (configForm.isEdit) {
        editor.value.setHtml(actItem.value.content)
    } else {
        editor.value.clear()
    }
}

//弹层关闭后
const onDialogClosed = () => {
    editor.value.clear()
}

const save = () => {
    dataForm.content = editor.value.getHtml()
    if (iconList.value[0]) {
        dataForm.icon = iconList.value[0].src
    }
    dataForm.covers = []
    for (let i in coverList.value) {
        dataForm.covers.push(coverList.value[i].src)
    }
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
        url: 'c=Product&a=goods_update',
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

            /*                for(let i in pageRef.value.tableData.category_tree){
                                let it=pageRef.value.tableData.category_tree[i]
                                if(it.id==dataForm.cid){
                                    actItem.category_name=it.name
                                    break
                                }
                            }*/

            actItem.value.category_name = res.data.category_name
            actItem.value.status_flag = res.data.status_flag
            actItem.value.goodsindex_flag = res.data.goodsindex_flag
            actItem.value.goodsindex_switch = actItem.value.goodsindex == 1 ? true : false
            actItem.value.is_hot_flag = res.data.is_hot_flag
            actItem.value.is_hot_switch = actItem.value.is_hot == 1 ? true : false
            actItem.value.is_xskc_flag = res.data.is_xskc_flag
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
        url: 'c=Product&a=goods_delete',
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
                setTimeout(() => {
                    isRequest = false
                }, 2000)
            }
        })
    })
}

const onSwitch = (ev: any, item: any, fieldName: string) => {
    let value = ev ? 1 : 0
    http({
        url: 'a=changeTableVal',
        data: {
            table: 'pro_goods',
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
        item[fieldName + '_switch'] = value == 1 ? true : false
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