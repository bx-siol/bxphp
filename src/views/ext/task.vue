<template>
    <div class="taskCer">
        <MyNav>
        </MyNav>
        <div class="taskCer_wrap">
            <div style="color: #3d3d3d !important;padding: 0 4%;font-size: 0.9rem;" v-if="task.id > 0">
                <div style="margin-top: 0.5rem;padding: 0.5rem;">Earn: <span
                        style="padding: 0.5rem;width: 350px;padding-right: 10rem;border-radius: 0.5rem;">{{
                            task.name
                        }}</span> </div>
                <div style="margin-top: 0.5rem;padding: 0.5rem;">Task Bonuses: <span
                        style="padding: 0.5rem;width: 350px;padding-right: 7.5rem;border-radius: 0.5rem;">{{
                            task.award
                        }}</span></div>
                <div style="margin-top: 0.5rem;padding: 0.5rem;">Deadline: <span
                        style="padding: 0.5rem;width: 250px;padding-right: 1rem;border-radius: 0.5rem;">{{
                            task.end_time
                        }}</span>

                    <span v-if="taskshow" @click="onTask"
                        style="cursor: pointer;text-align: center;padding: 0.5rem;color: #fff;background: #bd312d;border-radius: 0.5rem;margin: 1rem; ">Submit</span>
                </div>
                <div id="van-tabs-7-0" role="tab" class="van-tab van-tab--line van-tab--active" tabindex="0"
                    aria-selected="true" aria-controls="van-tab-8">
                    <span
                        style="width: 150px;text-align: center;padding: 0.5rem;color: #fff;background: #bd312d;border-radius: 0.5rem;margin: 1rem;"
                        class="van-tab__text van-tab__text--ellipsis">Task Details </span>
                </div>
                <div v-html="task.content"></div>
            </div>

            <!-- <MyListBase v-if="false" :url="pageUrl" ref="pageRef" @success="onPageSuccess">
                <template #default="{ list }">
                    <ul class="taskCer_list">
                        <li v-for="item in list">
                            <p class="tit">{{ item.task_name }}</p>
                            <div class="picbox">
                                <van-image :src="imgFlag(vo)" @click="imgPreview(vo)" v-for="vo in item.voucher">
                                </van-image>
                            </div>
                            <div v-if="item.check_remark && item.status > 2">{{ item.check_remark }}</div>
                            <div class="bot">
                                <p class="date">{{ item.create_time }}</p>
                                <p class="state">
                                    {{ lang(item.status_flag) }}
                                    <template v-if="item.status == 3">
                                        <van-button @click="onResubmit(item)" type="warning" size="mini"
                                            style="position: relative;top:-0.28rem;">Resubmit</van-button>
                                    </template>
                                </p>
                            </div>
                        </li>
                    </ul>
                </template>
            </MyListBase> -->
        </div>
    </div>

    <!--pop-->
    <MyPop v-model:show="popShow" :title="t('任务提交')"
        :wrapper-style="{ background: '#fff', marginTop: '6rem', height: '53%', width: '80%' }"
        :title-style="{ borderBottom: 0, color: '#606266' }" :content-style="{ padding: '0' }" class="newTaskPopup">
        <div class="wrap">
            <div class="row">
                <p class="labeltxt">{{ t('备注') }}</p>
                <van-field type="textarea" v-model="dataForm.remark"></van-field>
            </div>
            <div class="row">
                <p class="labeltxt">{{ t('凭证') }}</p>
                <MyUpload v-model:file-list="coverList" :limit="3" width="4rem" height="4rem">
                    <template #delete>&nbsp;</template>
                    <template #notice></template>
                </MyUpload>
            </div>
            <van-button class="submitBtn" @click="onSubmit">{{ t('提交') }}</van-button>
        </div>
    </MyPop>

    <!-- <MyLoading :show="loadingShow" title="Loading..."></MyLoading> -->
</template>

<script lang="ts">
import { defineComponent, ref } from "vue";
import MyNav from "../../components/Nav.vue";
import MyPop from "../../components/Pop.vue";
import MyUpload from '../../components/Upload.vue'
import MyLoading from '../../components/Loading.vue';
import MyListBase from '../../components/ListBase.vue';
import { Button, Field, Image, Popup, Tab, Tabs } from "vant";

export default defineComponent({
    name: "taskCer",
    components: {
        MyNav, MyPop, MyUpload, MyListBase,
        [Image.name]: Image,
        [Field.name]: Field,
        [Button.name]: Button,
        [Tab.name]: Tab,
        [Tabs.name]: Tabs
        // [Popup.name]: Popup,
    }
})
</script>
<script lang="ts" setup>

import { onMounted, ref, reactive } from 'vue';
import {
    img_2
} from '../../global/assets';
import http from "../../global/network/http";
import { _alert, getSrcUrl, imgPreview, lang } from "../../global/common";
import { useRoute, useRouter } from "vue-router";
import { useI18n } from 'vue-i18n'; 
const { t } = useI18n();
// lang('xxx')

const route = useRoute()
const router = useRouter()
const popShow = ref(false)
const coverList = ref<any>([])

const task = ref({})
const taskshow = ref(true)
const loadingShow = ref(true)
const pageRef = ref()
let pageUrl = ref('c=Ext&a=tasklog')
const tableData = ref<any>({})
const pdata = reactive({})

const onPageSuccess = (res: any) => {
    tableData.value = res.data
    loadingShow.value = false
}

const dataForm = reactive({
    tasklog_id: 0,
    remark: '',
    voucher: []
})

const onTask = () => {
    dataForm.tasklog_id = 0
    dataForm.remark = ''
    dataForm.voucher = []
    coverList.value = []
    popShow.value = true
}

const onResubmit = (item: any) => {
    dataForm.tasklog_id = item.id
    dataForm.remark = item.remark
    dataForm.voucher = []
    coverList.value = []
    for (let i in item.voucher) {
        dataForm.voucher.push(item.voucher[i])
        coverList.value.push({ src: item.voucher[i] })
    }
    popShow.value = true
}

const onSubmit = () => {
    dataForm.voucher = []
    for (let i in coverList.value) {
        dataForm.voucher.push(coverList.value[i].src)
    }
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => { 
    http({
        url: 'c=Ext&a=submitTask',
        data: {
            tasklog_id: dataForm.tasklog_id,
            id: task.value.id,
            remark: dataForm.remark,
            voucher: dataForm.voucher
        }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        _alert({
            type: 'success',
            message: res.msg,
            onClose: () => {
                router.go(0)
            }
        })
    })
    }, delayTime)
}

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

onMounted(() => {
    http({
        url: 'c=Ext&a=gettask&id=' + route.params.id
    }).then((res: any) => {
        if (res.code != 1) {
            return
        }
        task.value = res.data.list
        if (task.value.id == 8) {
            taskshow.value = false;
        }
    })
})

</script>

<style>
textarea {
    color: black !important;
}

.newTaskPopup .van-field__control {
    color: white;
}

.wrapper {
    /* background: #2c5c65; */
}
</style>