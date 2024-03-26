<template>
    <el-container style="height: 100%;background: #f2f2f2;">
        <!--侧边栏-->
        <el-aside :width="menuWidth" style="overflow-x: hidden;background: #ffffff;border: 1px solid #ebeef5;">
            <el-scrollbar height="100%" view-style="overflow-x:hidden;">
                <el-affix :offset="0.1" v-if="!isShrink">
                    <div @click="onTopSetClick({ path: '/index', name: '统计' })"
                        style="background: #ffffff;height: 50px;line-height: 50px;padding: 0 20px;cursor: default;font-weight: bold;border-bottom: 1px solid #f2f2f2;box-sizing:border-box;">
                        <i class="el-icon-s-home"></i>{{ store.state.config.name }}
                    </div>
                </el-affix>

                <!--菜单-->
                <el-menu :default-openeds="store.state.user.open_menus" :default-active="active" @select="onMenuSelect"
                    style="border: 0;">
                    <el-sub-menu :index="vo.path" v-for="(vo, idx) in store.state.user.menus">
                        <template #title><i :class="vo.ico"></i>{{ vo.name }}</template>
                        <el-menu-item-group>
                            <template #title>分组</template>
                            <el-menu-item :index="voo.path" v-for="(voo, idx2) in vo.sub_node"
                                @click="onMenuClick($event, voo)">{{ voo.name
                                }}</el-menu-item>
                        </el-menu-item-group>
                    </el-sub-menu>
                </el-menu>
            </el-scrollbar>
        </el-aside>

        <el-container>
            <!--头部-->
            <el-header style="background: #ffffff;line-height: 50px;padding: 0 10px;" height="50px">
                <el-row>
                    <el-col style="text-align: right;overflow: auto;height: 50px;">
                        <el-button type="danger" size="small" @click="clearCache">清理缓存</el-button>
                        &nbsp;&nbsp
                        <el-dropdown>
                            <div style="cursor: pointer;height: 30px;">
                                <span v-if="!store.state.config.is_mobile">{{ store.state.user.nickname }}</span>
                                <el-avatar shape="circle" :size="40" :src="imgFlag(store.state.user.headimgurl)"
                                    style="vertical-align: middle;position: relative;top:-2px;"></el-avatar>
                            </div>
                            <template #dropdown>
                                <el-dropdown-menu>
                                    <el-dropdown-item icon="el-icon-warning-outline"
                                        @click="onTopSetClick({ path: '/sys/profile', name: '基本资料' })">基本资料</el-dropdown-item>
                                    <el-dropdown-item icon="el-icon-lock"
                                        @click="onTopSetClick({ path: '/sys/safety', name: '安全设置' })">安全设置</el-dropdown-item>
                                    <el-dropdown-item divided icon="el-icon-switch-button"
                                        @click="onLogout">退出登录</el-dropdown-item>
                                </el-dropdown-menu>
                            </template>
                        </el-dropdown>
                    </el-col>
                </el-row>
                <div style="position: absolute;top:0px;vertical-align: middle;">
                    <el-icon @click="onShrink" style="margin-right: 20px;cursor: pointer;">
                        <arrow-left v-if="!isShrink" style="width: 30px;height: 23px;vertical-align: middle;" />
                        <arrow-right v-else style="width: 30px;height: 23px;vertical-align: middle;" />
                    </el-icon>
                    <a :href="h5url" target="_blank"><img src="../assets/images/web.png"
                            style="height: 20px;vertical-align: middle;" /></a>
                </div>
            </el-header>

            <!--内容区域-->
            <el-main style="height: 100%;width: 100%;padding: 0;background: #f2f2f2;overflow: hidden;">


                <el-scrollbar height="100%" v-loading="loadingCpu" always>
                    <div style="background: #ffffff; ">
                        <el-tabs v-model="activeTab" @tab-click="handleTabClick" @edit="removeTab" type="card" closable>
                            <!-- <el-tab-pane v-for="item in tabs" :key="item.name" :label="item.title" :name="item.name" /> -->
                            <el-tab-pane v-for="item in tabs" :key="item.name" :label="item.title" :name="item.name">

                            </el-tab-pane>
                        </el-tabs>
                        <router-view v-if="showView" />
                    </div>
                </el-scrollbar>
            </el-main>

        </el-container>
    </el-container>
</template>

<script lang="ts" setup>
import { ref, onMounted, computed, nextTick, watch } from 'vue'
import http from "../global/network/http";
import { getSrcUrl, _alert, getConfig, setConfig } from "../global/common";
import { useRoute, useRouter } from "vue-router";
import { useStore } from "vuex";
import { ElMessageBox } from "element-plus";
import { doLogout, flushUserinfo, getUserinfo, setLocalUser } from "../global/user";
import { ArrowLeft, ArrowRight } from "@element-plus/icons";

import { ElTabPane, ElTabs } from 'element-plus';
// import 'element-plus/lib/theme-chalk/el-tabs.css';
// import 'element-plus/lib/theme-chalk/el-tab-pane.css';

