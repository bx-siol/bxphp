<template>
    <div class="conbar">
        <el-breadcrumb separator="/" style="padding-left:12px;padding-top: 2px;line-height: 40px;display: inline-block;">
            <el-breadcrumb-item>测试</el-breadcrumb-item>
        </el-breadcrumb>
    </div>

    <div class="conbox">
        <div>
            <el-button type="primary" @click="testClick(333,$event)">测试</el-button>
            <br>
            <el-button @click="testStore">测试store</el-button>
            <br>
            <br>
<!--            <Editor esn="editor1" ref="editor1" :height="300">
                <div style="color: #42b983;">23232332</div>
            </Editor>-->
            <br>
            <br>
            <Editor2 esn="x233223" content="sdfsdfsd"></Editor2>
            <div>
                <Upload v-model:file-list="picList"></Upload>
            </div>
        </div>
    </div>

    <Pop :visible="popVisible" @cancel="popVisible=false" @save="onSave">
        <div>这是内容区域</div>
    </Pop>

</template>

<script lang="ts" setup>
    import {onMounted, ref} from 'vue'
    import Pop from "../../components/Pop.vue"
    // import Editor from '../../components/Editor.vue'
    import Editor2 from '../../components/Editor2.vue'
    import Upload from '../../components/Upload.vue'
    import {useStore} from "vuex";
    import {getLocalUser} from '../../global/user';

    const store=useStore()

    const testStore=()=>{
        console.log(getLocalUser())
    }

    const editor1=ref()
    const popVisible=ref(false)

    const onSave=()=>{
        console.log('onSave')
        popVisible.value=false
    }

    const testClick=(p1:any,ev:MouseEvent)=>{
        let el=ev.target as HTMLElement
        el.classList.add('ff22')    //添加class
        el.classList.remove('xx1')  //移除class
        console.log(p1)
        popVisible.value=true
    }

    const picList=ref([
        {name:'001',src:'uploads/2021/10/14/61679d1161ab5.png'},
        {name:'002',src:'uploads/2021/10/14/61679d1161ab5.png'},
        {name:'003',src:'uploads/2021/10/14/61679d1161ab5.png'}
    ])

    onMounted(()=>{
        setTimeout(()=>{store.dispatch('loadingFinish');},store.state.loadingTime)
    })
</script>