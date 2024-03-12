<template>
    <div class="conbar">
        <el-breadcrumb separator="/" style="padding-left:12px;padding-top: 2px;line-height: 40px;display: inline-block;">
            <el-breadcrumb-item>{{ store.state.config.active.name }}</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <div class="conbox">
        <el-row>
            <el-col :span="10">
                <el-form :style="{ width: configForm.width }" :label-width="configForm.labelWidth">
                    <el-form-item label="账号">
                        <el-input v-model="userinfo.account" autocomplete="off" disabled></el-input>
                    </el-form-item>
                    <el-form-item label="手机号">
                        <el-input v-model="userinfo.phone" autocomplete="off"></el-input>
                    </el-form-item>
                    <el-form-item label="新登录密码">
                        <el-input v-model="userinfo.password_flag" type="password" autocomplete="off"
                            placeholder="留空则忽略修改"></el-input>
                    </el-form-item>
                    <el-form-item label="新二级密码">
                        <el-input v-model="userinfo.password2_flag" type="password" autocomplete="off"
                            placeholder="留空则忽略修改"></el-input>
                    </el-form-item>
                    <!-- <el-form-item label="谷歌验证">
                        <el-radio-group v-model="userinfo.is_google">
                            <el-radio :label="1">开启</el-radio>
                            <el-radio :label="0">关闭</el-radio>
                        </el-radio-group>
                    </el-form-item> -->
                    <el-form-item label="允许登录IP">
                        <el-input type="textarea" v-model="userinfo.white_ip" autocomplete="off" rows="3"
                            placeholder="多个ip之间使用逗号“,”相隔，留空则不限制"></el-input>
                    </el-form-item>
                    <el-form-item label="当前二级密码">
                        <el-input v-model="userinfo.password2_ck_flag" type="password" autocomplete="off"
                            placeholder=""></el-input>
                    </el-form-item>
                    <el-form-item label="" style="padding-top: 10px;">
                        <el-button type="primary" @click="onSubmit">提交保存</el-button>
                    </el-form-item>
                </el-form>
            </el-col>
            <el-col :span="14">
                <el-card class="box-card" style="width: 280px;text-align: center;">
                    <template #header>
                        <div class="card-header">
                            <span>Google验证添加</span>
                        </div>
                    </template>
                    <div>
                        <el-image :src="google_qrcode" style="width: 240px;height: 240px;">
                            <template #placeholder>
                                <div class="image-slot" style="line-height: 240px;">
                                    加载中<span class="dot">...</span>
                                </div>
                            </template>
                        </el-image>
                        <div style="padding-top: 10px;">密钥：{{ userinfo.google_secret }}</div>
                    </div>
                </el-card>
                <div style="line-height: 22px;padding-top: 10px;">
                    1、Android移动设备：在手机应用市场搜索“Google身份验证器”或“Google Authenticator”，下载安装。<br>
                    2、iOS移动设备：进入AppStore，搜索“Google Authenticator”，下载安装。<br>
                </div>
            </el-col>
        </el-row>
        <div style="height: 50px;"></div>
    </div>
</template>

<script lang="ts" setup>
import { ref, onMounted, reactive, toRefs } from 'vue'
import { useStore } from "vuex";
import http from "../../global/network/http";
import { _alert } from "../../global/common";
import md5 from "md5";

let isRequest = false

const store = useStore()

const configForm = ref({
    title: '',
    width: '420px',
    labelWidth: '100px',
    visible: false,
    isEdit: false
})

const dataForm = reactive({
    userinfo: {
        password: '',
        password_flag: '',
        password2: '',
        password2_flag: '',
        password2_ck: '',
        password2_ck_flag: ''
    },
    google_qrcode: ''
})

const { userinfo, google_qrcode } = toRefs(dataForm)

const getData = () => {
    http({
        url: 'c=Sys&a=safety',
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        Object.assign(dataForm.userinfo, res.data.user)
        dataForm.google_qrcode = res.data.google_qrcode

        setTimeout(() => { store.dispatch('loadingFinish'); }, store.state.loadingTime)
    })
}

const onSubmit = () => {
    if (!dataForm.userinfo.password2_ck_flag) {
        _alert('请填写当前二级密码')
        return
    }
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    if (dataForm.userinfo.password_flag) {
        dataForm.userinfo.password = md5(dataForm.userinfo.password_flag)
    }
    if (dataForm.userinfo.password2_flag) {
        dataForm.userinfo.password2 = md5(dataForm.userinfo.password2_flag)
    }
    dataForm.userinfo.password2_ck = md5(dataForm.userinfo.password2_ck_flag)
    http({
        url: 'c=Sys&a=safety_update',
        data: dataForm.userinfo
    }).then((res: any) => {
        if (res.code != 1) {
            isRequest = false;
            _alert(res.msg)
            return
        }
        dataForm.userinfo.password2_ck = ''
        dataForm.userinfo.password2_ck_flag = ''
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
    getData()
})

</script>