<template>
    <Page url="c=User&a=message">

        <template #table="myScope">
            <el-table-column prop="id" label="ID" width="80"></el-table-column>
<!--            <el-table-column prop="title" label="标题"></el-table-column>-->
            <el-table-column prop="phone" label="手机"></el-table-column>
            <el-table-column prop="email" label="邮箱"></el-table-column>
            <el-table-column prop="content" label="内容"></el-table-column>
<!--            <el-table-column prop="cover" label="附件图" width="140" class-name="imgCellBox">
                <template #default="{row}">
                    <el-image
                            v-for="vo in row.covers"
                            style="width: 40px;height: 40px;vertical-align: middle;margin-right: 5px;"
                            fit="cover"
                            :src="imgFlag(vo)"
                            hide-on-click-modal
                            :preview-src-list="[imgFlag(vo)]">
                    </el-image>
                </template>
            </el-table-column>-->
            <el-table-column prop="create_time" label="时间" width="130"></el-table-column>
            <el-table-column label="操作" width="190">
                <template #default="scope">
                    <el-popconfirm
                            confirmButtonText='确定'
                            cancelButtonText='取消'
                            icon="el-icon-warning"
                            iconColor="red"
                            title="您确定要进行删除吗？"
                            @confirm="del(scope.$index,scope.row,myScope)">
                        <template #reference>
                            <el-button type="danger" size="small">删除</el-button>
                        </template>
                    </el-popconfirm>
                    <el-button size="small" @click="reply(scope.$index,scope.row)">
                        回复<span v-if="scope.row.is_new==1" style="margin-left: 5px;color: #ff6191;">New</span>
                    </el-button>

                </template>
            </el-table-column>
        </template>
        <template #summary="{tdata}">
            <span>记录数：{{tdata.count}}</span>
        </template>

        <template #layer>
            <!--弹出层-->
            <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width" :top="configForm.top" @opened="dialogOpened" @closed="onDialogClosed">
                <el-form :label-width="configForm.labelWidth">
                    <Page :url="pageUrl2" v-if="replyShow" title="回复记录" ref="pageRef2">
                        <template #search2>&nbsp;</template>
                        <template #table="myScope2">
                            <el-table-column label="用户" width="180">
                                <template #default="{row}">
                                    <span v-if="row.account">{{row.account}}/{{row.nickname}}</span>
                                    <span v-else>系统</span>
                                </template>
                            </el-table-column>
                            <el-table-column prop="content" label="回复内容"></el-table-column>
                            <el-table-column prop="create_time" label="时间"></el-table-column>
                        </template>
                    </Page>
                    <el-form-item label="回复内容">
                        <el-input type="textarea" v-model="replyForm.content" autocomplete="off" rows="3"></el-input>
                    </el-form-item>
                </el-form>
                <template #footer>
            <span class="dialog-footer">
                <el-button @click="configForm.visible = false">取消</el-button>
                <el-button type="primary" @click="save">提交回复</el-button>
            </span>
                </template>
            </el-dialog>
        </template>

    </Page>
</template>

<script lang="ts" setup>
    import {defineEmits,ref,onMounted, reactive,nextTick} from 'vue'
    import http from "../../global/network/http";
    import {getZero, _alert, getSrcUrl, showImg} from "../../global/common";
    import { checkPower } from '../../global/user';
    import Page from "../../components/Page.vue"
    import Upload from '../../components/Upload.vue'
    import {useStore} from "vuex";

    const store=useStore()
    let pageScope:any
    let isRequest=false

    //权限控制
    const power=reactive({
        reply:checkPower('User_message_reply'),
        delete:checkPower('User_message_delete'),
    })

    const configForm=reactive({
        title:'',
        width:'840px',
        labelWidth:'100px',
        visible:false,
        isEdit:false
    })

    const actItem=ref<any>({})
    const dataForm=reactive<any>({
        id:0,
        nid:'',
        name:'',
        sort:'',
        remark:'',
        cover:''
    })
    const coverList=ref<any>([])

    //弹层打开后回调
    const dialogOpened=()=>{
        //
    }

    //弹层关闭后
    const onDialogClosed=()=>{
        //
    }

    const replyShow=ref(false)
    const pageUrl2=ref('')
    const pageRef2=ref()

    const replyForm=reactive({
        content:''
    })

    const reply=(idx:number,item:any)=>{
        actItem.value=item
        actItem.value.is_new=0
        pageUrl2.value='c=User&a=message_log&mid='+item.id
        configForm.title='回复'
        nextTick(()=>{
            configForm.visible=true
            replyShow.value=true
        })
    }

    const save=()=>{
        if(!replyForm.content){
            _alert('请填写回复内容')
            return
        }
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        http({
            url: 'c=User&a=message_reply',
            data: {mid: actItem.value.id, content: replyForm.content}
        }).then((res:any)=>{
            isRequest=false
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            replyForm.content=''
            pageRef2.value.doSearch()
            _alert(res.msg)
        })
    }

    const del=(idx:number,item:any,myScope:any)=> {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        http({
            url: 'c=User&a=message_delete',
            data: {id: item.id}
        }).then((res: any) => {
            isRequest = false
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            //更新数据集
            myScope.delItem(idx)
            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => {

                }
            })
        })
    }

    const imgFlag=(src:string)=>{
        return getSrcUrl(src)
    }

    onMounted(()=>{

    })

</script>