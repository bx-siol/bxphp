<template>
    <van-overlay :show="show" style="z-index: 1999;background-color:rgba(53, 51, 51, 0.7);">
        <div class="wrapper animate__animated animate__slideInDown animate__faster" :style="wrapperStyle" @click.stop>
            <div v-if="title.length>0" :style="scopeTitleStyle">
                <span>{{title}}</span>
                <van-icon name="close" class="close1" size="28" v-if="closeType==1" @click="onClose"/>
            </div>
            <div class="content">
                <div class="contentInner" :style="contentStyle">
                    <slot></slot>
                </div>
            </div>
            <van-icon name="close" class="close2" size="40" v-if="closeType==2" @click="onClose"/>
        </div>
    </van-overlay>
</template>

<script lang="ts">
    import {defineComponent} from 'vue';
    import {Overlay,Icon} from 'vant';

    export default defineComponent({
        components:{
            [Overlay.name]:Overlay,
            [Icon.name]:Icon
        }
    })
</script>

<script lang="ts" setup>
    import {ref} from 'vue';
    const emit=defineEmits(['update:show'])
    const props=defineProps({
        show:{
            type:Boolean,
            default:false
        },
        wrapperStyle:{
            type:Object,
            default:{
                width:'80%',
                height:'70%',
                marginTop:'10%'
            }
        },
        titleStyle:{
            type:Object,
            default:null
        },
        contentStyle:{
            type:Object,
            default:{
                padding:'5%'
            }
        },
        title:{
            type:String,
            default:''
        },
        closeType:{
            type:Number,
            default:2
        }
    })

    const scopeTitleStyle=ref({
        position:'relative',
        lineHeight:'3rem',
        textAlign:'center',
        borderBottom:'1px solid #383a3a'
    })
    if(props.titleStyle){
        Object.assign(scopeTitleStyle.value,props.titleStyle)
    }

    const onClose=()=>{
        emit('update:show',false)
    }
</script>

<style scoped>
    .wrapper {
        background-color: #404040;
        color: #ffffff;
        margin: 0 auto;
        border-radius: 2.5%;
        overflow: visible;
        position: relative;
        max-width: 540px;
    }

    .wrapper .content{
        overflow-x: hidden;
        overflow-y: auto;
        height: 92%;
    }

    .wrapper .close1{
        position: absolute;
        right: 0.5rem;
        top:50%;
        margin-top:-14px;
    }

    .wrapper .close2{
        position: absolute;
        bottom: -3.2rem;
        left: 50%;
        margin-left: -20px;
        color: white;
    }
</style>