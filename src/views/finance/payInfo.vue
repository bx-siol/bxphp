<template>
    <div class="conBox" style="height: auto;background-color: #fff;">
        <Nav></Nav>
        <div style="height: 1rem;clear: both;"></div>
        <van-form @submit="onSubmit" :label-width="configForm.labelWidth" :label-align="configForm.labelAlign"
            style="padding: 0 10px;">

            <van-cell-group style="background:transparent;">
                <van-field>
                    <template #input>
                        <div class="amount">
                            <div style="margin-left: 2rem;">
                                <p>Amount payable </p>
                                <span>INR {{ info.money }}</span>
                            </div>
                        </div>
                    </template>
                </van-field>

                <van-field>
                    <template #input>
                        <div>
                            <div class="flex">
                                <span style="position: absolute;"><img :src="title1" /></span>
                                <p style="text-indent: 2.5rem;t">Copy Bank Account Information</p>
                            </div>
                            <div>


                                <div class="center">
                                    Bank: {{ info.receive_bank_name }}
                                    <van-button ref="bankRef" class="copyBtn" type="warning">{{ t('复制') }}
                                    </van-button>
                                </div>
                                <div class="center">
                                    Name: {{ info.receive_realname }}
                                    <van-button ref="realnameRef" class="copyBtn" type="warning">{{ t('复制') }}
                                    </van-button>
                                </div>
                                <div class="center">
                                    A/C: {{ info.receive_account }}
                                    <van-button ref="accountRef" class="copyBtn" type="warning">{{ t('复制') }}
                                    </van-button>
                                </div>

                                <div class="center">
                                    IFSC: {{ info.receive_ifsc }}
                                    <van-button ref="ifscRef" class="copyBtn" type="warning">{{ t('复制') }}
                                    </van-button>
                                </div>

                                <div v-if="info.receive_upi != ''" class="center">
                                    UPI: {{ info.receive_upi }}
                                    <van-button ref="upiRef" class="copyBtn" type="warning">{{ t('复制') }}
                                    </van-button>
                                </div>

                            </div>
                        </div>
                    </template>
                </van-field>

                <van-field>
                    <template #input>
                        <div>
                            <div class="flex">
                                <span style="position: absolute;"><img :src="title2" /></span>
                                <p style="text-indent: 2.5rem;t">Transfer the amount you want to recharge to us by Bank
                                    Account</p>
                            </div>
                            <div class="bottom">
                                <p style="color:rgb(130,121,119);"><span style="color: rgb(206,27,34);">Tip:</span>Please
                                    record your reference No. Ref No. after payment</p>
                            </div>
                        </div>
                    </template>
                </van-field>

                <van-field>
                    <template #input>
                        <div>
                            <div class="flex">
                                <span style="position: absolute;"><img :src="title3" /></span>
                                <p style="text-indent: 2.5rem;t">Enter ref No. and snbmit</p>
                            </div>
                            <div class="bottom">
                                <label>Ref No</label>
                                <input :disabled="disabledifg" type="text" class="underline-input"
                                    v-model="dataForm.pay_remark" />
                            </div>
                        </div>
                    </template>
                </van-field>

                <van-field>
                    <template #input>

                        <div>
                            <div class="flex">
                                <span style="position: absolute;"><img :src="title4" /></span>
                                <p style="text-indent: 2.5rem;t">{{ t('付款凭证') }}</p>
                            </div>
                            <div class="bottom">
                                <MyUpload :file-list="fileList" :limit="4"></MyUpload>
                            </div>
                        </div>




                    </template>
                </van-field>
            </van-cell-group>

            <template v-if="info.receive_type > 0">
                <div style="margin: 0 2rem;padding-top: 0.8rem;" v-if="info.status == 1 || info.status == 3">
                    <van-button round block type="primary" class="myBtn" native-type="submit">
                        Please Submit Your utr
                    </van-button>
                </div>
            </template>
            <div style="height: 2rem;"></div>
        </van-form>
        <MyTab></MyTab>
    </div>
</template>

<script lang="ts">
import { defineComponent, reactive } from 'vue'
import { Form, Field, CellGroup, Cell, Button, Icon, RadioGroup, Radio, Image, Loading, Uploader, Tag, Dialog, ConfigProvider } from 'vant'
import Nav from '../../components/Nav.vue'
import MyTab from '../../components/Tab.vue'
import MyUpload from '../../components/Upload.vue'
import title1 from '../../assets/index/title1.jpg'
import title2 from '../../assets/index/title2.jpg'
import title3 from '../../assets/index/title3.jpg'
import title4 from '../../assets/index/title4.png'
export default defineComponent({
    components: {
        Nav, MyUpload,
        [Form.name]: Form,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [RadioGroup.name]: RadioGroup,
        [Radio.name]: Radio,
        [Icon.name]: Icon,
        [Image.name]: Image,
        [Uploader.name]: Uploader,
        [Loading.name]: Loading,
        [Dialog.Component.name]: Dialog.Component,
        [Button.name]: Button,
        [Tag.name]: Tag,
        [ConfigProvider.name]: ConfigProvider
    }
})
</script>

<script lang="ts" setup>
import { _alert, lang, copy, getSrcUrl, imgPreview, showImg } from "../../global/common";
import { ref, onMounted, nextTick } from 'vue';
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";
import { http } from "../../global/network/http";
import { useI18n } from 'vue-i18n'; 
const { t } = useI18n();
const addressRef = ref()
const accountRef = ref()

