<template>
    <div class="paylogBox" style="height: 100%;overflow-y: auto;">
        <Nav leftText=''></Nav>
        <div class="share">
            <div style="width: 100%;display: flex;justify-content: space-around;">
                <van-image :src="imgFlag(tdata.avatar)" width="4.125rem" height="4.125rem"></van-image>
            </div>            
            <van-cell-group>
                <van-field v-model="montage.urls">
                    <template #button>
                        <van-button size="mini" type="warning" class="sendCodeBtn" plain  ref="linkCopyRef" >
                            <span>Invitation</span>
                        </van-button>
                    </template>
                </van-field>
            </van-cell-group>
            <div class="teamtotal">
                <div style="border-right: 1px solid #d1d1d1;">
                    <span style="font-size: 0.8rem">Team Size</span>
                    <span style="color: #009900;font-weight: bold">{{teamusercount1}}</span>
                </div>
                <div style="">
                    <span style="font-size: 0.8rem">Total Recharge</span>
                    <span style="color: #009900;font-weight: bold">{{TotalRecharge}}</span>
                </div>
            </div>
        </div>
        <div class="team">
          <div class="team_b" >
            <div>
              {{fy.lv1}}
              <spn style="position: absolute;right: 2rem;" @click="onLink({ name: 'User_teamlevel', params: { type: 'B' } })">{{t('详情')}}</spn>
            </div>
            <div>
              <span>Member</span>
              <span>{{lv1.people}}</span>
            </div>
            <div>
              <span>Today's new member</span>
              <span>{{lv1.todaypeople}}</span>
            </div>
            <div>
              <span>Order Total</span>
              <span>{{lv1.totalorder}} RS</span>
            </div>
          </div>
          <div class="team_c">
            <div style="background-color: #8d4bbb;">
              {{fy.lv2}}              
              <spn style="position: absolute;right: 2rem;" @click="onLink({ name: 'User_teamlevel', params: { type: 'C' } })">{{t('详情')}}</spn>
            </div>
            <div>
              <span>Member</span>
              <span>{{lv2.people}}</span>
            </div>
            <div>
              <span>Today's new member</span>
              <span>{{lv2.todaypeople}}</span>
            </div>
            <div>
              <span>Order Total</span>
              <span>{{lv2.totalorder}} RS</span>
            </div>
          </div>
          <div class="team_d">
            <div style="background-color: #0c8918;">
              {{fy.lv3}}
              <spn style="position: absolute;right: 2rem;" @click="onLink({ name: 'User_teamlevel', params: { type: 'D' } })">{{t('详情')}}</spn>
            </div>
            <div>
              <span>Member</span>
              <span>{{lv3.people}}</span>
            </div>
            <div>
              <span>Today's new member</span>
              <span>{{lv3.todaypeople}}</span>
            </div>
            <div>
              <span>Order Total</span>
              <span>{{lv3.totalorder}} RS</span>
            </div>
          </div>
        </div>

    </div>
    <MyLoading :show="loadingShow" title="Loading..."></MyLoading>
</template>

<script lang="ts">
import { _alert, goRoute } from "../../global/common";
import { defineComponent, onMounted, ref, computed } from "vue";
import Nav from "../../components/Nav.vue";
import MyListBase from "../../components/ListBase.vue";
import MyLoading from "../../components/Loading.vue";
import {
  Button,
  Tab,
  Tabs,
  Grid,
  GridItem,
  Cell,
  Field,
  Icon,
  Image,
} from "vant";

export default defineComponent({
  components: {
    Nav,
    MyListBase,
    MyLoading,
    [Button.name]: Button,
    [Image.name]: Image,
    [Tab.name]: Tab,
    [Tabs.name]: Tabs,
    [Grid.name]: Grid,
    [GridItem.name]: GridItem,
    [Cell.name]: Cell,
    [Field.name]: Field,
    [Icon.name]: Icon,
  },
});
</script>
<script lang="ts" setup>
import http from "../../global/network/http";
import { getSrcUrl, imgPreview, copy } from "../../global/common";
import { useI18n } from "vue-i18n";
const { t } = useI18n();

