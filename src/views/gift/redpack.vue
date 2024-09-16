<template>
    <div style="min-height: 480px;" class="giftbag">
        <MyNav leftText=''></MyNav>
        <div class="reward">
            <van-image style="margin-bottom: 32vh; width: 66%; " :src="giftTitle"></van-image>
            <van-form @submit="onSubmit">
                <van-cell-group inset>
                    <van-field style="font-weight: normal; color: #000; border: 2px solid #fc5014; border-radius: 5px;" v-model="dataForm.rsn" label-width="5rem"
                               :placeholder="t('请输入兑换码')" />
                </van-cell-group>
                <div class="reward-btn">
                    <van-button round block type="primary" native-type="submit" class="receiveBtn">
                        {{ t('收到') }}
                    </van-button>
                </div>
                <div style="color: rgb(255, 90, 45); font-size: 0.7rem; padding: 2rem; font-weight: bold;">
                    Copy the bonus code and send it to your friends,and get rewards after success.
                </div>
            </van-form>
        </div>

    </div>
</template>
<script lang="ts">
    import { _alert, lang } from "../../global/common";
    import { defineComponent, ref, reactive, onMounted } from 'vue';
    import { Image, Button, Form, Field, CellGroup } from 'vant';
    import MyNav from "../../components/Nav.vue";
    import giftTitle from '/src/assets/img/giftTitle.png'
    // import Giftbag from '../../assets/img/lottery/giftbag.png';


    export default defineComponent({
        components: {
            MyNav,
            [Image.name]: Image,
            [Button.name]: Button,
            [Form.name]: Form,
            [Field.name]: Field,
            [CellGroup.name]: CellGroup,

        },
    })

</script>

<script lang="ts" setup>

    import { useRoute } from "vue-router";
    import http from "../../global/network/http";
    import { useI18n } from 'vue-i18n'; const { t } = useI18n();


    let isRequest = false
    const route = useRoute()

    const dataForm = reactive({
        rsn: ''
    })

    const onSubmit = () => {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        const delayTime = Math.floor(Math.random() * 1000);
        setTimeout(() => {
            http({
                url: 'c=Gift&a=redpackAct',
                data: { rsn: dataForm.rsn }
            }).then((res: any) => {
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

    .receiveBtn {
        background: no-repeat url(../../assets/img/receiveBtn.jpg);
        background-size: 100% 100%;
        height: 2.5rem;
        border: none;
        font-size: 1.2rem;
        border-radius: 2px;
        width: 60%;
        margin: 0px auto;
        color: white;
    }
#app>div>form>div.van-cell-group.van-cell-group--inset>div {}

    .giftbag {
        background: no-repeat url(../../assets/img/giftBG.png);
        background-size: 100% 100%;
        height: 100%;
        overflow: hidden;
    }

    .reward {
        margin: auto auto;
        font: bold 22px/18px "微软雅黑";
        color: white;
        max-width: 640px;
        text-align: center;
        margin-top: 1rem;

        form {
            background: no-repeat url(/src/assets/img/GiftRPBG.jpg);
            background-size: 100% 100%;
            text-align: center;
            margin: auto;
            padding: 1.5rem;
        }

        .exchange {
            margin: 1.5rem auto 0.5rem;
            color: #222;
        }

        :deep .van-cell-group--inset {
            width: 80%;
            margin: auto;
            border-radius: 4px;
            margin-bottom: 3vh;
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