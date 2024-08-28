<template>
    <div class="cashlogBox">
        <MyNav>
            <template #right>
                <router-link :to="{ name: 'yeblog' }">
                    <span style="color: #ffffff;">{{ 'Revenue record' }}</span>
                </router-link>
            </template>
        </MyNav>
        <div class="tablebox">
            <div style="height: 2rem;line-height: 2rem; border-radius: 1rem 1rem 0 0; 
            background: rgb(228,243,246);color: #bd312d;">
                <img style="width: 14px;display: inline;" :src="yebimg1" /> Fund
                security is guaranteed
            </div>
            <div style="color: rgb(152,160,162);margin-top: 3rem;">Total Amount</div>
            <div style="color: #000;font-size: 2rem;"> {{ data.balance }}</div>

            <div style="width:100%;height: 7rem;margin-top: 2rem;">
                <div class="div3">
                    <div style="color: rgb(152,160,162);"> Earnings yesterday</div>
                    <div style="color: #bd312d;margin-top: 1rem;">{{ data.yesterday }}</div>
                </div>

                <div class="div3">
                    <div style="color: rgb(152,160,162);"> Cumulative income</div>
                    <div style="color: #000;margin-top: 1rem;">{{ data.all }}</div>
                </div>

                <div class="div3">
                    <div style="color: rgb(152,160,162);"> Annual return(%)</div>
                    <div style="color: #000;margin-top: 1rem;">{{ data.ral }}</div>
                </div>
            </div>

            <div style="height: 6rem;">
                <div style="color:#000;">Transfer amount</div>
                <div>
                    <van-field input-align="center" type="number" class="fieldbox" v-model="data.ye"
                        :placeholder="t('请输入金额')" style="font-size: 1.2rem;text-align:center;"></van-field>
                </div>
            </div>
            <div>
                <div class="btndiv" @click="onSubmitout"
                    style="color: #bd312d;background: linear-gradient( #fff, #bd312d);width: 6rem;padding: 1rem;border-radius: 1rem;margin-right: 2rem;">
                    Transfer Out</div>
                <div class="btndiv" @click="onSubmitin"
                    style="color: #fff;background:#bd312d;width: 6rem;padding: 1rem; border-radius: 1rem;">
                    Transfer In</div>

            </div>
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { _alert, lang } from "../../global/common";
import { defineComponent, onMounted, reactive, ref } from 'vue';
import { Field, CellGroup, Cell, Button, Icon, RadioGroup, Radio, Image } from 'vant';
import MyNav from '../../components/Nav.vue';
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';
import http from "../../global/network/http";
export default defineComponent({
    components: {
        MyNav, MyListBase,
        Image,
        [Image.name]: Image,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [RadioGroup.name]: RadioGroup,
        [Radio.name]: Radio,
        [Icon.name]: Icon,
        [Button.name]: Button
    }
})

</script>
<script lang="ts" setup>
import { getSrcUrl } from "../../global/common";
import { useRoute } from "vue-router";

import { yebimg1 } from '../../global/assets';
import { useI18n } from 'vue-i18n'; 
const { t } = useI18n();

let isRequest = false
const route = useRoute();

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

// const loadingShow = ref(true)
const loadingShow = ref(false)
const data = ref<any>({
    balance: 0,
    all: 0,
    ral: '7.5%',
    yesterday: 0,
    ye: 0
});
onMounted(() => {
    http({
        url: 'c=Wallet&a=getyeb'
    }).then((res: any) => {
        loadingShow.value = false
        if (res.code != 1) {
            return
        }
        data.value.balance = res.data.balance;
        data.value.ral = res.data.ral;
        data.value.all = res.data.all;
        data.value.yesterday = res.data.yesterday;
    })
})

const onSubmitout = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    if (data.value.ye <= 0)
        _alert("Please enter the amount")
    loadingShow.value = true
    http({
        url: 'c=Wallet&a=receiveyebout',
        data: {
            ye: data.value.ye,
        }
    }).then((res: any) => {
        loadingShow.value = false
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                location.reload();
            }
        })
    })
}

const onSubmitin = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    if (data.value.ye <= 0)
        _alert("Please enter the amount")

    loadingShow.value = true
    http({
        url: 'c=Wallet&a=receiveyebin',
        data: {
            ye: data.value.ye,
        }
    }).then((res: any) => {
        loadingShow.value = false
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                location.reload();
            }
        })
    })
}


</script>
<style scoped>
.fieldbox {
    background: transparent;
    border: 1px solid #7c7c7c;
    padding: 0.4rem 0.5rem;
    margin-top: 0.5rem;
    border-radius: 0.3rem;
}

.div3 {
    width: 33%;
    height: 100%;
    float: left;
}

.btndiv {
    width: 50%;
    height: 100%;
    display: inline;
    margin: 0 auto;
    /* float: left; */
}

.cashlogBox {
    height: 100vh;
    background-color: #bd312d;
}

.tablebox {
    text-align: center;
    border-radius: 1rem;
    background: #fff;
    height: calc(100vh - 70px);
    margin: 1rem 3% 0.5rem;
    padding: 0;

}
</style>