const onLink = (to: any) => {
  goRoute(to);
};
const loadingShow = ref(true);
const teamusercount = ref(0);
const teamusercount1 = ref(0);
const teamcount = ref(0);
const TotalRecharge = ref(0);
const linkCopyRef = ref();

const fy = ref({
  lv1: "",
  lv2: "",
  lv3: "",
});

const lv1 = ref({
  people: 0,
  todaypeople:0,
  totalorder:0,
});
const lv2 = ref({
  people: 0,
  todaypeople:0,
  totalorder:0,
});
const lv3 = ref({
  people: 0,
  todaypeople:0,
  totalorder:0,
});

const tdata = ref({
  RS: 0,
  avatar: "",
  icode: "",
  people: 0,
});


const imgFlag = (src: string) => {
  return getSrcUrl(src, 1);
};

const getTeam = () => {
  http({
    url: "c=User&a=GetTeamHierarchyPeopleNum",
  }).then((res: any) => {
    loadingShow.value = false;
    for (var it of res.data.list) {
      if (it.level == "1") {
        lv1.value.people += 1;
        lv1.value.totalorder += it.pro_order_B-0;
      } else if (it.level == "2") {
        lv2.value.people += 1;
        lv2.value.totalorder += it.pro_order_C-0;
      } else if (it.level == "3") {
        lv3.value.people += 1;
        lv3.value.totalorder += it.pro_order_D-0;
      }

      if(it.newmember1){
        lv1.value.todaypeople += 1;        
      }
      if(it.newmember2){
        lv2.value.todaypeople += 1;        
      }
      if(it.newmember3){
        lv3.value.todaypeople += 1;        
      }
    }

    var fylStr = res.data.fy;
    fy.value.lv1 = "B " + fylStr.split(",")[0].split("=")[1] + "%";
    fy.value.lv2 = "C " + fylStr.split(",")[1].split("=")[1] + "%";
    fy.value.lv3 = "D " + fylStr.split(",")[2].split("=")[1] + "%";
    teamcount.value = lv1.value.people + lv2.value.people + lv3.value.people;
    TotalRecharge.value = res.data.TotalRecharge
  });
};

const getusercount = () => {
  http({
    url: "c=User&a=gettodayregusercount",
  }).then((res: any) => {
    if (res.code != 1) {
      _alert({
        type: "error",
        message: res.msg,
        onClose: () => {},
      });
      return;
    }
    teamusercount.value = res.data.count;
    teamusercount1.value = res.data.count1;
  });
};

const getshare = () => {
  http({
    url: "c=Share&a=index",
  }).then((res: any) => {
    tdata.value = res.data;
    copy(linkCopyRef.value.$el, {
      text: (target: HTMLElement) => {
        return montage.value.urls.toLocaleLowerCase();
      },
    });
  });
};

const montage = computed(() => {
  return {
    urls: location.origin + "/#/Register?Icode=" + tdata.value.icode,
  };
});

onMounted(() => {
  getusercount();
  getTeam();
  getshare();
});


</script>

<style>
.van-hairline--top:after {
    border-top-width: 0px !important;
}

.van-grid-item__content:after {
    border-width: 0px !important;
}
</style>

