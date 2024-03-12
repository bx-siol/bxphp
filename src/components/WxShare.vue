<template>
<div style="display:none;"></div>
</template>

<script lang="ts">
    import {defineComponent,ref,onMounted} from 'vue'
    //@ts-ignore
    import wx from 'weixin-js-sdk';
    import {useRoute} from "vue-router";
    import {useStore} from "vuex";
    import {isWx} from "../global/common";

    export default defineComponent({
        components:{

        },
        props:{
            shareData:{
                type:Object,
                default:{
                    title:'标题',
                    desc:'描述',
                    link:'',
                    imgUrl:''
                }
            }
        },
        emits:['shareSuccess'],
        setup(props,{emit}){

            const route=useRoute()
            const store=useStore()

            const link=ref(store.state.config.img_url+route.fullPath)
            const icon=ref(store.state.config.img_url+'/public/images/share/icon.png')

            onMounted(()=>{
                if(!isWx()){
                    return
                }
                console.log(props.shareData)
                if(props.shareData.link&&props.shareData.link.length>0){
                    link.value=props.shareData.link
                }

                if(props.shareData.imgUrl&&props.shareData.imgUrl.length>0){
                    icon.value=props.shareData.imgUrl
                }

                wx.ready(function(){

                    //分享好友
                    wx.updateAppMessageShareData({
                        title:props.shareData.title,
                        desc:props.shareData.desc,
                        link: link.value,
                        imgUrl: icon.value,
                        success: function () {
                            // console.log('设置成功')
                        }
                    })

                    //分享朋友圈
                    wx.updateTimelineShareData({
                        title:props.shareData.title,
                        link: link.value,
                        imgUrl: icon.value,
                        success: function () {
                            // console.log('设置成功')
                        }
                    })
                });
            })

            return {

            }
        }
    })
</script>