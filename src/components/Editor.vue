<template>
    <div :style="{width:width}">
        <div :id="esn">
            <p v-if="hasSlot">
                <slot></slot>
            </p>
        </div>
    </div>
</template>

<script lang="ts" setup>
    import {ref,onMounted,useSlots} from 'vue'
    import Editor from 'wangeditor'
    import {_alert, getSrcUrl} from "../global/common";
    import {useStore} from "vuex";

    const props=defineProps({
        esn:{
            type:String,
            default:'content'
        },
        content:{
            type:String,
            default:''
        },
        width:{
            type:String,
            default:'100%'
        },
        height:{
            type:Number,
            default:300
        }
    })

    const store=useStore()
    const slots=useSlots()
    const hasSlot=(slots.default&&slots.default()[0].children)?true:false

    const editor=ref<Editor>()
    //editor.value.destroy()

    const init=()=>{
        editor.value = new Editor('#'+props.esn)
        editor.value.config.uploadImgServer = '/api/?a=upload'
        editor.value.config.uploadImgTimeout = 50 * 1000
        editor.value.config.uploadImgHeaders = {
            'X-Requested-With': 'XMLHttpRequest',
            'Token': store.state.token
        }
        editor.value.config.uploadImgHooks={
            customInsert: (insertImgFn:any, res:any)=>{
                if(res.code!=1){
                    _alert(res.msg)
                    return
                }
                insertImgFn(getSrcUrl(res.data.src))
            }
        }
        editor.value.config.uploadVideoServer = '/api/?a=upload'
        editor.value.config.uploadVideoMaxSize=100 * 1024 * 1024 // 100M
        editor.value.config.uploadVideoHeaders  = {
            'X-Requested-With': 'XMLHttpRequest',
            'Token': store.state.token
        }
        editor.value.config.uploadVideoHooks={
            customInsert: (insertVideoFn:any, res:any)=>{
                if(res.code!=1){
                    _alert(res.msg)
                    return
                }
                insertVideoFn(getSrcUrl(res.data.src))
            }
        }

        editor.value.config.height=props.height
        editor.value.config.zIndex=2

        editor.value.create()

        // let el=slots.default()[0].el as HTMLElement
        // let htmlStr=el.outerHTML
        if(props.content){
            editor.value.txt.html(props.content)
        }
    }

    //设置编辑器的内容
    const setHtml=(htmlStr:string)=>{
        if(editor.value){
            editor.value.txt.html(htmlStr)
        }
    }

    //获取编辑器的内容
    const getHtml=():string=>{
        let html=''
        if(editor.value){
            html=editor.value.txt.html() as string
        }
        return html
    }

    const clear=()=>{
        if(editor.value){
            editor.value.txt.clear()
        }
    }

    //对外暴露方法
    defineExpose({
        clear,
        editor,
        setHtml,
        getHtml
    })

    onMounted(()=>{
        init()
    })

</script>