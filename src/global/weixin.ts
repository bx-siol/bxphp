import store from '../store'
//@ts-ignore
import wx from 'weixin-js-sdk';
import {isWx} from "./common";

interface shareData {
    title?:string,
    desc?:string,
    link?:string,
    imgUrl?:string,
}

//微信分享
export const wxShare=(opt:shareData)=>{
    if(!isWx()){
        return
    }
    let href=location.href.split('#')[0]
    if(href.indexOf("&share=1")==-1){
        if(href.indexOf('?')!=-1){
            href+='&share=1'
        }else{
            href+='?share=1'
        }
    }

    let _opt:shareData={
        title:'标题',
        desc:'描述',
        link:href,
        imgUrl:store.state.config.img_url+'/public/images/share/icon.png'
    }
    Object.assign(_opt,opt)

    //分享好友
    wx.updateAppMessageShareData({
        title:_opt.title,
        desc:_opt.desc,
        link: _opt.link,
        imgUrl: _opt.imgUrl,
        success: function () {
            // console.log('设置成功')
        }
    })

    //分享朋友圈
    wx.updateTimelineShareData({
        title:_opt.title,
        link: _opt.link,
        imgUrl:_opt.imgUrl,
        success: function () {
            // console.log('设置成功')
        }
    })

}