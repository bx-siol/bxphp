<template>
    <Nav>
        <template #right>
            <van-button type="primary" size="mini" class="navRightBtn" @click="add">添加</van-button>
        </template>
    </Nav>
    <div style="height: 0.8rem;"></div>
    <van-list
            v-model:loading="dataTable.loading"
            :finished="dataTable.finished"
            finished-text="没有更多了"
            @load="getData">
        <van-card
                centered
                style="background: #1a1a1a;color: white;"
                v-for="(item,idx) in dataTable.list"
                :key="item.id"
                :thumb="imgFlag(item.qrcode)"
                @click-thumb="imgPreview(item.qrcode)">
            <template #num></template>
            <template #title>
                <div>{{item.realname}}</div>
            </template>
            <template #desc>
                <div>{{item.account}}</div>
                <div v-if="item.routing">路由号：{{item.routing}}</div>
            </template>
            <template #price><span style="color: white;">{{item.remark}}</span></template>
            <template #tags>
                <van-tag plain :type="item.type==3?'success':'primary'">{{item.type_flag}}</van-tag>&nbsp
                <van-tag plain type="success" v-if="item.bank_id>0">{{item.bank_name}}</van-tag>&nbsp
                <van-tag plain type="warning" v-if="item.province_id>0">{{item.province_name}}/{{item.city_name}}</van-tag>
            </template>
            <template #footer>
                <van-button type="warning" size="mini" @click="del(item.id,idx)">删除</van-button>
                <van-button type="primary" size="mini" @click="edit(item)" style="background-color: #ff4c43;border-color: #ff4c43;">编辑</van-button>
            </template>
        </van-card>
    </van-list>

    <Pop v-model:show="popShow"
         :title="configForm.title"
         :wrapper-style="{marginTop:'3rem',height: '68%',width:'89%',backgroundColor:'rgb(50,50,50)'}"
         :title-style="{borderBottom:0,color:'#ffffff'}"
         :content-style="{padding:'0'}">
        <van-form @submit="onSubmit" :label-width="configForm.labelWidth" :label-align="configForm.labelAlign">
            <van-cell-group>
                <van-field label="类型">
                    <template #input>
                        <van-radio-group v-model="dataForm.type" direction="horizontal">
                            <template v-for="(item,idx) in dataForm.banklog_type">
                                <van-radio class="xxxIcon" style="margin-right: 10px;" v-if="idx<4" :name="idx" icon-size="1rem">{{item}}</van-radio>
                            </template>
                        </van-radio-group>
                    </template>
                </van-field>
                <van-field
                        v-model="dataForm.bank_flag"
                        v-show="dataForm.type==1"
                        is-link
                        readonly
                        label="开户行"
                        placeholder="点击选择银行"
                        @click="bankShow = true"
                />
                <van-field
                        v-model="dataForm.area_flag"
                        v-show="dataForm.type==1"
                        is-link
                        readonly
                        label="地区"
                        placeholder="点击选择省份/城市"
                        @click="areaShow = true"
                />
                <van-field
                        label="姓名"
                        v-model="dataForm.realname"
                        placeholder="请输入"/>
                <van-field
                        label="账号"
                        v-model="dataForm.account"
                        placeholder="请输入"/>
                <van-field
                        label="路由号"
                        v-show="false&&dataForm.type=='1'"
                        v-model="dataForm.routing"
                        placeholder="选填"/>
                <van-field
                        style="padding-bottom: 0"
                        v-show="dataForm.type!='1'"
                        label="收款码">
                    <template #input>
                        <Upload v-model:file-list="fileList"></Upload>
                    </template>
                </van-field>
                <van-field
                        label="备注"
                        v-model="dataForm.remark"
                        placeholder="选填"/>
                <van-field
                        label="二级密码"
                        v-model="dataForm.password"
                        type="password"
                        placeholder="请输入"/>
            </van-cell-group>
            <div style="margin: 16px;">
                <van-button class="myBtn" round block type="primary" native-type="submit">提交</van-button>
            </div>
        </van-form>
    </Pop>

    <!--银行选择-->
    <van-popup v-model:show="bankShow" position="bottom">
        <van-picker
                :columns="banks"
                :columns-field-names="{text:'name'}"
                :default-index="dataForm.bank_id_index"
                @confirm="onBankConfirm"
                @cancel="bankShow = false"
        />
    </van-popup>

    <!--省市选择-->
    <van-popup v-model:show="areaShow" position="bottom">
        <van-area
                :area-list="areaList"
                :value="dataForm.city_id"
                :columns-num="2"
                @confirm="onAreaConfirm"
                @cancel="areaShow = false"/>
    </van-popup>

