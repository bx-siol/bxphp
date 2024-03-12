<template>
    <div class="conBox" style="height: auto;background-color: #fff;">
        <Nav></Nav>
        <!--------------- 提现订单详细信息 ---------------->
        <div class="goods">
            <ul class="ul_top">
                <li>
                    <p>Order Number:</p>
                    <p>{{ route.params.osn }}</p>
                </li>
                <li>
                    <p>Quantity:</p>
                    <p>{{ route.params.money }} Rs</p>
                </li>
                <li>
                    <p>Handling Fees({{ ((route.params.money - route.params.par1) / route.params.money * 100) }}%):
                    </p>
                    <p>{{ route.params.money - route.params.par1 }} Rs</p>
                </li>
                <li>
                    <p>Actual Account: </p>
                    <p>{{ route.params.par1 }} Rs</p>
                </li>
                <li>
                    <p>Time:</p>
                    <p>{{ route.params.par2 }}</p>
                </li>
                <li>
                    <p>Bank:</p>
                    <p>{{ route.params.par3 }}</p>
                </li>
                <li>
                    <p>Bank Account:</p>
                    <div class="flex">
                        <p>{{ route.params.par4 }}</p>
                        <van-button ref="accountRef" type="warning"></van-button>
                    </div>
                </li>
                <li>
                    <p>Real Name:</p>
                    <div class="flex">
                        <p>{{ route.params.par5 }}</p>
                        <van-button ref="nameRef" type="warning"></van-button>
                    </div>
                </li>
                <li>
                    <p>IFSC:</p>
                    <div class="flex">
                        <p>{{ route.params.par6 }}</p>
                        <van-button ref="ifscRef" type="warning"></van-button>
                    </div>
                </li>
                <li>
                    <p>State:</p>
                    <p>{{ t(route.params.par7) }}</p>
                </li>
            </ul>

        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, reactive, computed } from 'vue'
import { Button } from 'vant'

import Nav from '../../components/Nav.vue'
import MyUpload from '../../components/Upload.vue'
import MyListBase from '../../components/ListBase.vue';
export default defineComponent({
    components: {
        Nav, MyUpload, MyListBase,
        [Button.name]: Button,
    }
})
</script>

<script lang="ts" setup>
import { _alert, copy } from "../../global/common";
import { ref, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";
import { http } from "../../global/network/http";
import { useI18n } from 'vue-i18n';
const { t } = useI18n();

const route = useRoute()

const accountRef = ref('')
const nameRef = ref('')
const ifscRef = ref('')

const montage = computed(() => {
    return {
        account: route.params.par4,
        name: route.params.par5,
        ifsc: route.params.par6,

    };
});

nextTick(() => {

    copy(accountRef.value.$el, {
        text: (target: HTMLElement) => {
            return montage.value.account
        }
    })
    copy(nameRef.value.$el, {
        text: (target: HTMLElement) => {
            return montage.value.name
        }
    })
    copy(ifscRef.value.$el, {
        text: (target: HTMLElement) => {
            return montage.value.ifsc
        }
    })
})
</script>


<style scoped>
.conBox {
    padding: 1.2rem;
}

.conBox ul li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    line-height: 28px;
}

.conBox ul li p {
    color: #3c3c3c;
    font-weight: bold;

}

.conBox ul li p:nth-child(1) {
    color: #64523e;
}

.conBox ul li .flex {
    display: flex;
    align-items: center;
}

.conBox ul li :deep(.van-button) {
    padding: 0;
    border: none;
    background: url(../../assets/img/home/copy.png) center 5px;
    background-repeat: no-repeat;
    background-size: 60% 60%;
    height: 2rem;
    font-size: 18px;
    width: 2rem;
}
</style>