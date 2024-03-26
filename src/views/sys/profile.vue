<template>

    <div class="conbox">
        <el-row>
            <el-col :span="10">
                <el-form :style="{ width: configForm.width }" :label-width="configForm.labelWidth">
                    <el-form-item label="账号">
                        <el-input size="small" v-model="userinfo.account" autocomplete="off" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="手机号" v-if="userinfo.phone">
                        <el-input size="small" v-model="userinfo.phone" autocomplete="off" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="分组">
                        <el-input size="small" v-model="userinfo.gname" autocomplete="off" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="姓名">
                        <el-input size="small" v-model="userinfo.realname" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="昵称">
                        <el-input size="small" v-model="userinfo.nickname" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="头像">
                        <Upload v-model:file-list="headimgurlList"></Upload>
                    </el-form-item>
                    <el-form-item label="当前二级密码">
                        <el-input size="small" v-model="userinfo.password2_ck_flag" type="password" autocomplete="off"
                            placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="" style="padding-top: 10px;">
                        <el-button type="primary" @click="onSubmit">提交保存</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
        <div style="height: 50px;"></div>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, reactive, toRefs } from 'vue'
import http from "../../global/network/http"
import { _alert } from "../../global/common"
import md5 from "md5"
import Upload from '../../components/Upload.vue'
import { useStore } from "vuex";
import { flushUserinfo } from '../../global/user'

const store = useStore()
let isRequest = false
const headimgurlList = ref<any[]>([])

const configForm = ref({
    title: '',
    width: '420px',
    labelWidth: '100px',
})

const dataForm = reactive({
    userinfo: {
        password2_ck: '',
        password2_ck_flag: '',
        headimgurl: ''
    }
})

const { userinfo } = toRefs(dataForm)

const onSubmit = () => {
    if (headimgurlList.value[0]) {
        dataForm.userinfo.headimgurl = headimgurlList.value[0].src
    } else {
        _alert('请上传头像')
        return
    }
    if (!dataForm.userinfo.password2_ck_flag) {
        _alert('请填写当前二级密码')
        return
    }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    dataForm.userinfo.password2_ck = md5(dataForm.userinfo.password2_ck_flag)
    http({
        url: 'c=Sys&a=profile_update',
        data: dataForm.userinfo
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false;
            _alert(res.msg)
            return
        }
        dataForm.userinfo.password2_ck = ''
        dataForm.userinfo.password2_ck_flag = ''
        //更新用户信息
        flushUserinfo()
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


onMounted(() => {
    http({
        url: 'c=Sys&a=profile',
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        Object.assign(dataForm.userinfo, res.data.user)
        headimgurlList.value = [{ src: dataForm.userinfo.headimgurl }]
        setTimeout(() => { store.dispatch('loadingFinish'); }, store.state.loadingTime)
    })
})
</script>