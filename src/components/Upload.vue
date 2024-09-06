<template>
    <div class="myUploadBox">
        <div class="myUploadItem" v-for="(item,idx) in fileList" :style="{width:width,height:height,marginRight:spaceRight}">
            <van-image :src="imgFlag(item.src)" @click="onPreview(idx)" fit="cover" width="100%" height="100%">
                <template #error>
                    <template v-if="item.oriName">{{item.oriName}}</template>
                    <template v-else-if="item.name">{{item.name}}</template>
                    <template v-else>{{item.src}}</template>
                </template>
            </van-image>
            <slot name="delete">
                <div class="myUploadItemClose" @click="onDel(idx)">
                    <van-icon name="delete-o" size="16"></van-icon>
                </div>
            </slot>
        </div>

        <slot name="add">
            <div class="myUploadItem" :style="{width:width,height:height}" v-if="fileNum<limit">
                <div style="position: absolute;left: 0;top:0;width: 100%;height: 100%;display: table;">
                    <div style="display: table-cell;vertical-align: middle;text-align: center;">
                        <div>
                            <slot name="icon"><van-icon name="photograph" size="24px" color="#dcdee0"/></slot>
                        </div>
                        <div style="font-size: 0.8rem;">
                            <slot name="notice"></slot>
                        </div>
                    </div>
                </div>
                <input type="file" :accept="accept" mutiple="mutiple" @change="onChange" style="width: 100%;height: 100%;opacity: 0;position: absolute;left: 0;top:0;z-index: 2;"/>
                <div v-show="loadingShow" style="width: 100%;height: 100%;position: absolute;left: 0;top:0;z-index: 3;background: rgba(0,0,0,0.5);text-align: center;display: table;">
                    <div style="display: table-cell;vertical-align: middle;">
                        <van-loading size="20px" color="#ffffff" text-color="#ffffff" :vertical="true" style="">Uploading</van-loading>
                    </div>
                </div>
            </div>
        </slot>

    </div>
</template>

<script lang="ts">
    import {defineComponent} from 'vue';
    import {Uploader,Icon,Image,Loading} from 'vant';

    export default defineComponent({
        components:{
            [Uploader.name]:Uploader,
            [Image.name]:Image,
            [Icon.name]:Icon,
            [Loading.name]:Loading,
        }
    })
</script>

<script lang="ts" setup>
    import {ref,computed,onMounted} from 'vue'
    import {useStore} from "vuex";
    import {ImagePreview} from 'vant';
    import {_alert, getSrcUrl} from "../global/common";
    import axios, { AxiosRequestConfig } from "axios";
    import Compressor from "compressorjs";
    import { getLocalToken } from "../global/user";
    import http from "../global/network/http";

    interface UpFile{
        name:string,
        oriName?:string,
        src:string
    }

    const store=useStore()
    const emit=defineEmits(['update:fileList','success'])
    const props=defineProps({
        accept:{
            type:String,
            default:'image/*'
        },
        limit:{
            type:Number,
            default:1
        },
        fileList:{
            type:Array,
            default:[]
        },
        width:{
            type:String,
            default:'80px'
        },
        height:{
            type:String,
            default:'80px'
        },
        spaceRight:{type:String,default:'0.3rem'}
    })

    const loadingShow=ref(false)

    const fileNum=computed(()=>{
        return props.fileList.length
    })

    const onChange=(ev:Event)=>{
        let files=ev.target.files as File[]
        if(files.length<1){
            return
        }
        loadingShow.value=true
        new Compressor(files[0], {
            quality:0.6,
            success:(result)=>{
                let el=ev.target as HTMLInputElement
                el.value=''
                let file = new window.File([result], result.name, {type: result.type})
                onHttpRequest(file)
            },
            error(err:any) {
                console.log(err.message);
            }
        })
    }

    //上传文件
    const onHttpRequest=(file:File)=>{
        let FormDatas = new FormData();
        FormDatas.append('file',file,file.name);
        let config: AxiosRequestConfig={
            timeout: 10000,
            method:'POST',
            headers:{
                'Content-Type':'multipart/form-data',
                'X-Requested-With':'XMLHttpRequest',
                'Token': getLocalToken()
            },
            onUploadProgress: (progressEvent) => {//上传进度
                let num = progressEvent.loaded / progressEvent.total * 100 | 0;  //百分比
            }
        }
        axios.post('/api/?a=upload',FormDatas,config).then(result => {
            loadingShow.value=false
            let res=result.data
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            let flist=props.fileList
            flist.push({
                oriName:file.name,
                name:res.data.name,
                src:res.data.src
            })
            emit('update:fileList',flist)
            emit('success',file,res)
        })
    }

    //删除项
    const onDel=(idx:number)=>{
        let flist=props.fileList
        flist.splice(idx,1)
        emit('update:fileList',flist)
    }

    const imgFlag=(src:string)=>{
        return getSrcUrl(src, 1)
    }

    const onPreview=(idx:number)=>{
        let urls=[]
        for(let i in props.fileList){
            urls.push(getSrcUrl(props.fileList[i].src))
        }
        ImagePreview({
            images:urls,
            startPosition:idx,
            closeable: true
        })
    }

    onMounted(()=>{

    })

</script>

<style scoped>
    .myUploadBox{height: auto;overflow: hidden;}
    .myUploadBox .myUploadAdd{}
    .myUploadBox .myUploadItem{
        position: relative;background-color: #f7f8fa;border: 0px dotted #f7f8fa;
        display: inline-block;box-sizing: border-box;overflow: hidden;;
        margin-bottom: 0.25rem;
        word-break: break-all;
        cursor: pointer;
    }
    .myUploadItemClose{position: absolute;right: 0;top: 0;background: rgba(0,0,0,0.5);color: white;width: 20px;height: 20px;line-height: 23px;text-align: center;}

</style>