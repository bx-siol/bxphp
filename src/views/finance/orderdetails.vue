<template>
    <div class="conBox" style="height: auto;background-color: #fff;">
        <Nav></Nav>
        <!--------------- 充值订单详细信息 ---------------->
        <div class="goods">
            <ul class="ul_top">
                <li>
                    <p>Order Number:</p>
                    <p>{{ route.params.osn }}</p>
                </li>
                <li>
                    <p>Quantity:</p>
                    <p>{{ route.params.money }} Rs</p>
                </li>
                <li>
                    <p>Recharge Time: </p>
                    <p>{{ route.params.par1 }}</p>
                </li>
                <li>
                    <p>Payment Method: </p>
                    <p>{{ route.params.par2 }}</p>
                </li>
                <li>
                    <p>State:</p>
                    <p>{{ t(route.params.par3) }}</p>
                </li>
            </ul>

        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent, reactive, computed } from 'vue'
import Nav from '../../components/Nav.vue'
import MyUpload from '../../components/Upload.vue'
import MyListBase from '../../components/ListBase.vue';
export default defineComponent({
    components: {
        Nav, MyUpload, MyListBase,
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

const route = useRoute()
const loadingShow = ref(true)
const pageRef = ref()
const tableData = ref<any>({})


</script>


<style scoped>
.conBox {
    padding: 1.2rem;
}

.conBox ul li {
    display: flex;
    justify-content: space-between;
    line-height: 28px;
}

.conBox ul li p {
    color: #3c3c3c;
    font-weight: bold;

}

.conBox ul li p:nth-child(1) {
    color: #64523e;
    font-weight: bold;
}
</style>