import router from '../router';
import store from '../store'
import {userInfoInterface} from "./interface/user";
import http from "./network/http";
import {useStore} from "vuex";

//检测权限
export const checkPower=(nkey:string):boolean=>{
    const store=useStore()
    if(store.state.user.nkeys.length<1){
        return false
    }
    let res=false
    for(let i in store.state.user.nkeys){
        if(nkey==store.state.user.nkeys[i]){
            res=true
            break
        }
    }
    return res
}

//获取用户信息
export const getUserinfo=async (params?:{}):Promise<userInfoInterface|any>=>{
    let userInfo=null;
    await http({
        url: 'a=userinfo',
        data: params
    }).then((res:any)=>{
        userInfo=res;
    });
    return userInfo;
}

//刷新用户信息到store
export const flushUserinfo=(token?:string,callback?:any):void=>{
    getUserinfo({is_ht:true,token:token}).then((res:any)=>{
        setLocalUser(res.data)
        if(callback){
            callback(res)
        }
    })
}

//判断是否已经登录
export const isLogin=():userInfoInterface|Boolean=>{
    let token=getLocalToken()
    if(!token){
        return false
    }
    let user=getLocalUser()
    if(!user){
        return false
    }
    return user
}

//执行登录
export const doLogin=(user:userInfoInterface,token:string)=>{
    setLocalToken(token)
    setLocalUser(user)
}

//退出登录
export const doLogout=()=>{
    clearLocalData()
    router.push({name:'Login'})
}

//更新本地用户信息
export const setLocalUser=(payload:userInfoInterface)=>{
    let user=getLocalUser()
    if(!user){
        user=payload
    }else{
        Object.assign(user,payload)
    }
    window.localStorage.setItem('user',JSON.stringify(user))
    store.commit('setUser',user)
    return user
}

export const clearLocalUser=()=>{
    window.localStorage.removeItem('user')
    store.commit('setUser',{})
}

//获取本地存储用户信息
export const getLocalUser=()=>{
    let user:userInfoInterface|null=null
    let userJson=window.localStorage.getItem('user')
    try{
        if(userJson){
            user=JSON.parse(userJson)
            if(!user||!user.account){
                user=null
            }
        }
    }catch(e){

    }
    return user
}

//设置token
export const setLocalToken=(token:string)=>{
    window.localStorage.setItem('token',token)
    store.commit('setToken',token)
    return token
}

export const clearLocalToken=()=>{
    window.localStorage.removeItem('token')
    store.commit('setToken','')
}

//获取token
export const getLocalToken=()=>{
    let token=window.localStorage.getItem('token')
    if(!token){
        token=null
    }
    return token
}

export const clearLocalData=()=>{
    clearLocalToken()
    clearLocalUser()
}