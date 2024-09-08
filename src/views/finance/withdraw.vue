<template>
    <div class="recharge">
        <MyNav leftText=''></MyNav>
        <div class="recharge_wrap">

            <div style="background-color: #ebf9e8;padding: 1rem">
                <div
                    style="height: 2.5rem;background-color: white;border-radius: 5px;display: flex;color: #009900;align-items: center;justify-content: space-between;padding: 0 1rem;font-weight: bold;box-shadow: 0 0 10px 0 #d1d1d1;">
                    <img :src="icon121" style="height: 1.5rem;width: 1.5rem;position: absolute;border-radius: 5px;">
                    <div style="margin-left: 2.3rem;">Current Balance</div>
                    <div>₹ {{ wallet.balance }}</div>
                </div>
                <div
                    style="margin-top: 1rem; height: 2.5rem;background-color: white;border-radius: 5px;display: flex;color: #009900;align-items: center;justify-content: space-between;font-weight: bold;box-shadow: 0 0 10px 0 #d1d1d1;overflow: hidden;">
                    <van-field v-model="dataForm.money" :placeholder="t('请输入金额')" style="font-weight: 100;">
                        <template #left-icon>
                            <img :src="icon114"
                                style="height: 1.5rem;width: 1.5rem;margin-left: 0;margin-right: 0.6rem;">
                        </template>
                        <template #button>
                            <van-button size="small" type="primary" color="#fff"
                                style="border-radius:4px;color:white;background-color: #009900;padding: 0.3rem;"
                                @click="onClickAll">ALL</van-button>
                        </template>
                    </van-field>
                </div>

                <div class="payinfo">
                    <ul>
                        <li><span>{{ dataForm.money * tar / 100 }}RS</span>{{ t('税收') }}</li>
                        <li><span>{{ tar }}%</span>{{ t('费用比率') }}</li>
                        <li><span>{{ min }} RS</span>{{ t('最小提现金额') }}</li>
                        <li><span>{{ max }} RS</span>{{ t('最大提现金额') }}</li>
                    </ul>
                </div>

                <van-field class="fieldbox" v-model="password2" :placeholder="t('请输入提现密码')"
                    :type="showPassword ? 'text' : 'password'"
                    style="height: 1.75rem;font-size: 0.75rem;background-color: white;border: none;box-shadow: 0 0 10px 0 #d1d1d1;">
                    <template #right-icon>
                        <van-icon v-if="showPassword" name="eye-o" color="#d6d6d6"
                            @click="showPassword = false"></van-icon>
                        <van-icon v-else name="closed-eye" color="#d6d6d6" @click="showPassword = true"></van-icon>
                    </template>
                </van-field>

                <div class="submitBtn" @click="onSubmit">
                    {{ t('提现') }}
                </div>

            </div>

            <div class="withdrawalNotes">
                <div class="notice">
                    <span class="noticeText">Kind tips</span>
                </div>
                <div class="noticeList">
                    <div class="noticeListItem">
                        <span>1: Minimum withdrawal amount is Rs {{ min }}. </span>
                    </div>
                    <div class="noticeListItem">
                        <span>2: You can withdraw once a day.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>3: Withdrawal will reach your account within 24-72.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>4: Withdrawal tax {{ tar }}%.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>5: If the withdrawal fails, please reapply or check whether your bank account information
                            is
                            correct.</span>
                    </div>
                    <div class="noticeListItem">
                        <span>5: IFSC should be 11 characters, and the 5th character should be "0", not "O". If you fill
                            in
                            incorrect bank information,your withdrawal will fail.</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <MyLoading :show="loadingShow" :title="loadtitle"></MyLoading>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from "vue";
import MyNav from "../../components/Nav.vue";
import MyLoading from "../../components/Loading.vue";
import { goRoute } from "../../global/common";
import { Field, CellGroup, Cell, Checkbox, RadioGroup, Radio, Tag, Picker, Popup, Icon, Image } from "vant";

export default defineComponent({
    name: "withdrawal",
    components: {
        MyNav, MyLoading,
        [Field.name]: Field,
        [CellGroup.name]: CellGroup,
        [Cell.name]: Cell,
        [Checkbox.name]: Checkbox,
        [RadioGroup.name]: RadioGroup,
        [Radio.name]: Radio,
        [Tag.name]: Tag,
        [Picker.name]: Picker,
        [Popup.name]: Popup,
        [Icon.name]: Icon,
        [Image.name]: Image,
    }
})
</script>
<script lang="ts" setup>
import icon114 from '../../assets/ico/114.png'
import icon121 from '../../assets/ico/121.png'

