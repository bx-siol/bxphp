<template>
    <div class="paylogBox">
        <Nav></Nav>
        <div class="tablebox">
            <MyListBase :url="pageUrl" ref="pageRef"  @success="onPageSuccess">
                <template #default="{list}">
                    <table cellspacing="0" cellpadding="0" style="margin-bottom: 1rem;">
                        <thead>
                            <tr>
                                <th>{{t('时间')}}/{{t('订单号')}}</th>
                                <th>{{t('金额')}}</th>
                                <th>{{t('状态')}}</th>
                                <th>{{t('详情')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="item in list">
                                <td>{{item.create_time}}<br>{{item.osn}}</td>
                                <td>{{item.money}}</td>
                                <td>{{t(item.status_flag)}}</td>
                                <td>
                                    <van-button size="mini" type="warning" @click="onGoPayinfo(item)">{{t('详情')}}</van-button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </template>
            </MyListBase>
        </div>
    </div>

    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
    import {_alert, lang} from "../../global/common";
    import {defineComponent, onMounted, reactive ,ref} from 'vue';
    import {Image,Button} from 'vant';
    import Nav from '../../components/Nav.vue';
    import MyListBase from '../../components/ListBase.vue';
    import MyLoading from '../../components/Loading.vue';

    export default defineComponent({
        components:{
            Nav,MyListBase,
            [Image.name]:Image,
            [Button.name]:Button,
        }
    })
</script>
<script lang="ts" setup>
    import {getSrcUrl, goRoute, lang} from "../../global/common";

    import { useI18n } from 'vue-i18n'; 
const { t } = useI18n();
    let isRequest=false

    const imgFlag=(src:string)=>{
        return getSrcUrl(src, 1)
    }

    const loadingShow=ref(true)
    const pageRef=ref()
    let pageUrl=ref('c=Finance&a=paylog')
    const tableData=ref<any>({})
    const pdata=reactive({})

    const onPageSuccess=(res:any)=>{
        tableData.value=res.data
        loadingShow.value=false
    }

    const onGoPayinfo=(item:any)=>{
        goRoute({name:'Finance_payinfo',query:{osn:item.osn}})
    }

    onMounted(()=>{

    })

</script>