<style lang="scss" scoped>
.paylogBox {
  background: #fff;
  color: #000;
  padding: 1rem;
  height: auto !important;

  .share {
    display: flex;
    justify-content: space-around;
    flex-direction: column;

    .sendCodeBtn {
      background-color: #009900;
      color: white;
      font-weight: 100;
      border-radius: 8px;
      padding: 0.2rem 0.5rem;
    }

    :deep(.van-field) {
      font-size: 0.7rem;
      border: 1px solid #d1d1d1;
      border-radius: 5px;
      margin-top: 1rem;
      padding-right: 0.3rem;
    }

    :deep(.van-field__button){
      padding-left: 0.2rem;
    }

    .teamtotal {
      height: 3rem;
      border: 1px solid #f1f1f1;
      margin-top: 1rem;
      box-shadow: 0 0 8px 0 #d1d1d1;
      display: flex;
      border-radius: 10px;

      div {
        display: flex;
        width: 49%;
        justify-content: space-around;
        height: 3rem;
        align-items: center;
      }
    }
  }

  .team{

    .team_b,.team_c,.team_d{
      box-shadow: 0 0 10px 0 #d1d1d1;
      border-radius: 10px;
      overflow: hidden;
      margin-top: 1rem;

      div{
        height: 2rem;
        line-height: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 1rem 0 0.5rem;
        font-size: 0.8rem;
        border-bottom: 1px solid #d1d1d1;
        color: #555555;
      }

      div:first-child{
        background-color: #009900;
        color: white;
        font-weight: bold;
        padding: 0; 
        justify-content: center;
        border-bottom: 0;
      }

      div:last-child{
        border: none;
      }
    }
  }

  .will {
    margin-top: 1rem;
    background: linear-gradient(to right, #c49b6c 20%, #a77d52);
    border-radius: 6px;

    .card {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      align-items: center;
      font: 16px/20px "Rotobo";

      .item {
        display: flex;
        height: 5rem;
        text-align: center;
        flex-direction: row-reverse;
        margin-top: 1rem;

        .p1 {
          font-weight: bold;
          color: #fff;
          margin-left: 0.2rem;
        }

        .p2 {
          color: #fff;
          font-weight: bold;
        }
      }
    }
  }

  .paylogBoxWrapper {
    box-sizing: border-box;

    .levelTab {
      margin-top: 1.45rem;
      margin-bottom: 3rem;

      :deep(.van-tabs__nav) {
        background-color: transparent;
      }

      :deep(.van-tabs__content) {
        margin-top: 1rem;
      }

      :deep(.van-tab) {
        .van-tab__text {
          color: #ddd;
          font-weight: bold;
        }
      }

      :deep(.van-tab--active) {
        .van-tab__text {
          color: #fff;
          padding: 0.2rem 0.8rem;
          border: 1px solid #fff;
          border-radius: 6px;
        }
      }

      :deep(.van-grid-item__content--center) {
        flex-direction: row;
        padding: 1rem 0.375rem;
      }

      :deep(.van-grid-item__content:after) {
        border-width: 0px !important;
      }

      .levelItem_right {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        font-size: 0.75rem;
        margin-left: 0.1875rem;
      }

      .levelTabMember {
        height: 2rem;
        margin: 1rem 0 0.6rem 5%;
        font-size: 0.8rem;

        .levelTabValidMember {
          width: 45%;
          height: 2rem;
          line-height: 2rem;
          float: left;
          border-radius: 10px;
          text-align: center;
          background: #c49b6c;
          color: #fff;
          font-weight: bold;
        }

        .levelTabInactiveMember {
          width: 45%;
          height: 2rem;
          line-height: 2rem;
          float: right;
          border-radius: 10px;
          text-align: center;
          background: #c49b6c;
          color: #fff;
          font-weight: bold;
        }
      }
    }
  }

  .list-box {
    .invite {
      p {
        margin-top: 1rem;
        font-weight: bold;
        color: #64523e;
      }

      .copy {
        display: flex;
        align-items: center;

        :deep(.van-button) {
          height: 2.4rem;
        }

        :deep(.van-button__text) {
          display: flex;
          align-items: center;
          justify-content: flex-start;

          img {
            width: 1rem;
          }
        }
      }

      :deep(.van-field__control:read-only) {
        text-transform: none !important;
      }
    }

    :deep(.van-tabs__wrap) {
      top: -4.6rem;
      position: absolute;
      width: 100%;
    }

    .myListBox {
      display: flex;
      flex-direction: column;

      .listHead {
        font: bold 14px/20px "Rotobo";
      }

      .listitem {
        font: bold 12px/32px "Rotobo";

        td {
          text-align: center;
        }
        td:last-child{
            text-align: right;
        }

        .plus {
          display: inline-block;
          background: #a2754c;
          color: #fff;
          padding: 0 4px;
          font: normal 10px/16px "微软雅黑";
          border-radius: 10px;
        }
      }
    }
  }

  :deep(.van-field__control::-webkit-input-placeholder) {
    color: #64523e;
    font-weight: bold;
  }
}
</style>