import axios, { AxiosPromise, AxiosRequestConfig, AxiosResponse } from 'axios';
import qs from 'qs';
import { doLogout, getLocalToken } from "../user";
import { _alert } from "../common";

interface httpResult {
    code: number,
    msg: string,
    data?: any
}

const http = (config: AxiosRequestConfig): AxiosPromise => {
    let url = '/api/?';
    config.url = url + config.url
    const instance = axios.create({
        //baseURL: '/api',
        timeout: 10000,
        method: 'POST',
        transformRequest: [function (data) {
            // 对 data 进行任意转换处理
            return qs.stringify(data);
        }],
        //withCredentials:true,
        //method:'GET',
        //headers:{}
    });

    //请求拦截
    instance.interceptors.request.use(config => {
        //加入验证token
        const token = getLocalToken()
        if (config.headers) {
            config.headers['Token'] = token ? token : ''
            config.headers['Content-Type'] = 'application/x-www-form-urlencoded;charset=utf-8'
            config.headers['X-Requested-With'] = 'XMLHttpRequest'
        }
        return config
    }, error => {
        //return Promise.resolve(error)
        console.log(error)
    })

    //响应拦截
    instance.interceptors.response.use(res => {
        let data = res.data as any
        if (data.code == -98) {
            doLogout()
        }
        return res.data
    }, error => {
        //return Promise.reject(error)
        console.log(error)
    })

    return instance(config);
}

export { http, httpResult }

export default http