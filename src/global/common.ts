import store from '../store'
import router from "../router";
import { Toast, Dialog, ImagePreview } from "vant";
 
import Config from "./interface/config";
import ClipboardJS from "clipboard";

export const _alert = (options: any, callback: any = null, type: number = 0) => {

    var message = '';
    if (typeof (options) == 'string') {
        message = options;
    } else {
        message = options.message ?? "Loading failed. Please try again later"
    }
    switch (type) {
        case 1://加载中
            Toast.loading({
                duration: 3000,
                className: 'toastBox',
                message: message,
                forbidClick: true,
                loadingType: 'spinner',
                onClose: () => {
                    if (callback != null) callback()
                }
            });
            break;
        case 2://成功
            Toast.success({
                duration: 3000,
                className: 'toastBox',
                message: message,
                forbidClick: true,
                loadingType: 'spinner',
                onClose: () => {
                    if (callback != null) callback()
                }
            });
            break;
        case 3://失败
            Toast.fail({
                duration: 3000,
                className: 'toastBox',
                message: message,
                forbidClick: true,
                loadingType: 'spinner',
                onClose: () => {
                    if (callback != null) callback()
                }
            });
            break;
        default://提示
            Toast({
                closeOnClick: true,
                overlay: true,
                closeOnClickOverlay: true,
                className: 'toastBox',
                overlayClass: 'van-overlay',
                message: message,
                transition: "bounce",
                loadingType: 'spinner',
                duration: 3000,
                onClose: () => {
                    if (callback != null) callback()
                }
            });
            break;
    }
}
// export const _alert = (options: any) => {
//     if (typeof (options) == 'string') {
//         options = lang(options)
//         Toast({
//             message: options,
//             className: 'toastBox',
//             duration: 3500,
//         })
//     } else {
//         let def = {
//             message: 'System Upgrade',
//             // icon:'info-o',
//             overlay: true,
//             overlayStyle: { background: 'rgba(0,0,0,0.4)' },
//             className: 'toastBox',
//             duration: 3000,
//             closeOnClick: true,
//             closeOnClickOverlay: true,
//             transition: 'slide-enter',
//             onOpened: () => { },
//             onClose: () => { }
//         }
//         let opt = Object.assign(def, options)
//         opt.message = lang(opt.message)
//         Toast(opt);
//     }
// }

export const showImg = (src: string) => {
    Dialog.alert({
        message: '<img src="' + src + '" style="width: 100%;"/>',
        confirmButtonText: 'close',
        // confirmButtonColor:'#07c160',
        allowHtml: true,
        closeOnClickOverlay: true
    }).catch(() => { })
}

//srcs可以是单个资源，也可以是多个资源的数组
//startPosition 当srcs是多个资源时，指定开始预览的索引
export const imgPreview = (srcs: string | string[], startPosition?: number) => {
    let urls = []
    if (typeof srcs == 'string') {
        srcs = getSrcUrl(srcs)
        urls.push(srcs)
        startPosition = 0
    } else {
        for (let i in srcs) {
            urls.push(getSrcUrl(srcs[i]))
        }
    }
    ImagePreview(urls, startPosition)
}

export const getZero = (data: any) => {
    for (let i in data) {
        if (typeof data[i] == 'string') {
            data[i] = ''
        } else if (typeof data[i] == 'number') {
            data[i] = 0
        } else if (data[i] === null || data[i] === undefined) {
            data[i] = ''
        } else {
            if (data[i] instanceof Array) {
                data[i] = []
            } else {
                data[i] = {}
            }
        }
    }
    return data
}

//获取资源全路径
// export const getSrcUrl = (path: string): string => {
//     if (!path) {
//         return ''
//     }
//     path = path.replace(/^\/|\/$/g, '');
//     let url = store.state.config.img_url + '/' + path
//     return url
// }
export const getSrcUrl = (path: string, img: number): string => {
    if (!path) {
        return ''
    }
    path = path.replace(/^\/|\/$/g, ''); 
    let url = location.origin + '/' + path
    
    return url
}

export const goRoute = (target: string | object) => {
    if (typeof target == 'string') {
        router.push({ name: target })
    } else {
        router.push(target)
    }
}

//复制
export const copy = (el: HTMLElement, options?: any) => {
    let clipboard = new ClipboardJS(el, options);
    clipboard.on('success', function (e) {
        _alert('Copy succeeded')
        e.clearSelection()
    });
}

//获取本地存储配置信息
export const getConfig = (): Config | null => {
    let cnf: Config | null = {}
    let cnfJson = window.localStorage.getItem('config')
    try {
        if (cnfJson) {
            cnf = JSON.parse(cnfJson)
            if (!cnf || !cnf.img_url) {
                cnf = null
            }
        }
    } catch (e) {

    }
    if (cnf && !cnf.tab) {
        cnf.tab = 'first'
    }
    return cnf
}

//设置本地存储配置信息
export const setConfig = (cnf: Config) => {
    let config = getConfig()
    if (!config) {
        config = cnf
    } else {
        Object.assign(config, cnf)
    }
    window.localStorage.setItem('config', JSON.stringify(config))
    store.commit('setConfig', config)
}

//更新本地存储配置
export const updateStoreConfig = (obj: Config) => {
    let config = getConfig()
    if (config) {
        for (let i in obj) {
            config[i] = obj[i]
        }
        setConfig(config)
    }
}

export const clearLocalConfig = () => {
    window.localStorage.removeItem('config')
    store.commit('setConfig', {})
}

//检测是否是邮箱
export const isEmail = (email: string): Boolean => {
    if (!email) {
        return false
    }
    let reg = new RegExp("^[a-z0-9]+([._\\-]*[a-z0-9])*@([a-z0-9]+[-a-z0-9]*[a-z0-9]+.){1,63}[a-z0-9]+$");
    return reg.test(email)
}

export const isWx = () => {
    let ua = window.navigator.userAgent.toLowerCase()
    let match = ua.match(/MicroMessenger/i)
    if (match === null) {
        return false
    }
    return true
}

//翻译
export const lang = (str: string) => {
    if (!store.state.config.language || !str) {
        return str
    }
    let langStr = store.state.config.language[str]
    if (!langStr) {
        return str
    }
    return langStr
}

export const cutOutNum = (num, decimals) => {
    if (isNaN(num) || (!num && num !== 0)) {
        return '--'
    }
    // 默认为保留的小数点后两位
    let dec = decimals ? decimals : 2
    let tempNum = Number(num)
    let pointIndex = String(tempNum).indexOf('.') + 1 // 获取小数点的位置 + 1
    let pointCount = pointIndex ? String(tempNum).length - pointIndex : 0 // 获取小数点后的个数(需要保证有小数位)
    // 源数据为整数或者小数点后面小于decimals位的作补零处理
    if (pointIndex === 0 || pointCount <= dec) {
        let tempNumA = tempNum
        if (pointIndex === 0) {
            tempNumA = `${tempNumA}.`
            for (let index = 0; index < dec - pointCount; index++) {
                tempNumA = `${tempNumA}0`
            }
        } else {
            for (let index = 0; index < dec - pointCount; index++) {
                tempNumA = `${tempNumA}0`
            }
        }
        return tempNumA
    }
    let realVal = ''
    // 截取当前数据到小数点后decimals位
    realVal = `${String(tempNum).split('.')[0]}.${String(tempNum)
        .split('.')[1]
        .substring(0, dec)}`
    // 判断截取之后数据的数值是否为0
    if (realVal == 0) {
        realVal = 0
    }
    return realVal
} 