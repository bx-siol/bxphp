<template>
    <van-overlay :show="show" :custom-style="{background:'rgba(10,10,10,0.4)',zIndex:10000}">
        <div class="wrapper" @click.stop>
            <van-loading size="24px" color="#ffffff" text-color="#ffffff" :vertical="vertical">{{title}}</van-loading>
        </div>
    </van-overlay>
</template>

<script lang="ts">
    import {defineComponent} from 'vue'
    import {Loading,Overlay} from 'vant'
    export default defineComponent({
        components:{
            [Loading.name]:Loading,
            [Overlay.name]:Overlay,
        }
    })
</script>

<script lang="ts" setup>
    const props=defineProps({
        title:{
            type:String,
            default:'加载中...'
        },
        vertical:{
            type:Boolean,
            default:true
        },
        show:{
            type:Boolean,
            default:false
        }
    })
</script>

<style scoped>
/deep/.van-loading--vertical {
    background: #84973b;
    padding: 2rem;
    border-radius: 1rem;
}
    .wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
    }
</style>