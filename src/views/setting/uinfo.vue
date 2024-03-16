<template>
    <div class="conBox">
        <Nav></Nav>
        <van-form @submit="onSubmit" :label-width="configForm.labelWidth" :label-align="configForm.labelAlign">
            <van-cell-group>
                <van-field :label="t('账号')" placeholder="">
                    <template #input>
                        <input type="text" class="van-field__control" v-model="user.account" readonly />
                    </template>
                </van-field>
                <van-field :label="t('昵称')" v-model="dataForm.nickname" placeholder="Please enter ther nickname" />
                <van-field :label="t('头像')" placeholder="">
                    <template #input>
                        <van-image style="position: relative;top:-4px;margin-right: 10px;" width="80" height="80"
                            @click="imgPreview(user.headimgurl)" :src="imgFlag(user.headimgurl)" />
                    </template>
                </van-field>
                <van-field :label="t('修改头像')" placeholder="">
                    <template #input>
                        <van-image :src="imgFlag(dataForm.headimgurl)" style="width: 80px;height: 80px" loading-icon="plus"
                            @click="onChoseAvatar" />
                    </template>
                </van-field>
                <van-field :label-width="configForm.labelWidth" :label-align="configForm.labelAlign" :label="t('密码')"
                    v-model="dataForm.password" type="password" :placeholder="t('请输入密码')" />
            </van-cell-group>
            <div style="padding: 2rem;">
                <van-button class="myBtn" round block type="primary" native-type="submit">{{ t('提交') }}</van-button>
            </div>
        </van-form>
    </div>

    <Avatar ref="avatarRef" @success="onSuccessAvatar"></Avatar>
</template>

<script lang="ts">
import { defineComponent } from 'vue';
import { Button, Form, Field, Uploader, CellGroup, Image, Icon, Overlay } from 'vant';
import Nav from "../../components/Nav.vue";
import Avatar from "../../components/Avatar.vue";

export default defineComponent({
    components: {
        Nav, Avatar,
        [Button.name]: Button,
        [Form.name]: Form,
        [CellGroup.name]: CellGroup,
        [Field.name]: Field,
        [Image.name]: Image,
        [Icon.name]: Icon,
        [Overlay.name]: Overlay,
        [Uploader.name]: Uploader,
    }
})
</script>

<script lang="ts" setup>
import { _alert, lang, getSrcUrl, imgPreview } from "../../global/common";
import { ref, reactive, onMounted } from 'vue';
import { useStore } from "vuex";
import { http } from "../../global/network/http";
import md5 from 'md5';
import { flushUserinfo } from "../../global/user";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

let isRequest = false
const avatarRef = ref()
const store = useStore()
const user = ref({});

const configForm = reactive({
    labelAlign: 'right',
    labelWidth: '4.5rem'
})

const dataForm = reactive({
    nickname: store.state.user.nickname,
    headimgurl: '',
    password: ''
})

const onChoseAvatar = () => {
    avatarRef.value.chooseFile()
}

const onSuccessAvatar = (src: string) => {
    dataForm.headimgurl = src
}

const onSubmit = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Setting&a=uinfo_update',
            data: {
                nickname: dataForm.nickname,
                headimgurl: dataForm.headimgurl,
                password2: md5(dataForm.password)
            }
        }).then((res: any) => {
            setTimeout(() => {
                isRequest = false
            }, 2000)
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            dataForm.password = ''
            dataForm.headimgurl = ''
            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => {
                    flushUserinfo()
                }
            })
        })
    }, delayTime)
}

const imgFlag = (src: string) => {
    return getSrcUrl(src, 0)
}

onMounted(() => {
    const delayTime = Math.floor(Math.random() * 1000);
    // setTimeout(() => {
        http({
            url: 'c=User&a=UserInfo'
        }).then((res: any) => {
            user.value.account = res.data.account
            dataForm.nickname = res.data.nickname
            user.value.headimgurl = res.data.headimgurl
        })
    // }, delayTime)
})
</script>