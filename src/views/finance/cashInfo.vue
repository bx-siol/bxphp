<template>
    <div>
        <Nav></Nav>
        <div style="height: 0.5rem;clear: both;"></div>
        <van-config-provider :theme-vars="themeConfig">
            <van-form @submit="onSubmit">
                <van-cell-group>
                    <van-field :label-width="configForm.labelWidth" :label-align="configForm.labelAlign" label="订单">
                        <template #input>
                            {{ info.osn }}
                        </template>
                    </van-field>
                    <van-field :label-width="configForm.labelWidth" :label-align="configForm.labelAlign" v-if="info.money > 0"
                        label="额度">
                        <template #input>
                            {{ info.money }} USDT
                        </template>
                    </van-field>
                    <van-field :label-width="configForm.labelWidth" :label-align="configForm.labelAlign" v-if="info.money > 0"
                        label="实际到账">
                        <template #input>
                            {{ info.real_money }} USDT
                        </template>
                    </van-field>
                    <van-field :label-width="configForm.labelWidth" :label-align="configForm.labelAlign" label="协议">
                        <template #input>
                            {{ info.receive_protocol_flag }}
                        </template>
                    </van-field>
                    <van-field :label-width="configForm.labelWidth" :label-align="configForm.labelAlign" label="接收地址">
                        <template #input>
                            <div style="word-break: break-all;line-height: 1.5rem;">{{ info.receive_address }} <van-button
                                    :data-clipboard-text="info.receive_address" class="copyBtn" type="warning" size="mini"
                                    style="position: relative;top:-5px;display: none;">复制</van-button></div>
                        </template>
                    </van-field>
                    <van-field :label-width="configForm.labelWidth" :label-align="configForm.labelAlign" label="时间">
                        <template #input>
                            {{ info.create_time }}
                        </template>
                    </van-field>
                    <van-field :label-width="configForm.labelWidth" :label-align="configForm.labelAlign" label="状态">
                        <template #input>
                            {{ info.status_flag }}
                        </template>
                    </van-field>
                    <van-field :label-width="configForm.labelWidth" :label-align="configForm.labelAlign"
                        v-if="info.check_remark" label="审核">
                        <template #input>
                            {{ info.check_remark }}
                        </template>
                    </van-field>
                </van-cell-group>
                <div style="height: 2rem;"></div>
            </van-form>
        </van-config-provider>
    </div>
</template>

<script lang="ts">
import { defineComponent, onMounted, reactive, ref } from 'vue'
import { Form, Field, CellGroup, Cell, Button, Icon, Image, Loading, Tag, Dialog, ConfigProvider } from 'vant'
import Nav from '../../components/Nav.vue'
import { useRoute, useRouter } from "vue-router";
import { http } from "../../global/network/http";
import { _alert, getSrcUrl, showImg } from "../../global/common";
import ClipboardJS from "clipboard";

export default defineComponent({
    components: {
        Nav,
        [Form.name]: Form,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [Icon.name]: Icon,
        [Image.name]: Image,
        [Loading.name]: Loading,
        [Dialog.Component.name]: Dialog.Component,
        [Button.name]: Button,
        [Tag.name]: Tag,
        [ConfigProvider.name]: ConfigProvider
    },
    props: ['osn', 'back'],
    setup(props, { emit }) {

        let isRequest = false
        const route = useRoute()
        const router = useRouter()
        const configForm = reactive({
            labelAlign: 'right',
            labelWidth: '3.5rem'
        })

        const themeConfig = {
            cellLineHeight: '1.2rem',
        }

        const info = ref({})
        const dataForm = reactive({
            osn: ''
        })

        onMounted(() => {
            if (route.query.osn) {
                const delayTime = Math.floor(Math.random() * 1000);
                setTimeout(() => {
                    http({
                        url: 'c=Finance&a=cashInfo',
                        data: { osn: route.query.osn }
                    }).then((res: any) => {
                        if (res.code != 1) {
                            _alert(res.msg)
                            return
                        }
                        info.value = res.data.item
                    })
                }, delayTime)

            } else {
                router.push({ name: 'Finance_cash' })
            }

            const clipboard = new ClipboardJS('.copyBtn');
            clipboard.on('success', function (e) {
                _alert('复制成功')
                e.clearSelection();
            });
        })

        const imgFlag = (src: string) => {
            return getSrcUrl(src, 1)
        }

        const imgShow = (src: string) => {
            showImg(getSrcUrl(src))
        }

        return {
            configForm,
            themeConfig,
            dataForm,
            info,
            imgFlag,
            imgShow,
        }
    }
})
</script>

<style scoped>
.rowInfo {
    word-break: break-all;
    width: 100%;
}

.rowInfo button {
    vertical-align: middle;
}
</style>

