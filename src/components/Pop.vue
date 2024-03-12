<template>
    <div class="popBox" v-show="props.visible">
        <div class="el-dialog popWrap animate__animated animate__fadeInDown animate__faster" :style="{width:props.width,marginTop:props.top}">
            <div class="el-dialog__header">
                <span class="el-dialog__title" v-html="props.title"></span>
                <button aria-label="close" class="el-dialog__headerbtn" type="button" @click="onCancel">
                    <i class="el-dialog__close el-icon el-icon-close"></i>
                </button>
            </div>
            <div class="el-dialog__body">
                <slot>内容区域</slot>
            </div>
            <div class="el-dialog__footer">
                <span class="dialog-footer">
                    <slot name="footer">
                        <button class="el-button el-button--default" type="button" @click="onCancel"><span>取消</span></button>
                        <button class="el-button el-button--primary" type="button" @click="onSave"><span>保存</span></button>
                    </slot>
                </span>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import {defineComponent, onMounted, ref} from 'vue'

export default defineComponent({
    emits:['cancel','save'],
    props:{
        title:{
            type:String,
            default:'标题'
        },
        visible:{
            type:Boolean,
            default:false
        },
        width:{
            type:String,
            default:'600px'
        },
        top:{
            type:String,
            default:'8%'
        }
    },
    setup(props,{emit}){

        const onSave=()=>{
            emit('save')
        }

        const onCancel=()=>{
            emit('cancel')
        }

        onMounted(()=>{

        })

        return {
            props,
            onCancel,
            onSave,
        }
    }
})
</script>

<style scoped>
    .popBox{width: 100%;height: 100%;position: fixed;left: 0;top: 0;background-color: rgba(0,0,0,0.5);z-index: 9999;overflow: auto;}
</style>