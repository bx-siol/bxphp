<template>
  <div style="min-height: 480px" class="giftbag">
    <MyNav leftText=''></MyNav>
    <div class="reward">
      <van-form @submit="onSubmit">
        <van-cell-group inset>
          <van-field style="font-weight: normal; color: #000" v-model="dataForm.rsn" label-width="5rem" :placeholder="t('请输入兑换码')" />
        </van-cell-group>

        <div class="reward-btn">
          <van-button class="save" round block type="primary" native-type="submit">
            {{ t("收到") }}
          </van-button>
          <p style="margin-top: 1rem;">Copy the bonus code and send it your friends,</p>
          <p>and get rewards after success.</p>
        </div>
      </van-form>
    </div>
  </div>
</template>
<script lang="ts">
import { _alert, lang } from "../../global/common";
import { defineComponent, ref, reactive, onMounted } from "vue";
import { Button, Form, Field, CellGroup } from "vant";
import MyNav from "../../components/Nav.vue";

export default defineComponent({
  components: {
    MyNav,
    [Button.name]: Button,
    [Form.name]: Form,
    [Field.name]: Field,
    [CellGroup.name]: CellGroup,
  },
});
</script>

<script lang="ts" setup>
import { useRoute } from "vue-router";
import http from "../../global/network/http";
import { useI18n } from "vue-i18n";
const { t } = useI18n();

let isRequest = false;
const route = useRoute();

const dataForm = reactive({
  rsn: "",
});

const onSubmit = () => {
  if (isRequest) {
    return;
  } else {
    isRequest = true;
  }
  const delayTime = Math.floor(Math.random() * 1000);
  setTimeout(() => {
    http({
      url: "c=Gift&a=redpackAct",
      data: { rsn: dataForm.rsn },
    }).then((res: any) => {
      if (res.code != 1) {
        _alert(res.msg);
        isRequest = false;
        return;
      }
      _alert({
        type: "success",
        message: res.msg,
        onClose: () => {
          dataForm.rsn = "";
          isRequest = false;
        },
      });
    });
  }, delayTime);
};

onMounted(() => {});
</script>
<style lang="scss" scoped>
.van-field__control,
.van-field__label,
label {
  color: rgb(0, 0, 0) !important;
}

.giftbag {
  background: no-repeat url(../../assets/img/giftCode-bg.png) 0 2rem;
  background-repeat: no-repeat;
  background-size: 100% 100%;
  height: 100%;
}

.reward {
  width: 92%;
  height: 15rem;
  margin: 0 auto;
  position: fixed;
  bottom: 0%;
  left: 50%;
  transform: translateX(-50%);
  font: bold 22px/18px "微软雅黑";
  border-radius: 15px;
  color: white;
  display: flex;
  flex-direction: column;
  background-repeat: no-repeat;
  background-size: 100%;
  max-width: 640px;

  .exchange {
    margin: 1.5rem auto 0.5rem;
    color: #222;
  }

  :deep .van-cell-group--inset {
    width: 90%;
    margin: 1rem auto 2rem;
    border-radius: 8px;
  }

  :deep .van-field__control {
    color: #222;
    text-align: left;
  }

  :deep(.van-field__control::-webkit-input-placeholder) {
    color: #999 !important; //placeholder颜色修改
  }

  .reward-btn {
    display: flex;
    justify-content: space-around;
    flex-direction: column;
    align-items: center;

    .save {
      height: 2.5rem;
      border: none;
      font-size: 1.2rem;
      background: #f8c22e;
      border-radius: 10px;
      width: 80%;
    }

    p{
        width: 90%;
        font-size: 0.8rem;
        font-weight: 100;
        color: #f8c22e;
        text-align: center;
        font-weight: bold;
    }
  }
}
</style>
