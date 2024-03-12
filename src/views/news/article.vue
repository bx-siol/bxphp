<template>
<Page url="c=News&a=article">
    <template #btn="myScope">
        <el-button v-if="power.update" type="success" size="small" icon="el-icon-plus" @click="add(myScope)">添加文章</el-button>
    </template>

    <template #search="{params,tdata,doSearch}">
        <el-select style="width: 180px;margin-left: 10px;" v-model="params.s_cid" placeholder="所属分类">
            <el-option
                    key="0"
                    label="所属分类"
                    value="0">
            </el-option>
            <el-option
                    v-for="(item,idx) in tdata.category_tree"
                    :key="item.id"
                    :label="item.name"
                    :value="item.id">
            </el-option>
        </el-select>
    </template>

    <template #table="myScope">
        <el-table-column prop="id" label="ID" width="80"></el-table-column>
        <el-table-column prop="title" label="标题" min-width="200"></el-table-column>
        <el-table-column prop="cname" label="文章分类" width="160"></el-table-column>
        <el-table-column prop="label" label="标签" min-width="120"></el-table-column>
<!--        <el-table-column prop="ndesc" label="文章摘要" min-width="300"></el-table-column>-->
        <el-table-column prop="cover" label="封面图" width="80" class-name="imgCellBox">
            <template #default="scope">
                <el-image
                        style="width: 55px;height: 40px;vertical-align: middle;"
                        fit="cover"
                        :src="imgFlag(scope.row.cover)"
                        hide-on-click-modal
                        :preview-src-list="[imgFlag(scope.row.cover)]">
                </el-image>
            </template>
        </el-table-column>
        <el-table-column prop="url" label="跳转地址" width="120" class-name="imgCellBox">
            <template #default="{row}">
                <template v-if="row.url">
                    <el-button size="small" @click="onClickUrl(row)">点击查看</el-button>
                </template>
                <template v-else>/</template>
            </template>
        </el-table-column>
        <el-table-column prop="create_time" label="创建时间" width="120"></el-table-column>
        <el-table-column prop="publish_time_flag" label="发布时间" width="160"></el-table-column>
        <el-table-column prop="is_recommend_flag" label="推荐" width="70"></el-table-column>
        <el-table-column prop="status_flag" label="状态" width="70"></el-table-column>
        <el-table-column label="操作" width="160">
            <template #default="scope">
                <el-popconfirm
                        confirmButtonText='确定'
                        cancelButtonText='取消'
                        icon="el-icon-warning"
                        iconColor="red"
                        title="您确定要进行删除吗？"
                        @confirm="del(scope.$index,scope.row,myScope)" v-if="power.delete">
                    <template #reference>
                        <el-button type="danger" size="small">删除</el-button>
                    </template>
                </el-popconfirm>
                <el-button size="small" v-if="power.update" @click="edit(scope.$index,scope.row)">编辑</el-button>
            </template>
        </el-table-column>
    </template>

    <template #summary="{tdata}">
        <span>记录数：{{tdata.count}}</span>
    </template>

    <!--弹出层-->
    <template #layer="{tdata}">
        <el-dialog :title="configForm.title" v-model="configForm.visible" :close-on-click-modal="false" :width="configForm.width" :top="configForm.top" @opened="dialogOpened" @closed="onDialogClosed">
            <el-form :label-width="configForm.labelWidth">
                <el-form-item label="所属分类">
                    <el-select style="width: 200px;" v-model="dataForm.cid" placeholder="选择分类">
                        <el-option
                                key="0"
                                label="选择分类"
                                value="0">
                        </el-option>
                        <el-option
                                v-for="(item,idx) in tdata.category_tree"
                                :key="item.id"
                                :label="item.name"
                                :value="item.id">
                        </el-option>
                    </el-select>
                </el-form-item>
                <el-form-item label="标题">
                    <el-input v-model="dataForm.title" autocomplete="off" placeholder=""></el-input>
                </el-form-item>
                <el-form-item label="跳转地址">
                    <el-input v-model="dataForm.url" autocomplete="off" placeholder="填写地址则直接跳转"></el-input>
                </el-form-item>
                <el-form-item label="标签" style="margin-bottom: 0;">
                    <el-input v-model="dataForm.label" autocomplete="off" placeholder=""></el-input>
                    <span>多个之间使用逗号分隔</span>
                </el-form-item>
                <el-form-item label="作者">
                    <el-input v-model="dataForm.author" autocomplete="off" placeholder=""></el-input>
                </el-form-item>
                <el-form-item label="摘要">
                    <el-input type="textarea" v-model="dataForm.ndesc" autocomplete="off" rows="3"></el-input>
                </el-form-item>
                <el-form-item label="发布时间" style="margin-bottom: 0;">
                    <el-date-picker
                            v-model="dataForm.publish_time_flag"
                            type="datetime"
                            placeholder="请选择"
                    >
                    </el-date-picker>
                </el-form-item>
                <el-form-item label="推荐" style="margin-bottom: 0;">
                    <el-radio-group v-model="dataForm.is_recommend">
                        <el-radio :label="1">是</el-radio>
                        <el-radio :label="0">否</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="状态" style="margin-bottom: 0;">
                    <el-radio-group v-model="dataForm.status">
                        <el-radio :label="1">待发布</el-radio>
                        <el-radio :label="2">已发布</el-radio>
                    </el-radio-group>
                </el-form-item>
                <el-form-item label="封面图">
                    <Upload v-model:file-list="coverList" width="105px" height="80px"></Upload>
                </el-form-item>
                <el-form-item label="文章内容">
                    <Editor :height="400" ref="editor"></Editor>
