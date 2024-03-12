<template>
    <div style="min-height: 640px;" class="giftbag">
        <MyNav></MyNav>
        <div class="giftbag2">
            <img :src="giftbag3" style="width: 20rem;margin: 0 auto;position: absolute;top: 10rem;left: 50%;transform: translate(-50%, -50%);">
            <div class="reward">
                <van-form @submit="onSubmit">
                    <van-cell-group inset>
                        <van-field style="font-weight: normal;color: #000;" v-model="dataForm.rsn" label-width="5rem"
                            :placeholder="t('请输入兑换码')" />
                    </van-cell-group>

                    <div class="reward-btn">
                        <van-button round block type="primary" native-type="submit" style="height: 2.5rem;border: none;font-size: 1.2rem;background: #fdda8c;
                        border-radius: 30px;width: 76%;margin: 0 auto; color: #cb0d0a;">{{ t('收到') }}
                        </van-button>
                    </div>
                    <div style="color: #fdda8c;margin-top: 1.2rem;font-size: 12px;padding: 0 2rem;text-align: center;" v-if="false">
                        Enter the red envelope code to claim relevant rewards
                    </div>
                </van-form>
            </div>
        </div>


    </div>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>
<script lang="ts">
import { _alert, lang } from "../../global/common";
import { defineComponent, ref, reactive, onMounted } from 'vue';
import { Button, Form, Field, CellGroup } from 'vant';
import MyNav from "../../components/Nav.vue";
import MyLoading from "../../components/Loading.vue";
// import Giftbag from '../../assets/img/lottery/giftbag.png';


export default defineComponent({
    components: {
        MyNav,MyLoading,
        [Button.name]: Button,
        [Form.name]: Form,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,

    },
})

</script>

<script lang="ts" setup>
import giftbag3 from '../../assets/img/giftCode-bg3.png'
import { useRoute } from "vue-router";
import http from "../../global/network/http";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();


let isRequest = false
const route = useRoute()
const loadtitle = ref("Loading...")
const loadingShow = ref(false);
const dataForm = reactive({
    rsn: ''
})

const onSubmit = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    loadingShow.value = true;
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
    http({
        url: 'c=Gift&a=redpackAct',
        data: { rsn: dataForm.rsn }
    }).then((res: any) => {
        loadingShow.value = false;
        if (res.code != 1) {
            _alert(res.msg)
            isRequest = false
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                dataForm.rsn = ''
                isRequest = false
            }
        })
    })
    }, delayTime)
}

onMounted(() => {

})


</script>
<style lang="scss" scoped>
.van-field__control,
.van-field__label,
label {
    color: rgb(0, 0, 0) !important;
}

#app>div>form>div.van-cell-group.van-cell-group--inset>div {}

.giftbag {
    background: no-repeat url(../../assets/img/giftCode-bg.png)0 2rem;
    background-size: 100% 48rem;
    background-color: #ffe9d4;
    height: 100%;
    overflow: hidden;
    position: relative;

    :deep(.van-nav-bar) {
        background-color: transparent;
        color: #84973b !important;
    }
    :deep(.van-nav-bar__left) {
        .alter {
            color: #84973b !important;
        }
    }

    :deep(.van-nav-bar__title) {
        .alter {
            color: #84973b !important;
        }
    }
}
.giftbag2{
    background: no-repeat url(../../assets/img/giftCode-bg2.png)center 12rem;
    background-size: 80% 26rem;
    height: 100%;
}
.reward {
    width: 92%;
    height: 18rem;
    position: relative;
    top: 35rem;
    left: 50%;
    transform: translate(-50%, -50%);
    font: bold 22px/18px "微软雅黑";
    border-radius: 15px;
    color: white;
    display: flex;
    flex-direction: column;
    background-repeat: no-repeat;
    background-size: 100%;

    .exchange {
        margin: 1.5rem auto 0.5rem;
        color: #222;
    }

    :deep .van-cell-group--inset {
        width: 76%;
        margin: 1rem auto 1.2rem;
        border: 1px solid #222;
        border-radius: 16px;
    }

    :deep .van-field__control {
        color: #222;
        text-align: left;
    }

    :deep(.van-field__control::-webkit-input-placeholder) {
        color: #999 !important; //placeholder颜色修改
    }
}
</style>