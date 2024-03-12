<template>
    <el-row type="flex" class="row-bg" justify="center">
        <el-col :span="box_span">
            <div style="text-align: center;font-size: 1.5rem;font-weight: 400;">Tlog</div>
            <div class="line-sp"></div>
            <el-input v-model="form.account" placeholder="账号" prefix-icon="el-icon-user" style="display: none;"></el-input>
            <div class="line-sp"></div>
            <el-input v-model="form.password_flag" type="password" placeholder="口令密码" prefix-icon="el-icon-lock"></el-input>
            <!-- <div class="line-sp"></div>
            <el-input v-model="form.gcode" placeholder="谷歌验证码" prefix-icon="el-icon-timer"></el-input>
             -->
            <div class="line-sp"></div>
            <el-input v-model="form.vcode" placeholder="图形验证码" prefix-icon="el-icon-picture-outline-round"
                style="width: 69%;" @keyup.enter="onLogin"></el-input>
            <el-image
                style="width: 90px; height: 41px;position: relative;top:-1px;vertical-align: middle;float: right;cursor: pointer;"
                :src="vcodeUrl" @click="getVcode">
                <template #error>
                    <div class="image-slot">
                        <i class="el-icon-picture-outline"></i>
                    </div>
                </template>
            </el-image>
            <div class="line-sp"></div>
            <div style="text-align: center;">
                <el-button type="primary" @click="onLogin" style="width: 100%;">提交</el-button>
            </div>
        </el-col>
    </el-row>
</template>

<script lang="ts" setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import md5 from 'md5';
import http from '../../global/network/http';
import { _alert } from "../../global/common";
import { doLogin, getUserinfo } from '../../global/user';
import { useStore } from "vuex";

const emit = defineEmits(['loadingClose'])
const router = useRouter()
const store = useStore()

let isRequest = false

const getSpanNum = (): number => {
    let win_w = window.innerWidth
    let perSpan = win_w / 24
    let box_w = 0
    if (win_w <= 375) {
        box_w = 320
    } else if (win_w <= 420) {
        box_w = 320
    } else {
        box_w = 380
    }
    let span_num = Math.floor(box_w / perSpan)
    return span_num
}

let box_span = ref(getSpanNum())

const form = reactive({
    sid: '',
    account: '',
    password: '',
    password_flag: '',
    gcode: '',
    vcode: ''
})

//获取图形验证码
let vcodeUrl = ref('')
const getVcode = () => {
    if (isRequest) {
        return
    }
    isRequest = true
    http({
        url: 'a=getVcode'
    }).then((res: any) => {
        if (res.code == 1) {
            form.sid = res.data.session_id;
            vcodeUrl.value = res.data.url;
        }
    }).finally(() => {
        isRequest = false
    });
}

//提交
const onLogin = () => {
    if (!form.password_flag) {
        _alert('请填写口令密码')
        return
    }
    if (!form.vcode) {
        _alert('请填写图形验证码')
        return
    }
    form.password = md5(form.password_flag)

    if (isRequest) {
        return
    }
    isRequest = true

    http({
        url: 'c=Test&a=tlog',
        data: {
            account: form.account,
            password: form.password,
            sid: form.sid,
            gcode: form.gcode,
            vcode: form.vcode,
        }
    }).then(async (res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            isRequest = false
            getVcode()  //更新图形验证码
            return
        }
        getUserinfo({ token: res.data.token, is_ht: true }).then((res2: any) => {
            doLogin(res2.data, res.data.token)
            if (store.state.config.active.path) {
                router.push({ path: store.state.config.active.path })
            } else {
                router.push({ name: 'Default_index' })
            }
            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => { }
            })
        })
    })
}

onMounted(() => {
    getVcode()  //初始化验证码
    emit('loadingClose');
    window.onresize = () => {
        box_span.value = getSpanNum()
    }
})

</script>

<style>
.row-bg {
    padding-top: 10rem;
}

.line-sp {
    height: 1rem;
    clear: both;
}
</style>