<!--                    <Editor2 :height="400" ref="editor"></Editor2>-->
                </el-form-item>
            </el-form>
            <template #footer>
        <span class="dialog-footer">
            <input type="hidden" v-model="dataForm.id"/>
            <el-button @click="configForm.visible = false">取消</el-button>
            <el-button type="primary" @click="save">保存</el-button>
        </span>
            </template>
        </el-dialog>
    </template>

</Page>
</template>

<script lang="ts" setup>
    import {ref,onMounted, reactive,getCurrentInstance,nextTick} from 'vue';
    import http from "../../global/network/http";
    import {getZero, _alert, getSrcUrl, showImg} from "../../global/common";
    import { checkPower } from '../../global/user';
    import Page from "../../components/Page.vue";
    import Editor from "../../components/Editor.vue";
    import Editor2 from "../../components/Editor2.vue";
    import Upload from '../../components/Upload.vue';

    const insObj=getCurrentInstance()
    const editor=ref()
    let pageScope:any

    let isRequest=false

    //权限控制
    const power=reactive({
        update:checkPower('News_article_update'),
        delete:checkPower('News_article_delete')
    })

    const configForm=reactive({
        title:'',
        width:'1024px',
        labelWidth:'100px',
        top:'2%',
        visible:false,
        isEdit:false
    })

    const actItem=ref<any>()
    const dataForm=reactive<any>({
        id:0,
        cid:'',
        title:'',
        publish_time_flag:'',
        is_recommend:0,
        status:2,
        author:'',
        url:'',
        label:'',
        ndesc:'',
        cover:'',
        content:''
    })
    const coverList=ref<any>([])

    const add=(myScope:any)=>{
        pageScope=myScope
        coverList.value=[]
        getZero(dataForm)
        dataForm.cid=''
        dataForm.status=2
        configForm.visible=true
        configForm.title='添加文章'
        configForm.isEdit=false
    }

    const edit=(idx:number,item:any)=>{
        coverList.value=[{src:item.cover}]
        actItem.value=item
        for(let i in dataForm){
            dataForm[i]=item[i]
        }
        configForm.visible=true
        configForm.title='编辑文章'
        configForm.isEdit=true
    }

    //弹层打开后回调
    const dialogOpened=()=>{
        //重要：打开后重新更新引用
        if(insObj){
            editor.value=insObj.refs['editor']
        }
        nextTick(()=>{
            if(configForm.isEdit) {
                editor.value.setHtml(actItem.value.content)
            }else{
                editor.value.clear()
            }
        })
    }

    //弹层关闭后
    const onDialogClosed=()=>{
        editor.value.clear()
    }

    const save=()=>{
        if(!dataForm.cid){
            _alert('请选择文章分类')
            return
        }
        if(!dataForm.title){
            _alert('请填写文章标题')
            return
        }
        if(coverList.value[0]){
            dataForm.cover=coverList.value[0].src
        }
        if(!dataForm.cover){
            _alert('请上传封面图')
            return
        }
        dataForm.content=editor.value.getHtml()
        if(!dataForm.content){
            _alert('请填写文章内容')
            return
        }
        if(isRequest){
            return
        }else{
            isRequest=true
        }
        http({
            url: 'c=News&a=article_update',
            data: dataForm
        }).then((res:any)=>{
            if(res.code!=1){
                isRequest=false
                _alert(res.msg)
                return
            }
            configForm.visible=false  //关闭弹层
            if(!configForm.isEdit){//添加的重新加载
                pageScope.doSearch()
            }else{//动态更新字段
                for(let i in dataForm){
                    actItem.value[i]=dataForm[i]
                }
                actItem.value.cname=res.data.cname
                actItem.value.publish_time=res.data.publish_time
                actItem.value.status_flag=res.data.status_flag
                actItem.value.is_recommend_flag=res.data.is_recommend_flag
            }
            _alert({
                type:'success',
                message:res.msg,
                onClose:()=>{
                    setTimeout(()=>{
                        isRequest=false
                    },2000)
                }
            })
        })
    }

    const del=(idx:number,item:any,myScope:any)=> {
        if (isRequest) {
            return
        } else {
            isRequest = true
        }
        http({
            url: 'c=News&a=article_delete',
            data: {id: item.id}
        }).then((res: any) => {
            if (res.code != 1) {
                isRequest = false
                _alert(res.msg)
                return
            }
            //更新数据集
            myScope.delItem(idx)

            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => {
                    setTimeout(() => {
                        isRequest = false
                    }, 2000)
                }
            })
        })
    }

    const onClickUrl=(row:any)=>{
        window.open(row.url,'_blank')
    }

    const imgFlag=(src:string)=>{
        return getSrcUrl(src)
    }

    onMounted(()=>{

    })

</script>