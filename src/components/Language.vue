<template>
    <div v-if="props.showAct" class="switchLan"
        :style="[props.switchLanStyle]">
        <van-button class="showPickerBtn" v-if="props.showIcon" :icon="$store.state.showLanguage ? 'arrow-up' : 'arrow-down'"
            type="primary" icon-position="right" color="#fff" style="background-color: transparent; padding: 0;border:none;" @click="onAction">
            {{ dataForm.language_name }}
        </van-button>
        <span :style="props.style" @click="onAction" v-else>
            {{ dataForm.language_name }}
            <van-icon :name="$store.state.showLanguage ? 'arrow-up' : 'arrow-down'" style="vertical-align: middle;color: #333" />
        </span>
    </div>
    <van-action-sheet v-model:show="$store.state.showLanguage" :actions="actions" :cancel-text="t('取消')"
        close-on-click-action @select="onSelect" @cancel="onCancel" />
</template>

<script lang="ts">
import { defineComponent, onMounted, reactive, ref } from 'vue'
import { ActionSheet, Tag, Icon, Button } from 'vant'

export default defineComponent({
    components: {
        [ActionSheet.name]: ActionSheet,
        [Icon.name]: Icon,
        [Tag.name]: Tag,
        [Button.name]: Button
    }
})
</script>

<script lang="ts" setup>
import http from "../global/network/http";
import { useStore } from "vuex";
import { _alert, lang } from "../global/common";
import Switchlan from '../assets/img/home/switchlan.png'
import { useI18n } from 'vue-i18n';
const { t } = useI18n();


const props = defineProps({
    top: {
        type: String,
        default: '0.8rem'
    },
    showIcon: {
        type: Boolean,
        default: false
    },
    showAct: {
        type: Boolean,
        default: true
    },
    style: {
        type: Object,
        default: {
            color: '#ffffff',
            fontSize: '1rem'
        }
    },
    switchLanStyle: {
        type: Object,
        default: {}
    }
})

const emit = defineEmits(['cancel'])

const store = useStore()

const actions = ref<any>([]);
const dataForm = reactive({
    language_code: store.state.config.language_code,
    language_name: store.state.config.language_name
})

const onAction = () => {
    store.state.showLanguage = true
}

const onCancel = () => {
    emit('cancel')
}

const onSelect = (action: any, index: number) => {
  localStorage.setItem('lang', action.lang);
  store.state.showLanguage = false;
  location.reload()
};
const choose = () => {
    onAction()
}

defineExpose({
    choose
})

onMounted(() => {

actions.value.push({
  lang: 'en',
  name: 'EN'
});

actions.value.push({
  lang: 'ind',
  name: 'इंडी'
});
const selectedLang = localStorage.getItem('lang');
if (selectedLang) {
  dataForm.language_code = selectedLang;

  const selectedLangObj = actions.value.find((action: any) => action.lang === selectedLang);
  if (selectedLangObj) {
    dataForm.language_name = selectedLangObj.name;
  }
}
});

</script>
<style lang="scss" scoped>
.showPickerBtn {
  height: 1.375rem;
  box-sizing: border-box;
  border-radius: 0.9375rem;
  display: flex;
  align-items: center;
  justify-content: center;
  line-height: 0.75rem;
  font-weight: bold;
  :deep(.van-button) {
    background-color: #00b57e;
  }

  :deep(.van-icon__image) {
    width: 1.6rem;
    height: 1.6rem;
  }

  :deep(.van-button__content) {
    .van-icon-arrow-down {
      font-size: 0.75rem;
      color: #fff;
    }

    .van-icon-arrow-up {
      font-size: 0.75rem;
      color: #fff;
    }
  }
}
</style>