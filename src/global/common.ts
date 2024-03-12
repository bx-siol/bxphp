import { ElMessage, ElMessageBox } from 'element-plus'
import store from '../store'
import Config from "./interface/config";
import ClipboardJS from "clipboard";

export const _alert = (options: any) => {
    ElMessage.closeAll()
    if (typeof (options) == 'string') {
        ElMessage({
            type: 'warning',
            message: options
        })
    } else {
        ElMessage(options)
    }
}

export const showImg = (src: string) => {
    ElMessageBox.close()
    let html = '<div style="text-align: center;">'
    html += '<img src="' + src + '" style="max-width: 90%;max-height: 90%;"/>'
    html += '</div>'
    ElMessageBox.alert(html, '图片预览', {
        dangerouslyUseHTMLString: true,
        customClass: 'el-message-box-showimg',
        confirmButtonText: '关闭'
    }).catch(() => {

    })
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
export const getSrcUrl = (path: string): string => {
    if (!path) {
        return ''
    }
    //let url = store.state.config.img_url + '/' + path
    let url = location.origin + '/' + path
    return url
}

//复制
export const copy = (el: HTMLElement, options?: any) => {
    let clipboard = new ClipboardJS(el, options);
    clipboard.on('success', function (e) {
        _alert('复制成功')
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
            if (!cnf) {
                cnf = null
            }
        }
    } catch (e) {

    }
    if (cnf) {
        if (!cnf.tab) {
            cnf.tab = 'first'
        }
        if (!cnf.active) {
            cnf.active = {}
        }
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

export const clearLocalConfig = () => {
    window.localStorage.removeItem('config')
    store.commit('setConfig', {})
}