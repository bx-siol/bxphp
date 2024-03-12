import store from '../../store'
import {_alert} from "../common";

export interface WsData {
    act?:String,
    code?:number,
    alert?:number,
    msg?:string,
    data?:any,
    [key:string]:any
}

let ws:WebSocket
let pingTimer:any=null
let pingInterval=10000
let needReConnect=true

//发送数据
export const send=(dt:string|WsData)=>{
    if(!ws||ws.readyState!=1){
        console.log('Wesocket not connected')
        return
    }
    let str=''
    if(typeof dt=='string'||typeof dt=='number'||typeof dt==undefined){
        str=dt.toString()
    }else{
        str=JSON.stringify(dt)
    }
    if(store.state.debug){
        ws.send(str)
    }else{
        let buffer=new Blob([str],{type:'text/plain'});
        let reader=new FileReader();
        reader.readAsArrayBuffer(buffer);
        reader.onload=function(){
            if(reader.result){
                ws.send(reader.result)
            }else{
                console.log('readAsArrayBuffer失败')
            }
        }
    }
}

const ping=()=>{
    if(pingTimer){
        clearInterval(pingTimer)
    }
    pingTimer=setInterval(()=>{
        send('ping')
    },pingInterval)
}

export const createWebsocket=(isTourist?:boolean)=>{
    ws=new WebSocket(store.state.config.ws_url as string)

    ws.onopen=()=>{
        let ldata={
            act:'User/login',
            tourist:isTourist,
            account:'',
            token:''
        }
        if(!isTourist){
            ldata.account=store.state.user.account as string
            ldata.token=store.state.token
        }
        send(ldata)
    }

    ws.onmessage=async (evt:MessageEvent)=>{
        let message:string=''
        if(typeof evt.data=='object'){
            message=await (new Response(evt.data)).text();
        }else{
            message = evt.data
        }
        if(!message||message=='pong'){
            return false
        }
        let data=JSON.parse(message) as WsData
        if(!data||!data.act){
            return false
        }
        if(data.code!=1){
            if(data.alert==1){
                _alert(data.msg)
            }
            return false
        }
        if(data.act=='User/login'){//登录成功
            ping()
            if(data.code==1){
                store.commit('setWsOk',true)
            }
        }else if(data.act=='User/logout'){//退出登录
            needReConnect=false
        }
        //处理动作
        wsAction(data)
    }

    ws.onclose=()=>{
        console.log("Websocket closed")
        if(needReConnect){
            createWebsocket()
        }
    }

    ws.onerror=()=>{}
}

export const useWebsocket=():WebSocket=>{
    return ws
}

let handleWebsocketAct:any

export const handleWebsocket=(callback:(res:WsData)=>void)=>{
    handleWebsocketAct=callback
}

const wsAction=(res:WsData)=>{
    //公共逻辑
    // console.log('公共逻辑...')
    if(typeof handleWebsocketAct=='function'){
        handleWebsocketAct(res)
    }
}

