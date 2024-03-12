<template>
    <div class="conbar">
        <el-breadcrumb separator="/" style="padding-left:12px;padding-top: 2px;line-height: 40px;display: inline-block;">
            <el-breadcrumb-item>{{store.state.config.active.name}}</el-breadcrumb-item>
        </el-breadcrumb>
    </div>
    <div class="conbox">
        <el-row>
            <el-col :span="10">
                <el-form :style="{width:configForm.width}" :label-width="configForm.labelWidth">
                    <el-form-item label="邀请链接：">
                        {{ulink.url}}
                        <el-button size="small" type="success" ref="copyRef">复制</el-button>
                    </el-form-item>
                    <el-form-item label="二维码：">
                        <el-image :src="imgFlag(ulink.qrcode)" style="height: 200px;width: 200px;"/>
                    </el-form-item>
                </el-form>
            </el-col>
        </el-row>
        <div style="height: 50px;"></div>
    </div>
</template>

<script lang="ts" setup>
    import {ref,onMounted, reactive, toRefs} from 'vue'
    import http from "../../global/network/http"
    import {_alert, getSrcUrl,copy} from "../../global/common"
    import {useStore} from "vuex";

    const store=useStore()
    let isRequest=false

    const configForm=ref({
        title:'',
        width:'1020px',
        labelWidth:'100px',
    })

    const imgFlag=(src:string)=>{
        return getSrcUrl(src)
    }

    const copyRef=ref()
    const ulink=ref({})

    onMounted(()=>{
        http({
            url: 'c=User&a=ulink',
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            ulink.value=res.data

            //处理复制
            copy(copyRef.value.$el,{
                text:(target:HTMLElement)=>{
                    return res.data.url
                }
            })

            setTimeout(()=>{store.dispatch('loadingFinish');},store.state.loadingTime)
        })
    })
</script>