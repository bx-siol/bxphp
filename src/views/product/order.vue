<template>
    <div class="paylogBox conBox" style="background-color: #fff;">
        <Nav></Nav>
        <div class="tablebox" style="padding-top: 0.5rem;">
            <div style="padding-bottom: 0.5rem;text-align: center;color: #bd312d;font-weight: bold;">
                {{ t('全部') }}：RS{{ tableData.money }}</div>
            <MyListBase :url="pageUrl" ref="pageRef" @success="onPageSuccess">
                <template #default="{ list }">
                    <van-card :desc="t('描述信息')" :title="t('商品标题')" :thumb="imgFlag(item.icon)"
                        @click-thumb="imgShow(item.icon)" v-for="item in list">
                        <template #title>
                            <div style="font-size: 0.9rem;">
                                <div style="word-break: break-all;">{{ item.goods_name }}</div>
                            </div>
                        </template>
                        <template #num>
                            <template v-if="item.status == 1">
                                <template v-if="item.receive == 1">
                                    <van-button @click="onReceive(item)" size="mini"
                                        style="background-color: #bd312d;border-color: #bd312d;color: white;font-size: 1rem;height: 1.8rem;line-height: 1.8rem;">
                                        &nbsp;{{ t('收到') }}&nbsp;</van-button>
                                </template>
                                <template v-else> 
                                    <van-button @click="onReceiveNo(item)" size="mini"
                                        style="background-color: #999999;border-color: #999999;color: #f2f3f5;font-size: 1rem;height: 1.8rem;line-height: 1.8rem;">
                                        &nbsp;{{ t('收到') }}&nbsp;</van-button>
                                </template>
                            </template>
                            <template v-else>
                                <span
                                    style="font-size: 1rem;height: 1.8rem;line-height: 1.8rem;color: #bd312d;display: inline-block;">{{ t('结束') }}</span>
                            </template>
                        </template>
                        <template #desc>
                            <div style="font-size: 0.9rem;">
                                <div>
                                    <span style="font-weight: bold;color: #bd312d;">RS{{ item.money }}</span>&nbsp;&nbsp;
                                    {{ item.days }}{{ t('天') }}&nbsp;&nbsp;{{ item.rate }}%/{{ t('天') }}
                                </div>
                                <div>Profit: {{ item.total_days }}{{ t('天') }}&nbsp;&nbsp;RS{{ item.total_reward }}</div>
                            </div>
                        </template>
                        <template #tags>
                            <!--                            <van-tag plain type="danger" style="background-color: #0098a2;border-color: #0098a2;color: white;">标签</van-tag>-->
                        </template>
                        <template #price>
                            <span
                                style="color: #ffffff;font-size: 0.9rem;line-height: 1.8rem;height: 1.8rem;display: inline-block;">{{ item.create_time }}</span>
                        </template>
                        <!--                        <template #footer>
                            <van-button size="mini">按钮</van-button>
                            <van-button size="mini">按钮</van-button>
                        </template>-->
                    </van-card>
                </template>
            </MyListBase>
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { defineComponent, onMounted, reactive, ref } from 'vue';
import { Image, Card, Button, Tag } from 'vant';
import Nav from '../../components/Nav.vue';
import MyListBase from '../../components/ListBase.vue';
import MyLoading from '../../components/Loading.vue';

export default defineComponent({
    components: {
        Nav, MyListBase,
        [Image.name]: Image,
        [Card.name]: Card,
        [Button.name]: Button,
        [Tag.name]: Tag,
    }
})
</script>
<script lang="ts" setup>
import { _alert, lang, getSrcUrl, imgPreview } from "../../global/common";
import { onMounted, ref } from "vue";
import http from "../../global/network/http";
import { useI18n } from 'vue-i18n'; 
const { t } = useI18n();
//t('222')
let isRequest = false

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const imgShow = (src: string) => {
    imgPreview(src)
}

const loadingShow = ref(true)
const pageRef = ref()
let pageUrl = ref('c=Product&a=order')
const tableData = ref<any>({})
const pdata = reactive({})

const onPageSuccess = (res: any) => {
    tableData.value = res.data
    loadingShow.value = false
}

const onReceive = (item: any) => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => { 
    http({
        url: 'c=Product&a=receiveProfit',
        data: { osn: item.osn }
    }).then((res: any) => {
        isRequest = false
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                item.receive = 0
            }
        })
    })
    }, delayTime)
}

const onReceiveNo = (item: any) => {
    _alert('Currently unavailable')
}

onMounted(() => {

})

</script>