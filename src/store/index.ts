import { createStore } from 'vuex';
import Config from "../global/interface/config";
import { userInfoInterface } from "../global/interface/user";

let config: Config | null = {}
let user: userInfoInterface | null = {}

export default createStore({
    state: {
        debug: true,
        config: config,
        token: '',
        user: user,
        backurl: '',
        showView: true,
        showLanguage: false,
        wsOk: false,
        newscountc: ''
    },
    mutations: {
        setConfig(state, payload) {
            state.config = payload
        },
        setnewscountc(state, payload) {
            state.newscountc = payload
        },
        setToken(state, payload) {
            state.token = payload
        },
        setUser(state, payload) {
            state.user = payload
        },
        setWsOk(state, payload) {
            state.wsOk = payload
        }
    },
    actions: {
        init(ctx, payload) {
            ctx.commit('setToken', payload.token)
            ctx.commit('setUser', payload.user)
        }
    },
    modules: {

    }
})
