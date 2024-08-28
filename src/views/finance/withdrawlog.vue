<template>
    <div class="cashlogBox">
        <MyNav></MyNav>
        <div class="tablebox">
            <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
                <template #default="{ list }">

                    <div v-for="item in list">
                        <div class="goods">
                            <ul class="ul_top">
                                <li>{{ t('订单号') }}: {{ item.osn }}</li>
                                <li>{{ item.money }} Rs</li>
                            </ul>
                            <ul class="ul_bot">
                                <li>{{ item.create_time }}</li>
                                <li @click="onGoPayinfo(item)">{{ t(item.status_flag) }}</li>
                            </ul>
                        </div>
                    </div>

                </template>
            </MyListBase>
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { _alert, lang, getSrcUrl, goRoute } from "../../global/common";
import { defineComponent, onMounted, reactive, ref } from 'vue';
import MyNav from '../../components/Nav.vue';
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';

export default defineComponent({
    components: {
        MyNav, MyListBase,
        [Image.name]: Image,
    }
})
</script>
<script lang="ts" setup>
import { useI18n } from 'vue-i18n';
const { t } = useI18n();
let isRequest = false

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const loadingShow = ref(true)
const pageRef = ref()
let pageUrl = ref('c=Finance&a=cashlog')
const tableData = ref<any>({})
const pdata = reactive({})

const onPageSuccess = (res: any) => {
    tableData.value = res.data
    loadingShow.value = false
}

const onGoPayinfo = (item: any) => {
    goRoute({
        name: 'Finance_order2',
        params: {
            osn: item.osn,
            money: item.money,
            par1: item.real_money,
            par2: item.create_time,
            par3: item.receive_bank_name,
            par4: item.receive_account,
            par5: item.receive_realname,
            par6: item.receive_ifsc,
            par7: t(item.status_flag),

        }
    })
}

onMounted(() => {

})
</script>

<style lang="scss" scoped>
.tablebox {
    padding: 1rem 1rem 0;
}

.goods {
    margin-bottom: 1rem;
    border-bottom: 1px solid #ddd;

    ul {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #6e523e;
        line-height: 22px;
    }

    .ul_bot {
        margin-bottom: 0.5rem;
        font-size: 14px;

        li:nth-child(1) {
            color: #000;
        }

        li:nth-child(2) {
            background-color: #6e523e;
            padding: 0.1rem 0.8rem;
            border-radius: 1rem;
            color: #fff;
            // width: 3rem;
            text-align: center;
        }
    }

}
</style>
