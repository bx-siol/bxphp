<template>
    <div style="color: white;">
        <MyNav></MyNav>
        <div style="height: 4rem;"></div>
        <van-form @submit="onSubmit">
            <van-cell-group inset>
                <van-field style="background: #ba192f;color: aliceblue;" v-model="dataForm.rsn" :label="t('兑换码')"
                    label-width="3rem" :placeholder="t('请输入兑换码')" />
            </van-cell-group>
            <div style="margin: 16px;">
                <van-button round block type="primary" native-type="submit"
                    style="background: #bd312d;border-color: #bd312d;">
                    {{ t('收到') }}</van-button>
            </div>
        </van-form>
    </div>
</template>

<script lang="ts">
import { _alert, lang } from "../../global/common";

import { defineComponent, ref, reactive, onMounted } from 'vue';
import { Button, Form, Field, CellGroup } from 'vant';
import MyNav from "../../components/Nav.vue";

export default defineComponent({
    components: {
        MyNav,
        [Button.name]: Button,
        [Form.name]: Form,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
    }
})
</script>

<script lang="ts" setup>
import { useRoute } from "vue-router";
import http from "../../global/network/http";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
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
<style>
.van-field__control,
.van-field__label,
label {
    color: aliceblue !important;
}

#app>div>form>div.van-cell-group.van-cell-group--inset>div {}
</style>