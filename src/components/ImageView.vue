<template>
    <el-image-viewer
            v-show="show"
            :url-list="fileList"
            :initial-index="startPosition"
            hide-on-click-modal
            @switch="onSwitch"
            @close="onClose"
    >

    </el-image-viewer>
</template>

<script lang="ts" setup>
    import {defineProps,ref} from 'vue';

    const props=defineProps({
        fileList:{
            type:Array,
            default:[]
        },
        startPosition:{
            type:Number,
            default:0
        },
        show:{
            type:Boolean,
            default:false
        }
    })

    const emit=defineEmits(['update:startPosition','update:show'])

    const onSwitch=(idx:number)=>{
        emit('update:startPosition',idx)
    }

    const onClose=()=>{
        emit('update:show',false)
    }

</script>