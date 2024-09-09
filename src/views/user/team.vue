<template>
  <div class="paylogBox" style="height: 100%;overflow-y: auto;">
    <Nav leftText=''></Nav>
    <div class="teamdata">
      <div class="teamdataList">
        <div class="teamdataitem">
          <div> {{ tableData.paycount + tableData.unpaycount }} </div>
          <div> All members </div>
        </div>
        <div class="teamdataitem" @click="onLinkc('pay')">
          <div> {{ tableData.paycount }} </div>
          <div> {{ LVv }} Valid member </div>
        </div>
        <div class="teamdataitem">
          <div> {{ teamusercount }} </div>
          <div> Team Day Invitation </div>
        </div>
        <div class="teamdataitem">
          <div> {{ tableData.today }} </div>
          <div> Added today </div>
        </div>
        <div class="teamdataitem" @click="onLinkc('unpay')">
          <div> {{ tableData.unpaycount }} </div>
          <div> {{ LVv }} Invalid member </div>
        </div>
        <div class="teamdataitem">
          <div> {{ teamusercount1 }} </div>
          <div> Team Size </div>
        </div>
      </div>
    </div>
    <div class="share">
      <div style="color: #009900;font-weight: bold;font-size: 0.9rem;">Invitation Code</div>
      <van-cell-group>
        <van-field v-model="montage.icode">
          <template #button>
            <van-button size="mini" type="warning" class="sendCodeBtn" plain ref="CodeCopyRef">
              <span>Copy</span>
            </van-button>
          </template>
        </van-field>
        <div style="color: #009900;font-weight: bold;font-size: 0.9rem;margin-top: 1rem;">Invitation Link</div>
        <van-field v-model="montage.urls">
          <template #button>
            <van-button size="mini" type="warning" class="sendCodeBtn" plain ref="linkCopyRef">
              <span>Copy</span>
            </van-button>
          </template>
        </van-field>
      </van-cell-group>
    </div>

    <div
      style="height: 3rem;display: flex;color: #009900;font-weight: bold;justify-content: center;align-items: center;">
      Team Details
    </div>

    <div class="paylogBoxWrapper">
      <van-tabs @click-tab="onClickTab" line-height="0" v-model:active="active" class="levelTab">
        <van-tab :title="fy.lv1">
          <MyListBase :url="requesturl1" ref="pageRef" @success="onPageSuccess">
            <template #default="{ list }">
              <table style="border-collapse: collapse;">
                <thead>
                  <tr class="listHead">
                    <th>{{ t('用户名') }}</th>
                    <th>{{ t('推荐人') }}</th>
                    <th>{{ t('团队规模') }}</th>
                    <th>{{ t('资产') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="listitem" v-for="(item, index) in list" :key="index">
                    <td>{{ item.account }}</td>
                    <td>{{ item.referrer }}</td>
                    <td>{{ item.teamSize }}</td>
                    <td>{{ item.assets }}RS</td>
                  </tr>
                </tbody>
              </table>
            </template>
          </MyListBase>
        </van-tab>
        <van-tab :title="fy.lv2">
          <MyListBase :url="requesturl2" ref="pageRef1" @success="onPageSuccess">
            <template #default="{ list }">
              <table style="border-collapse: collapse;">
                <thead>
                  <tr class="listHead">
                    <th>{{ t('用户名') }}</th>
                    <th>{{ t('推荐人') }}</th>
                    <th>{{ t('团队规模') }}</th>
                    <th>{{ t('资产') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="listitem" v-for="(item, index) in list" :key="index">
                    <td>{{ item.account }}</td>
                    <td>{{ item.referrer }}</td>
                    <td>{{ item.teamSize }}</td>
                    <td>{{ item.assets }}RS</td>
                  </tr>
                </tbody>
              </table>
            </template>
          </MyListBase>
        </van-tab>
        <van-tab :title="fy.lv3">
          <MyListBase :url="requesturl3" ref="pageRef2" @success="onPageSuccess">
            <template #default="{ list }">
              <table style="border-collapse: collapse;">
                <thead>
                  <tr class="listHead">
                    <th>{{ t('用户名') }}</th>
                    <th>{{ t('推荐人') }}</th>
                    <th>{{ t('团队规模') }}</th>
                    <th>{{ t('资产') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="listitem" v-for="(item, index) in list" :key="index">
                    <td>{{ item.account }}</td>
                    <td>{{ item.referrer }}</td>
                    <td>{{ item.teamSize }}</td>
                    <td>{{ item.assets }}RS</td>
                  </tr>
                </tbody>
              </table>
            </template>
          </MyListBase>
        </van-tab>
      </van-tabs>
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
import { Button, Tab, Tabs, Grid, GridItem, Cell, Field, Icon, Image } from "vant";
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
const CodeCopyRef = ref();
const linkCopyRef = ref();

const fy = ref({
  lv1: "",
  lv2: "",
  lv3: "",
});

const lv1 = ref({
  people: 0,
});
const lv2 = ref({
  people: 0,
});
const lv3 = ref({
  people: 0,
});

const tdata = ref({
  RS: 0,
  avatar: "",
  icode: "",
  people: 0,
});


const getTeam = () => {
  http({
    url: "c=User&a=GetTeamHierarchyPeopleNum",
  }).then((res: any) => {
    loadingShow.value = false;
    for (var it of res.data.list) {
      if (it.level == "1") {
        lv1.value.people += 1;
      } else if (it.level == "2") {
        lv2.value.people += 1;
      } else if (it.level == "3") {
        lv3.value.people += 1;
      }
    }

    var fylStr = res.data.fy;
    fy.value.lv1 = 'B ' + (fylStr.split(',')[0]).split('=')[1] + '%  (' + lv1.value.people + ')';
    fy.value.lv2 = 'C ' + (fylStr.split(',')[1]).split('=')[1] + '%  (' + lv2.value.people + ')';
    fy.value.lv3 = 'D ' + (fylStr.split(',')[2]).split('=')[1] + '%  (' + lv3.value.people + ')';
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
        onClose: () => { },
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
    copy(CodeCopyRef.value.$el, {
      text: (target: HTMLElement) => {
        return montage.value.icode.toLocaleLowerCase();
      },
    });
  });
};

const montage = computed(() => {
  return {
    urls: location.origin + "/#/Register?Icode=" + tdata.value.icode,
    icode: tdata.value.icode,
  };
});


const requesturl1 = ref('c=User&a=team&lv=1')
const requesturl2 = ref('c=User&a=team&lv=2')
const requesturl3 = ref('c=User&a=team&lv=3')
const pageRef = ref()
const pageRef1 = ref()
const pageRef2 = ref()
const cpageRef = ref()
const LVv = ref('Lv1');
const active = ref(0)
const tableData = ref<any>({paycount:0,unpaycount:0,today:0})
const atype = ref();

const onPageSuccess = (res: any) => {
  tableData.value = res.all
  loadingShow.value = false
  if (cpageRef.value == undefined) {
    cpageRef.value = pageRef.value
  } else if (res.lv == 1) {
    cpageRef.value = pageRef.value
  } else if (res.lv == 2) {
    cpageRef.value = pageRef1.value
  } else if (res.lv == 3) {
    cpageRef.value = pageRef2.value
  }
}

// gettodayregusercount
const onClickTab = (title: any) => {
  switch (title.name) {
    case 0:
      cpageRef.value = pageRef.value
      LVv.value = 'Lv1'
      requesturl1.value = "c=User&a=team&lv=1"
      break;
    case 1:
      cpageRef.value = pageRef1.value
      LVv.value = 'Lv2'
      requesturl2.value = "c=User&a=team&lv=2"
      break;
    case 2:
      cpageRef.value = pageRef2.value
      LVv.value = 'Lv3'
      requesturl3.value = "c=User&a=team&lv=3"
      break;
  }
  if (cpageRef.value != undefined) {
    loadingShow.value = true
    cpageRef.value.doSearch()
  }
};

const onLinkc = (type: string) => {
  cpageRef.value.delall();
  loadingShow.value = true
  if (type == 'pay') {
    atype.value = "pay"
    if (tableData.paycount != 0)
      cpageRef.value.doSearch({ type: atype.value })
    else
      loadingShow.value = false
  } else if (type == 'unpay') {
    atype.value = "unpay"
    if (tableData.unpaycount != 0)
      cpageRef.value.doSearch({ type: atype.value })
    else
      loadingShow.value = false
  }
}

onMounted(() => {
  getusercount();
  getTeam();
  getshare();
});


</script>

<style lang="scss" scoped>
.paylogBox {
  background-color: #ebf9e8;
  color: #000;
  height: auto !important;

  .teamdata {
    padding: 1rem;
    background-color: white;

    .teamdataList {
      display: flex;
      border: 1px solid #009900;
      border-radius: 5px;
      padding: 1rem;
      flex-wrap: wrap;

      .teamdataitem {
        display: flex;
        align-items: center;
        justify-content: space-evenly;
        flex-direction: column;
        width: 33.33%;
        height: 3.5rem;
        color: #009900;

        div:first-child {
          font-weight: bold;
        }

        div:last-child {
          font-size: 0.7rem;
        }
      }
    }
  }

  .share {
    display: flex;
    justify-content: space-around;
    flex-direction: column;
    padding: 1rem;
    background-color: white;
    margin-top: 1rem;

    .sendCodeBtn {
      background-color: #009900;
      color: white;
      font-weight: 100;
      border-radius: 8px;
      padding: 0.2rem 0.5rem;
      height: 2rem;
    }

    :deep(.van-field) {
      font-size: 0.7rem;
      border: 1px solid #d1d1d1;
      border-radius: 8px;
      margin-top: 0.3rem;
      padding: 0;
      padding-left: 0.5rem;


    }

    :deep(.van-field__button) {
      padding-left: 0.2rem;
    }

  }

  .paylogBoxWrapper {

    .levelTab {
      background-color: white;

      :deep(.van-tabs__wrap) {
        height: 2.5rem;
        font-weight: bold;

        .van-tab {
          height: 2.5rem;
        }

        .van-tab--active {
          background-color: #009900;
          color: white;
        }
      }

      .myListBox {
        border-top: 1px solid #d1d1d1;

        .listHead {
          height: 2.5rem;
          color: #009900;

          th{
            font-size: 0.9rem;
            font-weight: 600;
          }
        }

        td {
          text-align: center;
          width: 10%;
          border-top: 1px solid #d1d1d1;
          height: 2.5rem;
          font-size: 0.8rem;
          color: #837b7b;
          padding-left: 2%;
        }

        td:last-child {
          text-align: right;
          padding-right: 4%;
        }

      }

    }
  }

}
</style>