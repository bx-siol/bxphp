import { createStore } from 'vuex';
import Config from "../global/interface/config";
import {userInfoInterface} from "../global/interface/user";

let config:Config|null={}
let user: userInfoInterface|null={}

export default createStore({
    state: {
        debug:true,
        config:config,
        token:'',
        user:user,
        loading:true,
        loadingTime:100,
        wsAction:{},
        wsOk:false
    },
    mutations: {
        loadingStart(state,payload){
            state.loading=true
        },
        loadingFinish(state,payload){
            state.loading=false
        },
        setConfig(state,payload){
            state.config=payload
        },
        setToken(state,payload){
            state.token=payload
        },
        setUser(state,payload){
            state.user=payload
        },
        setWsOk(state,payload){
            state.wsOk=payload
        }
    },
    actions: {
        loadingStart(ctx){
            ctx.commit('loadingStart')
        },
        loadingFinish(ctx){
            ctx.commit('loadingFinish')
        },
        init(ctx,payload){
            ctx.commit('setToken',payload.token)
            ctx.commit('setUser',payload.user)
        }
    },
    modules: {
    }
})
