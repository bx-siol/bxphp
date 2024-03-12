<template>
<Page url="c=User&a=rauth">
    <template #search="{params,tdata}">
        <el-select style="width: 120px;" v-model="params.s_status" placeholder="认证状态">
            <el-option key="0" label="认证状态" value="0"></el-option>
            <el-option
                    v-for="(item,idx) in tdata.rauth_status"
                    :key="idx"
                    :label="item"
                    :value="idx">
            </el-option>
        </el-select>
    </template>

    <template #table>
        <el-table-column prop="uid" label="UID" width="80"></el-table-column>
        <el-table-column prop="account" label="账号" width="120"></el-table-column>
        <el-table-column prop="realname" label="真实姓名"></el-table-column>
        <el-table-column prop="idcard" label="身份证号" width="180"></el-table-column>
        <el-table-column prop="front" label="证件正面" width="120" class-name="imgCellBox">
            <template #default="scope">
                <el-image
                        style="width: 60px;height: 40px;vertical-align: middle;"
                        fit="cover"
                        :src="imgFlag(scope.row.front)"
                        hide-on-click-modal
                        :preview-src-list="[imgFlag(scope.row.front)]">
                </el-image>
            </template>
        </el-table-column>
        <el-table-column prop="back" label="证件反面" width="120" class-name="imgCellBox">
            <template #default="scope">
                <el-image
                        style="width: 60px;height: 40px;vertical-align: middle;"
                        fit="cover"
                        :src="imgFlag(scope.row.back)"
                        hide-on-click-modal
                        :preview-src-list="[imgFlag(scope.row.back)]">
                </el-image>
            </template>
        </el-table-column>
        <el-table-column prop="status_flag" label="状态" width="100"></el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="130"></el-table-column>
        <el-table-column prop="check_time" label="审核时间" width="130"></el-table-column>
        <el-table-column prop="check_remark" label="审核备注" min-width="100"></el-table-column>
        <el-table-column label="操作" min-width="80">
            <template #default="scope">
                <el-button size="small" v-if="power.check&&scope.row.status<3" @click="check(scope.$index,scope.row)" type="success">审核</el-button>
                <span v-else>/</span>
            </template>
        </el-table-column>
    </template>

    <template #layer="{tdata}">
        <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width">
            <el-form :label-width="configForm.labelWidth">
                <el-form-item label="账号" style="margin-bottom: 0;">
                    <div>{{actItem.account}}（{{actItem.nickname}}）</div>
                </el-form-item>
                <el-form-item label="姓名">
                    <div>{{actItem.realname}}</div>
                </el-form-item>
                <el-form-item label="身份证号">
                    <div>{{actItem.idcard}}</div>
                </el-form-item>
                <el-form-item label="正面">
                    <el-image
                            style="width: 100px;height: 60px;"
                            fit="cover"
                            :src="imgFlag(actItem.front)"
                            hide-on-click-modal
                            :preview-src-list="[imgFlag(actItem.front)]">
                    </el-image>
                </el-form-item>
                <el-form-item label="反面">
                    <el-image
                            style="width: 100px;height: 60px;"
                            fit="cover"
                            :src="imgFlag(actItem.back)"
                            hide-on-click-modal
                            :preview-src-list="[imgFlag(actItem.back)]">
                    </el-image>
                </el-form-item>
                <el-form-item label="状态">
                    <el-radio-group v-model="dataForm.status">
                        <el-radio :label="idx" v-for="(item,idx) in tdata.rauth_status">{{item}}</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="备注">
                    <el-input type="textarea" v-model="dataForm.check_remark" autocomplete="off" rows="3"></el-input>
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
</template>

<script lang="ts" setup>
    import {ref,onMounted, reactive} from 'vue'
    import http from "../../global/network/http"
    import {_alert, getSrcUrl} from "../../global/common"
    import { checkPower } from '../../global/user'
    import {useStore} from "vuex"
    import Page from '../../components/Page.vue'

    let isRequest=false
    const store=useStore()

    //权限控制
    const power=reactive({
        check:checkPower('User_rauth_check'),
    })

    const configForm=reactive({
        title:'审核',
        width:'540px',
        labelWidth:'100px',
        visible:false,
        isEdit:false
    })

    const actItem=ref<any>()
    const dataForm=reactive<any>({
        uid:0,
        status:'',
        check_remark:''
    })

    const check=(idx:number,item:any)=>{
        actItem.value=item
        dataForm.uid=item.uid
        dataForm.check_remark=item.check_remark
        dataForm.status=item.status.toString()
        configForm.visible=true
    }

    const saveCheck=()=>{
        http({
            url: 'c=User&a=rauth_check',
            data: dataForm
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            actItem.value.check_remark=dataForm.check_remark
            actItem.value.check_time=res.data.check_time
            actItem.value.status=res.data.status
            actItem.value.status_flag=res.data.status_flag
            configForm.visible=false
            _alert(res.msg)
        })
    }

    const imgFlag=(src:string)=>{
        if(!src){
            return false
        }
        return getSrcUrl(src)
    }

    onMounted(()=>{

    })
</script>