import { http } from "../../global/network/http";
import { _alert, lang } from "../../global/common";
import { useRouter } from "vue-router";
import md5 from "md5";
import { useI18n } from 'vue-i18n'; const { t } = useI18n();

let isRequest = false
const router = useRouter()
const loadtitle = ref("Loading...")
const loadingShow = ref(false);
const showPassword = ref(false)

const password2 = ref<string>('')

const dataForm = reactive({
    password2: '',
    money: '0'
})

const checked = ref(1)
const wallet = ref({})
const banklog = ref({})
const min = ref(0)
const max = ref(0)
const tar = ref(0)
const ptypeArr = ref([])

const onClickAll = () => {
    dataForm.money = wallet.value.balance * 1
}

const onSubmit = () => {
    if (isRequest) {
        return
    } else {
        isRequest = true
    }

    let ptype: any = {}
    for (let i in ptypeArr.value) {
        if (ptypeArr.value[i].id == checked.value) {
            ptype = ptypeArr.value[i];
            break
        }
    }

    if (dataForm.money - 0 < min.value - 0) {
        isRequest = false
        _alert(' Minimum withdrawal amount is ' + min.value)
        return
    }

    if (dataForm.money - 0 > max.value - 0) {
        isRequest = false
        _alert(' Maximum withdrawal amount is ' + max.value)
        return
    }

    loadingShow.value = true;
    const delayTime = Math.floor(Math.random() * 1000);
    setTimeout(() => {
        http({
            url: 'c=Finance&a=withdrawAct',
            data: {
                //banklog_id:banklog.value.id,
                money: dataForm.money,
                password2: md5(password2.value)
            }
        }).then((res: any) => {
            loadingShow.value = false;
            if (res.code != 1) {
                isRequest = false
                _alert(res.msg)
                return
            }
            _alert(res.msg, function () {
                location.reload()
            })
        })
    }, delayTime)

}

onMounted(() => {
    http({
        url: 'c=Finance&a=withdraw'
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg, function () {
                router.go(-1)
            })
            return
        }
        console.log(res.data.sys_pset.cash.max);
        ptypeArr.value = res.data.ptms
        wallet.value = res.data.wallet
        banklog.value = res.data.banklog
        min.value = res.data.sys_pset.cash.min
        max.value = res.data.sys_pset.cash.max
        tar.value = res.data.sys_pset.cash.fee.percent
    })
})

</script>
<style lang="scss" scoped>
.recharge {
    height: 100vh;
    overflow-y: scroll;
    background: #fff;
    color: #000;
    height: 100%;
    position: relative;

    input {
        color: #000;
    }

    .recharge_wrap {

        .fieldbox {
            :deep(.van-field__left-icon) {
                display: flex;
                position: relative;
                right: 1rem;
                margin-right: -0.8rem;
            }
        }

        .payinfo {
            margin-top: 0.3rem;

            ul {
                display: flex;
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: space-between;

                li {
                    padding: 0.3rem 0;
                    color: #727f72;
                    width: 48%;
                    display: flex;
                    flex-direction: column;
                    justify-content: space-around;
                    align-items: center;
                    background-color: white;
                    margin-bottom: 1rem;
                    height: 3rem;
                    box-shadow: 0 0 10px 0 #d1d1d1;
                    border-radius: 5px;

                    span {
                        color: #009900;
                        font-weight: bold;
                        margin-bottom: 0.2rem;
                    }
                }
            }
        }


        .submitBtn {
            display: flex;
            align-items: center;
            justify-content: space-around;
            height: 2.5rem;
            width: 100%;
            text-align: center;
            line-height: 2.5rem;
            color: white;
            background-color: #009900;
        }

        .withdrawalNotes {
            padding: 1rem;

            .notice {
                display: flex;
                align-items: center;
                justify-content: space-around;
                color: #009900;

                img {
                    width: 0.8rem;
                    margin-right: 0.5rem;
                }

                .noticeText {
                    font-size: 0.875rem;
                    font-weight: bold;
                }

                .dot {
                    width: 0.5rem;
                    height: 0.5rem;
                    background: #000;
                    border-radius: 50%;
                    margin-right: 0.375rem;
                }
            }

            .noticeList {
                .noticeListItem {
                    margin-top: 0.5rem;
                    line-height: 20px;
                    font-size: 0.75rem;
                }
            }
        }
    }
}
</style>