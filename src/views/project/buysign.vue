<template>
    <MyNav>
            <template #left>
                <div></div>
            </template>
    </MyNav>
    <div class="buysign">
        <input id="sign" type="text" v-model="sign" autofocus="autofocus" />        
        <van-button class="touziBtn" @click="onSubmit">{{ t('提交') }}</van-button>

    </div>
    <MyLoading :show="loadingShow" title="Submit"></MyLoading>
</template>
<script lang="ts">
import { defineComponent } from "vue";
import MyLoading from "../../components/Loading.vue";
import MyNav from "../../components/Nav.vue";

export default defineComponent({
    name: "productDet",
    components: {
        MyNav, MyLoading,
    }
})
</script>
<script lang="ts" setup>
import { ref, onMounted, reactive } from "vue";
import { useRoute, useRouter } from "vue-router";
import { _alert, lang, cutOutNum } from "../../global/common";
import http from "../../global/network/http";

import { useI18n } from 'vue-i18n'; const { t } = useI18n();

const route = useRoute()
const router = useRouter()
const osn = ref(route.params.osn)
const sign = ref('')

let isRequest = false
const loadingShow = ref(false);

const onSubmit = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    loadingShow.value = true;

    http({
        url: 'c=Product&a=SigningCcontract',
        data: {
            sign: sign.value,
            osn:osn.value
        }
    }).then((res: any) => {
        loadingShow.value = false;
        if (res.code != 1) {
            isRequest = false
            _alert(res.msg)
            return
        }
        _alert(res.msg, function () {
            router.push({ path: '/' })
        })
    })
}

onMounted(() => {
    document.getElementById('sign').focus();
})

</script>
<style lang="scss" scoped>
.buysign {
    background-image: url(../../assets/img/project/contract.jpg);
    background-repeat: no-repeat;
    background-size: 100% 100%;
    height: calc(100% - 1px);

    input {
        position: absolute;
        bottom: 1px;
        right: 3rem;
        height: 2.5rem;
        width: 7rem;
        font-size: 0.9rem;
        padding: 0.2rem;
    }

    .touziBtn{
        position: absolute;
        bottom: 1rem;
        left:7rem ;
        background-color: #84973b;
        padding: 0.5rem;
        border-radius: 10px;
        color: white;
        font-size: 0.9rem;
    }
}
</style>