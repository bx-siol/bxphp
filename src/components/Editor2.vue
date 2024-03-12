<template>
    <div :style="{width:width,border:'1px solid #dedede'}">
        <div :id="'toolbar_'+esn"></div>
        <div :id="esn" :style="{height:height+'px',borderTop:'1px solid #dedede'}"></div>
    </div>
</template>

<script lang="ts" setup>
    import {ref,onMounted,useSlots,nextTick} from 'vue';
    import '@wangeditor/editor/dist/css/style.css';
    import { createEditor, createToolbar, IEditorConfig, IDomEditor,Toolbar,IToolbarConfig} from '@wangeditor/editor';
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
        },
        placeholder:{type:String,default:'请输入内容'}
    })

    const store=useStore()
    const slots=useSlots()
    const hasSlot=(slots.default&&slots.default()[0].children)?true:false

    const editorConfig: Partial<IEditorConfig> = {
        MENU_CONF:{
            uploadImage:{
                server: '/api/?a=upload',
                maxFileSize: 100 * 1024 * 1024, // 100M
                allowedFileTypes: ['image/*'],
                timeout: 500 * 1000, // 500 秒
                // 小于该值就插入 base64 格式（而不上传），默认为 0
                //base64LimitSize: 5 * 1024, // 5kb
                headers:{
                    'X-Requested-With': 'XMLHttpRequest',
                    'Token': store.state.token
                },
                customInsert(res: any, insertFn: any) {
                    if(res.code!=1){
                        _alert(res.msg)
                        return
                    }
                    let alt=''
                    let href=''
                    insertFn(getSrcUrl(res.data.src), alt, href)
                },
            }
        }
    }
    editorConfig.placeholder = props.placeholder

    editorConfig.onChange = (editor: IDomEditor) => {
        // 当编辑器选区、内容变化时，即触发
        //console.log('content', editor.children)
        //console.log('html', editor.getHtml())
    }

    let editor:IDomEditor
    let toolbar:Toolbar

    onMounted(()=>{
        // 创建编辑器
        editor = createEditor({
            selector: '#'+props.esn,
            config: editorConfig,
            content: [], // 默认内容，下文有解释
            mode: 'default' // 或者 'simple' ，下文有解释
        })

        const toolbarConfig: Partial<IToolbarConfig> = {
/*            insertKeys: {
                index: 5, // 插入的位置，基于当前的 toolbarKeys
                keys: ['menu-key1']
            }*/
        }

        // 创建工具栏
        toolbar = createToolbar({
            editor,
            config: toolbarConfig,
            selector: '#toolbar_'+props.esn,
            mode: 'default' // 或者 'simple' ，下文有解释
        })

        nextTick(()=>{
            if(props.content){
                setTimeout(()=>{
                    setHtml(props.content)
                },1)
            }
        })
    })

    //设置编辑器的内容
    const setHtml=(htmlStr:string)=>{
        if(editor){
            editor.dangerouslyInsertHtml(htmlStr)
        }
    }

    //获取编辑器的内容
    const getHtml=():string=>{
        let html=''
        if(editor){
            html=editor.getHtml() as string
        }
        return html
    }

    const clear=()=>{
        if(editor){
            editor.clear()
        }
    }

    //对外暴露方法
    defineExpose({
        clear,
        setHtml,
        getHtml
    })

    /*
    const editor=ref<Editor>()
    //editor.value.destroy()

    const init=()=>{
        editor.value = new Editor('#'+props.esn)
        editor.value.config.uploadImgServer = '/api/?a=upload'
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

    onMounted(()=>{
        init()
    })

     */

</script>