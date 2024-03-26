<template>
    
    <div class="conbox">
        <div class="consearch">
            <el-button type="success" v-for="item in searchArr" @click="onClickSearchItem(item)">{{ item.name }}</el-button>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable v-model="sStartTime" type="date"
                placeholder="开始日期">
            </el-date-picker>
            <el-date-picker size="small" :style="{ marginLeft: '10px', width: '150px' }" clearable v-model="sEndTime" type="date"
                placeholder="结束日期">
            </el-date-picker>
            <el-button type="primary" style="margin-left: 10px;" @click="onSearch">查询</el-button>
        </div>
        <div class="indexDefaultBox">
            <el-row>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.total_pay_money }}</div>
                            <div class="countItemTxt">总充值</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.total_balance }}</div>
                            <div class="countItemTxt">总余额</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.invest_money }}</div>
                            <div class="countItemTxt">总投资</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.total_cash_money }}</div>
                            <div class="countItemTxt">总提现</div>
                        </div>
                    </div>
                </el-col>
            </el-row>

            <div class="countItemHr"></div>

            <el-row>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_2" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.total_member }}</div>
                            <div class="countItemTxt">总会员数</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_3" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.invest_count }}</div>
                            <div class="countItemTxt">总订单数</div>
                        </div>
                    </div>
                </el-col>                
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.reward_money_today }}</div>
                            <div class="countItemTxt">今日订单分红</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.reward_money }}</div>
                            <div class="countItemTxt">总订单分红</div>
                        </div>
                    </div>
                </el-col>                
            </el-row>

            <div class="countItemHr"></div>

            <el-row>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_3" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.invest_count_today }}</div>
                            <div class="countItemTxt">今日订单</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.today_redpack_money }}</div>
                            <div class="countItemTxt">今日红包</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.today_pay_money }}</div>
                            <div class="countItemTxt">今日充值</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.today_first_pay }}</div>
                            <div class="countItemTxt">今日首充</div>
                        </div>
                    </div>
                </el-col>                
            </el-row>

            <div class="countItemHr"></div>
            <el-row>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.today_cash_money }}</div>
                            <div class="countItemTxt">今日提现</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.uncheck_cash_money }}</div>
                            <div class="countItemTxt">提现未到账</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_2" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.today_member }}</div>
                            <div class="countItemTxt">今日注册</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_2" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.effective_member }}</div>
                            <div class="countItemTxt">有效会员</div>
                        </div>
                    </div>
                </el-col>                
            </el-row>
            <div class="countItemHr"></div>
            <el-row>
                <el-col :span="6">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_1" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ tableData.total_jy }}</div>
                            <div class="countItemTxt">总结余</div>
                        </div>
                    </div>
                </el-col>
                <el-col :span="8">
                    <div class="countItem">
                        <div class="countItemLeft">
                            <el-image :src="ico_4" class="countItemIcon" />
                        </div>
                        <div class="countItemRight">
                            <div class="countItemNum">{{ yzm }}  <span style="font-size: 0.7rem;color: red;">万能验证码（使用后作废，有效期十分钟）</span></div>
                            <div class="countItemTxt"><el-button size="small" type="primary" @click="yzmclk">万能验证码</el-button></div>
                            
                        </div>
                    </div>
                </el-col>
            </el-row>
            <div style="height: 50px;"></div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, ref, reactive, onMounted } from 'vue';

export default defineComponent({

})
</script>
<script lang="ts" setup>
import { ico_1, ico_2, ico_3, ico_4 } from '../../global/assets';
import { onMounted, ref } from 'vue'
import { useStore } from "vuex";
import http from "../../global/network/http";
import dayjs from "dayjs";
import { _alert } from "../../global/common";
const store = useStore()
// console.log(ico_4)

const tableData = ref({})
const searchArr = [
    { type: 1, name: '今天' },
    { type: 2, name: '昨天' },
    { type: 3, name: '最近7天' },
    { type: 4, name: '最近30天' },
    { type: 5, name: '上月' },
    { type: 6, name: '本月' }
]

const searchItem = ref({
    type: 0
})
const onClickSearchItem = (item: any) => {
    searchItem.value = item
    doSearch()
}

const sStartTime = ref('')
const sEndTime = ref('')

const doSearch = () => {
    let s_start_time = dayjs(new Date(sStartTime.value)).format('YYYY-MM-DD')
    let s_end_time = dayjs(new Date(sEndTime.value)).format('YYYY-MM-DD')
    http({
        url: 'c=Default&a=getData1',
        data: {
            type: searchItem.value.type,
            start_time: s_start_time,
            end_time: s_end_time
        }
    }).then((res: any) => {
        if (res.code != 1) {
            _alert(res.msg)
            return
        }
        tableData.value.total_balance = res.data.total_balance
        tableData.value.invest_count_today = res.data.invest_count_today
        tableData.value.invest_count = res.data.invest_count
        tableData.value.invest_money = res.data.invest_money
        tableData.value.reward_money = res.data.reward_money
        tableData.value.reward_money_today = res.data.reward_money_today

        http({
            url: 'c=Default&a=getData2',
            data: {
                type: searchItem.value.type,
                start_time: s_start_time,
                end_time: s_end_time
            }
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            tableData.value.total_pay_money = res.data.total_pay_money
            tableData.value.today_pay_money = res.data.today_pay_money
            tableData.value.today_first_pay = res.data.today_first_pay
            tableData.value.total_cash_money = res.data.total_cash_money
            tableData.value.today_cash_money = res.data.today_cash_money
            tableData.value.uncheck_cash_money = res.data.uncheck_cash_money
            tableData.value.total_jy = res.data.total_jy


            http({
                url: 'c=Default&a=getData3',
                data: {
                    type: searchItem.value.type,
                    start_time: s_start_time,
                    end_time: s_end_time
                }
            }).then((res: any) => {
                if (res.code != 1) {
                    _alert(res.msg)
                    return
                }
                tableData.value.today_redpack_money = res.data.today_redpack_money
                tableData.value.total_member = res.data.total_member
                tableData.value.today_member = res.data.today_member
                tableData.value.effective_member = res.data.effective_member
            })

        })

    })
}

const yzm = ref('');
const yzmclk = () => {
    http({
            url: 'c=Default&a=yzm'
        }).then((res: any) => {
            yzm.value = res.data.sms_code
        })
}

const onSearch = () => {
    searchItem.value.type = 0
    doSearch()
}

onMounted(() => {
    if (!store.state.user.gid || store.state.user.gid == 92) {
        location.href = '/h5/'
        return
    }
    onSearch()
    setTimeout(() => { store.dispatch('loadingFinish'); }, store.state.loadingTime)
})

</script>

<style>
.indexDefaultBox {
    padding: 2rem 2rem;
}

.indexDefaultBox .countItem {
    width: 100%;
    overflow: hidden;
}

.indexDefaultBox .countItemLeft,
.indexDefaultBox .countItemRight {
    display: inline-block;
    vertical-align: middle;
    height: 70px;
    border: 0px solid #dedede;
}

.indexDefaultBox .countItemLeft {
    width: 70px;
    line-height: 70px;
    text-align: center;
    font-weight: bold;
    border-radius: 5px;
    background-color: #409eff;
    color: white;
}

.countItemIcon {
    margin-top: 11px;
}

.indexDefaultBox .countItemRight {
    padding-left: 8px;
}

.countItemRight .countItemNum {
    font-weight: bold;
    font-size: 20px;
    padding-top: 8px;
}

.countItemRight .countItemTxt {}

.countItemHr {
    height: 1px;
    background-color: #eeeeee;
    margin: 2rem 0;
}
</style>