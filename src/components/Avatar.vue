<template>
    <van-overlay :show="true" v-show="editHeadimgShow">
        <img ref="editHeadimg" :src="imgFlag(editHeadimgUrl)" style="margin: 0 auto;max-width: 100%;max-height: 100%;"/>
        <div style="position: absolute;bottom: 10%;width: 100%;text-align: center;">
            <van-button type="warning" size="small" @click="onCancel">&nbsp;&nbsp;Cancle&nbsp;&nbsp;</van-button>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <van-button type="success" size="small" @click="onCropper">&nbsp;&nbsp;Cropper&nbsp;&nbsp;</van-button>
        </div>
    </van-overlay>

    <MyLoading :show="loadingShow" title="Uploading"></MyLoading>

    <input type="file" ref="fileRef" accept="image/*" mutiple="mutiple" @change="onChange" style="display: none;"/>
</template>

<script lang="ts">
    import {defineComponent,ref,reactive,onMounted,onBeforeUnmount} from 'vue';
    import {Overlay,Button} from 'vant';
    import MyLoading from './Loading.vue';

    export default defineComponent({
        components:{
            MyLoading,
            [Overlay.name]:Overlay,
            [Button.name]:Button,
        }
    })
</script>

<script lang="ts" setup>
    import {_alert, getSrcUrl} from "../global/common";
    import Compressor from "compressorjs";
    import 'cropperjs/dist/cropper.css';
    import Cropper from 'cropperjs';
    import axios, {AxiosRequestConfig} from "axios";
    import {useStore} from "vuex";
    import http from "../global/network/http";
    import { getLocalToken } from "../global/user";
    import {Dialog} from "vant";
    import {doLogout} from "../global/user";

    const fileRef=ref()
    const loadingShow=ref(false)
    const editHeadimgShow=ref(false)
    const editHeadimg=ref()
    const editHeadimgUrl=ref('')
    const cropperHeadimgUrl=ref('')
    let cropper:any=null

    const store=useStore()

    const imgFlag=(src:string)=>{
        return getSrcUrl(src, 1)
    }

    const emit=defineEmits(['success','cancle'])

    //选择文件
    const chooseFile=()=>{
        fileRef.value.click()
    }

    const getAvatar=()=>{
        return cropperHeadimgUrl.value
    }

    defineExpose({
        chooseFile,
        getAvatar
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
                'Token':getLocalToken()
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
            onFileSuccess(file,res)
        })
    }

    const onFileSuccess=(file:File,res:any)=>{
        editHeadimgUrl.value=res.data.src
        editHeadimgShow.value=true
        let image=editHeadimg.value as HTMLImageElement
        image.addEventListener('load',()=>{
            if(cropper){
                cropper.destroy()
            }
            cropper = new Cropper(image, {
                aspectRatio: 1/1,
                viewMode:1,
                guides:false,
                minCropBoxWidth:80,
                minCropBoxHeight:80,
                crop(event) {
                },
            });
        })
    }

    const onCancel=()=>{
        editHeadimgShow.value=false
        emit('cancle')
    }

    const onCropper=()=>{
        if(!cropper){
            return
        }
        let canvas = cropper.getCroppedCanvas();
        let base64=canvas.toDataURL('image/jpeg');
        http({
            url:'a=uploadImg64',
            data:{imgdata:base64}
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            editHeadimgShow.value=false
            cropperHeadimgUrl.value=res.data.src
            emit('success',res.data.src)
        })
    }

</script>