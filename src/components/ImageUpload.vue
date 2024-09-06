<template>
    <MyLoading :show="loadingShow" title="Uploading"></MyLoading>
    <input type="file" ref="fileRef" accept="image/*" mutiple="mutiple" @change="onChange" style="display: none;width:0;height: 0;"/>
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
    import {defineExpose} from 'vue';
    import {_alert, getSrcUrl} from "../global/common";
    import Compressor from "compressorjs";
    import axios, {AxiosRequestConfig} from "axios";
    import {useStore} from "vuex";
    import http from "../global/network/http";
    import Cropper from "cropperjs";

    const emit=defineEmits(['success'])

    const store=useStore()

    const fileRef=ref()
    const fileUrl=ref('')
    const loadingShow=ref(false)

    //选择文件
    const chooseFile=()=>{
        fileRef.value.click()
    }

    const getFile=()=>{
        return fileUrl.value
    }

    defineExpose({
        chooseFile,
        getFile
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
                'Token':store.state.token
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
        fileUrl.value=res.data.src
        emit('success',res.data.src)
    }

</script>