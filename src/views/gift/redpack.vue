<template>
    <div style="min-height: 480px;" class="giftbag">
        <MyNav></MyNav>
        <div class="reward">
            <van-image style="margin-bottom: 1rem;" :src="giftTitle"></van-image>
            <van-form @submit="onSubmit">
                <div style="color: #ff5a2d; font-weight: 500; font-size: 1rem; padding: 0.8rem 2rem; line-height: 1.5rem; ">
                    Copy the bonus code and send it to your friends,and get rewards after success.
                </div>
                <van-cell-group inset>
                    <van-field style="font-weight: normal;color: #000;" v-model="dataForm.rsn" label-width="5rem"
                               :placeholder="t('请输入兑换码')" />
                </van-cell-group>

                <div class="reward-btn">
                    <van-button round block type="primary" native-type="submit" style="height: 2.5rem; border: none; font-size: 1.2rem; background: rgb(255 206 153); border-radius: 2px; width: 80%; margin: 0px auto; color: #932225;">
                        {{ t('收到') }}
                    </van-button>
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

#app>div>form>div.van-cell-group.van-cell-group--inset>div {}

    .giftbag {
        background: no-repeat url(../../assets/img/giftBG.png);
        background-size: 100% 100%;
        height: 100%;
        overflow: hidden;
    }

    .reward {
        width: 92%;
        height: 15rem;
        margin: auto auto;
        font: bold 22px/18px "微软雅黑";
        border-radius: 15px;
        color: white;
        background-repeat: no-repeat;
        background-size: 100%;
        max-width: 640px;
        text-align: center;
        margin-top: 5rem;

        form {
            background: no-repeat url(/src/assets/img/GiftRPBG.png);
            background-size: 100% 100%;
            height: 20rem;
            width: 19rem;
            text-align: center;
            margin: auto;
        }

        .exchange {
            margin: 1.5rem auto 0.5rem;
            color: #222;
        }

        :deep .van-cell-group--inset {
            width: 80%;
            margin: 4rem auto 2rem;
            border-radius: 4px;
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