const bankRef = ref()
const realnameRef = ref()
const ifscRef = ref('')
const upiRef = ref('')

let isRequest = false
const route = useRoute()
const router = useRouter()
const store = useStore()

const configForm = reactive({
    labelAlign: 'right',
    labelWidth: '5.5rem'
})

const fileList = ref<any>([])
const info = ref({})

const dataForm = reactive({
    osn: '',
    money: '',
    pay_realname: '',
    pay_remark: '',
})

const onSubmit = () => {
    if (isRequest) {
        return
    }
    if (!dataForm.pay_remark) {
        _alert('Please enter UTR')
        return
    }
    Dialog.confirm({
        title: 'TIPS',
        message: 'Are you sure to submit?',
        confirmButtonText: t('确定'),
        cancelButtonText: t('取消'),
        beforeClose: (action: string): Promise<boolean> | boolean => {
            if (action != 'confirm') {
                return true
            }
            return new Promise((resolve) => {
                if (isRequest) return
                isRequest = true
                let pay_banners: any = []
                for (let i in fileList.value) {
                    pay_banners.push(fileList.value[i].src)
                }
                const delayTime = Math.floor(Math.random() * 1000);
                setTimeout(() => { 
                http({
                    url: 'c=Finance&a=payInfoUpdate',
                    data: {
                        osn: dataForm.osn,
                        money: dataForm.money,
                        pay_remark: dataForm.pay_remark,
                        pay_realname: dataForm.pay_realname,
                        pay_banners: pay_banners
                    }
                }).then((res: any) => {
                    if (res.code != 1) {
                        _alert(res.msg)
                        setTimeout(() => {
                            isRequest = false
                        }, 2000)
                        return
                    }
                    _alert({
                        type: 'success',
                        message: res.msg,
                        onClose: () => {
                            location.reload()
                        }
                    })
                })
                }, delayTime)
            })
        }
    })
}
const disabledifg = ref(false)
onMounted(() => {
    if (!route.query.osn) {
        router.go(-1)
        return
    }

    http({
        url: 'c=Finance&a=payInfo',
        data: { osn: route.query.osn }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        info.value = res.data.item
        dataForm.osn = res.data.item.osn
        dataForm.pay_remark = res.data.item.pay_remark
        if (dataForm.pay_remark) disabledifg.value = true;
        for (let i in res.data.item.pay_banners) {
            fileList.value.push({
                src: res.data.item.pay_banners[i],
                url: getSrcUrl(res.data.item.pay_banners[i])
            })
        }

        nextTick(() => {
            //处理复制
            if (addressRef.value) {
                copy(addressRef.value.$el, {
                    text: (target: HTMLElement) => {
                        return res.data.item.receive_address
                    }
                })
            }

            if (ifscRef.value) {
                copy(ifscRef.value.$el, {
                    text: (target: HTMLElement) => {
                        return res.data.item.receive_ifsc
                    }
                })
            }

            if (upiRef.value) {
                copy(upiRef.value.$el, {
                    text: (target: HTMLElement) => {
                        return res.data.item.receive_upi
                    }
                })
            }

            if (accountRef.value) {
                copy(accountRef.value.$el, {
                    text: (target: HTMLElement) => {
                        return res.data.item.receive_account
                    }
                })
            }
            if (bankRef.value) {
                copy(bankRef.value.$el, {
                    text: (target: HTMLElement) => {
                        return res.data.item.receive_bank_name
                    }
                })
            }
            if (realnameRef.value) {
                copy(realnameRef.value.$el, {
                    text: (target: HTMLElement) => {
                        return res.data.item.receive_realname
                    }
                })
            }
        })

    })

})

const imgFlag = (src: string) => {
    return getSrcUrl(src, 1)
}

const imgShow = (src: string) => {
    imgPreview(src)
}

</script>

<style scoped>
.conBox .amount {
    height: 7.5rem;
    width: 100%;
    margin: 0 auto;
    background-image: url(../../assets/index/headline.jpg);
    background-size: 100% 100%;
    color: #fff;
    font: bold 16px/30px '微软雅黑';
    display: flex;
    flex-direction: column;
    justify-content: center;
}


.conBox .amount span {
    font-size: 24px;
}

.conBox .flex {
    display: flex;
    font: bold 16px/30px '微软雅黑';
}

.conBox .flex img {
    width: 24px;
    height: 24px;
    margin-top: 3px;
}

.conBox .bottom {
    margin-top: 12px;
}

.conBox .copyBtn {
    border: none;
    background: none;
    color: rgb(240, 100, 34);
    position: absolute;
    right: 0;
    padding: 0;
}

.conBox .center {
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: rgb(130, 121, 119);
    font-weight: bold;
    padding: 4px 0;
    margin-top: 8px;
}

.conBox .myBtn {
    width: 80%;
    margin: 8px auto;
    font-weight: bold;
    font-size: 16px;
    border-radius: 5px;
}

.conBox .underline-input {
    border: none;
    border-bottom: 1px solid black;
    outline: none;
    text-indent: 0.6rem;
    width: 86%;
    position: absolute;
    color: rgb(240, 100, 24);
}

.field .van-field__text {
    display: block;
}



.conBox .van-cell::after {
    border: none;
}

.van-hairline--top-bottom:after {
    border: none;
}
</style>