const route = useRoute();
const router = useRouter();

const tabs = ref([
    {
        title: '首页',
        name: '首页',
        path: '/index',
    }
]);

const activeTab = ref(route.name);

const handleTabClick = (tab: any) => {
    updateActive({ path: null, name: tab.paneName })
    router.push({ name: tab.paneName })
};

const addTab = (routeName: string, routePath: string) => {
    const tabExists = tabs.value.some(tab => tab.name === routeName);
    if (!tabExists) {
        tabs.value.push({
            title: routeName,
            name: routeName,
            path: routePath,
        });
    }
    activeTab.value = routeName;
    router.push({ path: routePath })
    reloadView()
};

watch(() => route.name, (newRouteName) => {
    newRouteName && addTab(newRouteName as string, route.path);
});

const removeTab = (targetName: string) => {
    tabs.value = tabs.value.filter(tab => tab.name !== targetName);
    if (activeTab.value === targetName) {
        const nextTab = tabs.value[tabs.value.length - 1] || tabs.value[0];
        router.push(nextTab.path);
    }
};


const emit = defineEmits(['loadingClose'])

let isRequest = false

const store = useStore()

const menuWidth = ref('170px')
const isShrink = ref(false)
const onShrink = () => {
    if (isShrink.value) {
        menuWidth.value = '170px'
    } else {
        menuWidth.value = '0px'
    }
    isShrink.value = !isShrink.value
}

const showView = ref(true)
const loadingCpu = computed(() => {
    return store.state.loading as Boolean
})


//更新active
const updateActive = (activeInfo: any) => {
    let config = getConfig()
    if (config) {
        config.active = {
            path: activeInfo.path,
            name: activeInfo.name
        }
        setConfig(config)
    }
}

//重载视图
const reloadView = () => {
    showView.value = false
    setTimeout(() => {
        showView.value = true
    }, 300)
    store.dispatch('loadingStart')
}
const h5url = ref('/');
const active = computed(() => {
    //初始化时没有设置active数据
    if (!store.state.config.active.path) {
        if (route.name) {
            let routeName = route.name.toString().toLocaleLowerCase()
            let nameArr = routeName.split('_')
            let topName = nameArr[0]
            for (let i in store.state.user.menus) {
                let topItem = store.state.user.menus[i]
                if (topItem.nkey.toLocaleLowerCase() == topName) {
                    for (let j in topItem.sub_node) {
                        let item = topItem.sub_node[j]
                        if (item.nkey.toLocaleLowerCase() == routeName) {
                            updateActive({ path: route.path, name: item.name })
                            break
                        }
                    }
                    break
                }
            }
        }
        return route.path
    }
    return store.state.config.active.path
})

//子菜单点击
const onMenuClick = (ev: any, item: any) => {
    // console.log(ev, item); 
    updateActive({ path: ev.index, name: item.name })
    addTab(item.name, item.path);
}

//子菜单切换-每次点击都会执行
const onMenuSelect = (ev: string) => { }

//顶部点击设置
const onTopSetClick = (item: any) => {
    updateActive({ path: item.path, name: item.name })
    reloadView()
    router.push({ path: item.path })
}

//退出登录
const onLogout = () => {
    ElMessageBox.confirm('您确定要退出登录吗？', '系统提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        iconClass: 'el-icon-warning-outline'
    }).then(() => {
        if (isRequest) {
            return
        }
        isRequest = true
        http({
            url: 'a=logout'
        }).then((res: any) => {
            if (res.code != 1) {
                isRequest = false
                _alert(res.msg)
                return
            }
            doLogout()
            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => { }
            })
        })

    }).catch(() => {
        // alert('取消')
    })
}

//清理缓存
const clearCache = () => {
    ElMessageBox.confirm('您确定要清理缓存吗？', '系统提示', {
        confirmButtonText: '确定',
        cancelButtonText: '取消',
        type: 'warning'
    }).then(() => {
        http({
            url: 'a=clearCache'
        }).then((res: any) => {
            flushUserinfo('', () => {
                _alert({
                    type: 'success',
                    message: res.msg,
                    onClose: () => { }
                })
            })
        })
    }).catch(() => {
        //取消
    })
}

const imgFlag = (src: string) => {
    return getSrcUrl(src)
}

onMounted(async () => {
    emit('loadingClose')
    if (route.path != store.state.config.active.path) {
        updateActive({ path: route.path, name: '首页' })
    }
    var cf = getConfig();
    //console.log(cf);
    h5url.value = cf.img_url;
})

</script>

<style>
.el-menu-item-group .el-menu-item-group__title {
    display: none;
}

/*.el-dropdown-menu__item a{color: #606266;text-decoration: none;}*/
/*.el-dropdown-menu__item:hover a{color: #66b1ff;}*/
.el-sub-menu__title i:first-child:before {
    position: relative;
    top: -1px;
}

.el-sub-menu__title i:last-child:before {
    position: relative;
}
</style>