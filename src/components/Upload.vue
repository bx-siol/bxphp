<template>
    <div class="myUploadBox">
        <div class="upload-item" v-for="(item, idx) in fileList"
            :style="{ width: width, height: height, overflow: needText ? 'visible' : 'hidden' }">
            <el-image style="width: 100%;height: 100%;" hide-on-click-modal :preview-src-list="fileListPreviewCpu"
                :src="imgFlag(item.src)" fit="cover">
                <template #error>
                    <div style="display: table;width: 100%;" :style="{ height: height }">
                        <div
                            style="word-break: break-all;display: table-cell;vertical-align: middle;text-align: center;width: 100%;">
                            <template v-if="item.oriName">{{ item.oriName }}</template>
                            <template v-else-if="item.name">{{ item.name }}</template>
                            <template v-else>{{ item.src }}</template>
                        </div>
                    </div>
                </template>
            </el-image>
            <div class="upload-item-tool">
                <el-icon :size="20" title="删除" style="background: rgba(0,0,0,0.5);color: #ffffff;"
                    @click.stop="onDel(idx)">
                    <Delete></Delete>
                </el-icon>
            </div>
            <div style="position: absolute;left: 0;bottom: 0;width: 100%;" v-if="needText">
                <el-input size="small" v-model="item.text" autocomplete="off" :placeholder="textPlaceholder"
                    :input-style="{ borderRadius: 0 }" clearable></el-input>
            </div>
        </div>
        <el-upload v-show="fileNum < limit" class="upload-item"
            :style="{ width: width, height: height, marginRight: 0 }" :accept="accept" :show-file-list="false"
            list-type="picture" :http-request="onHttpRequest" :on-progress="onProgress" :on-success="onSuccess"
            :on-remove="onRemove" :on-preview="onPreview" :on-exceed="onExceed" action="/api/?a=upload">
            <div class="my-upload-trigger">
                <el-progress v-show="progressShow" type="circle" :percentage="progressPercent"
                    :width="parseInt(height)" />
                <div style="line-height: 100%;font-size: 30px;font-weight: bold;"><i class="el-icon-plus"
                        :style="{ lineHeight: height }"></i></div>
            </div>
        </el-upload>

    </div>
</template>

<script lang="ts" setup>
import { ref, computed, onMounted } from 'vue'
import { useStore } from "vuex";
import { _alert, getSrcUrl } from "../global/common";
import axios, { AxiosRequestConfig } from "axios";
import { Delete } from '@element-plus/icons'

interface UpFile {
    name: string,
    oriName?: string,
    src: string
}

const store = useStore()
const emit = defineEmits(['update:fileList'])
const props = defineProps({
    accept: {
        type: String,
        default: '.jpg,.jpeg,.png,.bmp'
    },
    limit: {
        type: Number,
        default: 1
    },
    fileList: {
        type: Array,
        default: []
    },
    width: {
        type: String,
        default: '120px'
    },
    height: {
        type: String,
        default: '120px'
    },
    needText: { type: Boolean, default: false },
    textPlaceholder: String
})

//相册计算属性
const fileListPreviewCpu = computed((): string[] => {
    let urls: string[] = [];
    for (let i = 0; i < props.fileList.length; i++) {
        let it = props.fileList[i] as any
        urls.push(imgFlag(it.src))
    }
    return urls
})

const fileNum = computed(() => {
    return props.fileList.length
})

const progressShow = ref(false)
const progressPercent = ref(0)

//上传文件
const onHttpRequest = (file: any) => {
    progressShow.value = true
    progressPercent.value = 0
    let FormDatas = new FormData();
    FormDatas.append('file', file.file);
    let config: AxiosRequestConfig = {
        timeout: 10000,
        method: 'POST',
        headers: {
            'Content-Type': 'multipart/form-data',
            'X-Requested-With': 'XMLHttpRequest',
            'Token': store.state.token
        },
        onUploadProgress: (progressEvent) => {//上传进度
            let num = progressEvent.loaded / progressEvent.total * 100 | 0;  //百分比
            file.onProgress({ percent: num })     //进度条
        }
    }
    axios.post('/api/?a=upload', FormDatas, config).then(res => {
        file.onSuccess(res.data, file)    //上传成功(打钩的小图标)
    })
}

const onProgress = (event: any, file: any, flist: any) => {
    progressPercent.value = event.percent
    if (event.percent >= 100) {
        progressShow.value = false
    }
}

const onSuccess = (res: any, file: any) => {
    if (res.code != 1) {
        _alert(res.msg)
        return
    }
    let flist = props.fileList
    flist.push({
        oriName: file.name,
        name: res.data.name,
        src: res.data.src
    })
    emit('update:fileList', flist)
}

const onRemove = () => { }
const onPreview = () => { }
//图片上传超出个数
const onExceed = (files: any, flist: any) => {
    _alert('图片个数超出限制')
}

//删除项
const onDel = (idx: number) => {
    let flist = props.fileList
    flist.splice(idx, 1)
    emit('update:fileList', flist)
}

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

onMounted(() => {

})

</script>

<style scoped>
.upload-item {
    position: relative;
    display: inline-block;
    margin-right: 10px;
    border: 1px dashed #c0ccda;
    background-color: #fbfdff;
    overflow: hidden;
    font-weight: bold;
    text-align: center;
}

.upload-item-tool {
    height: 20px;
    position: absolute;
    z-index: 2;
    top: -5px;
    right: 0;
}

.my-upload-trigger {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background: transparent;
}
</style>