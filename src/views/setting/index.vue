<template>
    <div class="conBox">
        <Nav></Nav>
        <div style="height: 1rem;clear: both;"></div>
        <van-cell-group>
            <van-cell style="text-align: left;" to="/setting/uinfo" is-link>
                <template #title>
                    <span style="vertical-align: sub;">{{ t('个人资料') }}</span>
                </template>
                <template #value>
                    <van-image style="vertical-align: middle;" width="30" height="30"
                        :src="imgFlag(dataForm.headimgurl)" />
                </template>
            </van-cell>
            <van-cell style="text-align: left;" :title="t('修改登录密码')" :to="{ name: 'Setting_password' }" is-link />
            <van-cell style="text-align: left;" :title="t('修改支付密码')" :to="{ name: 'Setting_password2' }" is-link />
            <van-cell style="text-align: left;" :title="t('语言')" is-link @click="onLanguageChoose">
                <template #value>
                    <p class="lang">{{ store.state.config.language_name }}</p>
                </template>
            </van-cell>
            <!--            <van-cell title="Google Authenticator" :to="{name:'Setting_google'}" is-link />-->
            <!--            <van-cell title="实名认证" to="/setting/auth" is-link />-->
            <van-cell style="text-align: left;" :title="t('绑定银行卡')" :to="{ name: 'Setting_bank' }" is-link />
        </van-cell-group>
        <div style="padding: 2rem;">
            <van-button class="myBtn" type="primary" @click="onLogout" block round>{{ t('退出') }}</van-button>
        </div>
    </div>

    <MyLanguage ref="languageRef" :show-act="false"></MyLanguage>
</template>

<script lang="ts">
import { defineComponent, ref,onMounted,reactive } from 'vue';
import { CellGroup, Cell, Button, Image, Dialog } from 'vant';
import Nav from '../../components/Nav.vue';
import MyLanguage from '../../components/Language.vue';

export default defineComponent({
    components: {
        Nav, MyLanguage,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [Image.name]: Image,
        [Button.name]: Button
    }
})
</script>

<script lang="ts" setup>
import { useStore } from "vuex";
import http from "../../global/network/http";
import { _alert, getSrcUrl, lang } from "../../global/common";
import { doLogout } from "../../global/user";
import { useI18n } from 'vue-i18n'; 
const { t } = useI18n();

const store = useStore()

const dataForm = reactive({
    headimgurl: '',
})

const languageRef = ref()
const onLanguageChoose = () => {
    languageRef.value.choose()
}

const onLogout = () => {
    Dialog.confirm({
        message: t('确定要退出么'),
        confirmButtonText: t('确定'),
        cancelButtonText: t('取消'),
        width: '280px',
        allowHtml: true,
        beforeClose: (action: string): Promise<boolean> | boolean => {
            if (action != 'confirm') {
                return true
            }
            return new Promise((resolve) => {
                http({
                    url: 'a=logout'
                }).then((res: any) => {
                    if (res.code != 1) {
                        _alert(res.msg)
                        return
                    }
                    resolve(true);
                    doLogout()
                    _alert({
                        type: 'success',
                        message: res.msg,
                        onClose: () => { }
                    })
                })
            })
        }
    }).catch(() => { })
}

const imgFlag = (src: string) => {
    return getSrcUrl(src, 0)
}

onMounted(() => {
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=User&a=UserInfo'
        }).then((res: any) => {
            dataForm.headimgurl = res.data.headimgurl
        })
    }, delayTime)
})
</script>
<style scoped>
.van-cell__right-icon {
    color: #000
}
.conBox{
    padding: 0 1rem;
    height: 100%;
    background-color: #84973b;
}

.conBox .van-cell {
    color: #fff;
    border: 1px solid #fff;
    border-radius: 6px;
    margin: 1rem 0;
}
</style>