</template>

<script lang="ts">
import {defineComponent} from 'vue';
import {
    Form,
    Field,
    Tag,
    CellGroup,
    Cell,
    Button,
    Icon,
    RadioGroup,
    Radio,
    Image,
    Loading,
    Dialog,
    Uploader,
    List,
    Card,
    Area,
    Picker,
    Popup
} from 'vant'
import Nav from '../../components/Nav.vue'
import Pop from '../../components/Pop.vue'
import Upload from '../../components/Upload.vue'

export default defineComponent({
    components:{
        Nav, Pop,Upload,
        [Form.name]:Form,
        [List.name]:List,
        [Card.name]:Card,
        [Tag.name]:Tag,
        [Field.name]:Field,
        [CellGroup.name]:CellGroup,
        [Cell.name]:Cell,
        [RadioGroup.name]:RadioGroup,
        [Radio.name]:Radio,
        [Icon.name]:Icon,
        [Image.name]:Image,
        [Area.name]:Area,
        [Picker.name]:Picker,
        [Popup.name]:Popup,
        [Loading.name]:Loading,
        [Uploader.name]:Uploader,
        [Dialog.Component.name]: Dialog.Component,
        [Button.name]:Button
    }
})
</script>

<script lang="ts" setup>
    import {ref,reactive,onMounted} from 'vue';
    import { areaList } from '@vant/area-data';
    import {http} from "../../global/network/http";
    import {_alert, getSrcUrl, imgPreview} from "../../global/common";
    import md5 from "md5";
    import {Dialog} from "vant";

    let isRequest=false

    const popShow=ref(false)

    const configForm=reactive({
        title:'',
        labelAlign:'right',
        labelWidth:'3.5rem'
    })

    let nowItem= ref<any>()
    const dataForm=reactive({
        banklog_type:{},
        area_flag:'',
        bank_flag:'',
        id:0,
        province_id:'110000',
        city_id:'110100',
        bank_id:0,
        bank_id_index:0,
        type:'1',
        account:'',
        realname:'',
        routing:'',
        qrcode:'',
        remark:'',
        password:''
    })

    const fileList=ref<any[]>([])

    const getItemInfo=(id:number,cb:any)=>{
        http({
            url:'c=Setting&a=banklog_info',
            data:{id:id}
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            cb(res.data)
        })
    }

    const add=()=> {
        configForm.title='添加'
        dataForm.id=0
        dataForm.realname=''
        dataForm.account=''
        dataForm.routing=''
        dataForm.qrcode=''
        dataForm.remark=''
        dataForm.password=''
        fileList.value=[]
        popShow.value = true
    }

    const edit=(item:any)=>{
        configForm.title='编辑'
        nowItem.value=item
        dataForm.id=item.id
        dataForm.type=item.type.toString()
        dataForm.realname=item.realname
        dataForm.account=item.account
        dataForm.routing=item.routing
        dataForm.qrcode=item.qrcode
        dataForm.remark=item.remark
        dataForm.bank_id=item.bank_id
        dataForm.province_id=item.province_id
        dataForm.city_id=item.city_id.toString()
        if(item.bank_id>0){
            dataForm.bank_flag=item.bank_name
            for(let i=0;i<banks.value.length;i++){
                if(banks.value[i].id==item.bank_id){
                    dataForm.bank_id_index=i
                }
            }
        }
        if(item.province_id>0){
            dataForm.area_flag=item.province_name
        }
        if(item.city_id>0){
            dataForm.area_flag+=' / '+item.city_name
        }
        dataForm.password=''
        if(item.qrcode){
            fileList.value=[{src:item.qrcode}]
        }
        popShow.value = true
    }

    const onSubmit=()=>{
        if(isRequest){
            return
        }
        if(fileList.value[0]){
            dataForm.qrcode=fileList.value[0].src
        }else{
            dataForm.qrcode=''
        }
        let password2=md5(dataForm.password)
        isRequest=true
        http({
            url:'c=Setting&a=banklog_update',
            data:{
                id:dataForm.id,
                type:dataForm.type,
                bank_id:dataForm.bank_id,
                province_id:dataForm.province_id,
                city_id:dataForm.city_id,
                realname:dataForm.realname,
                account:dataForm.account,
                routing:dataForm.routing,
                qrcode:dataForm.qrcode,
                remark:dataForm.remark,
                password2:password2
            }
        }).then((res:any)=>{
            setTimeout(()=>{
                isRequest=false
            },2000)
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{
                    popShow.value=false
                    if(dataForm.id==0){
                        location.reload()
                    }else{
                        getItemInfo(dataForm.id,(item:any)=>{
                            for(let i in item){
                                nowItem.value[i]=item[i]
                            }
                        })
                    }
                }
            })
        })
    }

    const del=(id:number,idx:number)=>{
        Dialog.confirm({
            message: '您确定要删除吗？',
            confirmButtonText:'确定',
            cancelButtonText:'取消',
            width:'280px',
            allowHtml:true,
            beforeClose:(action:string):Promise<boolean>|boolean => {
                if(action!='confirm'){
                    return true
                }
                return new Promise((resolve) => {
                    http({
                        url:'c=Setting&a=banklog_delete',
                        data:{id:id}
                    }).then((res: any) => {
                        if (res.code != 1) {
                            _alert(res.msg)
                            return
                        }
                        resolve(true);
                        _alert({
                            type: 'success',
                            message: res.msg,
                            onClose: () => {
                                dataTable.list.splice(idx,1)
                            }
                        })
                    })
                })
            }
        }).catch(()=>{})
    }

    //分页查询
    const dataTable = reactive({
        list: new Array(),
        loading: false,
        finished: false,
    });

    //获取数据
    const pdata=reactive({
        page:1
    })
    const getData=()=>{
        if(isRequest){
            return
        }
        isRequest=true
        http({
            url:'c=Setting&a=banklog',
            data:pdata
        }).then((res:any)=>{
            dataTable.loading = false;
            isRequest=false
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            if(pdata.page==1){
                dataForm.banklog_type=res.data.banklog_type
                banks.value=res.data.banks
            }
            dataTable.finished=res.data.finished
            pdata.page=res.data.page
            for(let i in res.data.list){
                dataTable.list.push(res.data.list[i])
            }
        })
    }

    const imgFlag=(src:string)=>{
        return getSrcUrl(src, 1)
    }

    const banks=ref<any>([])
    const bankShow=ref(false)
    const areaShow=ref(false)

    const onBankConfirm=(val:any)=>{
        bankShow.value=false
        dataForm.bank_id=val.id
        dataForm.bank_flag=val.name
    }

    const onAreaConfirm=(val:any)=>{
        areaShow.value=false
        if(val[0]){
            dataForm.province_id=val[0].code
        }
        if(val[1]){
            dataForm.city_id=val[1].code
        }
        let nameArr=[]
        for(let i in val){
            nameArr.push(val[i].name)
        }
        dataForm.area_flag=nameArr.join(' / ')
    }

    onMounted(()=>{
        getData()
    })
</script>

<style>
    .xxxIcon .van-radio__icon--checked .van-icon{background-color: #ff4c43;border-color: #ff4c43;}
</style>