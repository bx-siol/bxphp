<template>
    <div class="conBox">
        <Nav></Nav>
        <van-form @submit="onSubmit">
            <van-cell-group>
                <van-field
                        v-model="dataForm.account"
                        readonly left-icon="user-o">
                </van-field>
                <van-field
                        v-model="dataForm.google_secret"
                        placeholder="密钥" readonly left-icon="shield-o">
                    <template #button>
                        <van-button size="mini" text="Update" type="warning" @click="updateGoogleSecret" style="vertical-align: middle;margin-right: 5px;color: #0098a2;border-color: #0098a2;background-color: transparent;" plain></van-button>
                        <van-button size="mini" text="Copy" type="success" :data-clipboard-text="dataForm.google_secret" class="copyBtn" style="vertical-align: middle;background-color: transparent;" plain></van-button>
                    </template>
                </van-field>
                <van-field readonly left-icon="qr">
                    <template #input>
                        <van-image :src="dataForm.google_qrcode" width="200" height="200">
                            <template v-slot:loading>
                                <van-loading type="spinner" size="20" />
                            </template>
                        </van-image>
                    </template>
                </van-field>
                <van-field readonly left-icon="exchange">
                    <template #input>
                        <van-radio-group v-model="dataForm.is_google" direction="horizontal">
                            <van-radio :name="idx" v-for="(item,idx) in dataForm.sys_switch">{{lang(item)}}</van-radio>
                        </van-radio-group>
                    </template>
                </van-field>
                <van-field
                        v-model="dataForm.password"
                        type="password"
                        placeholder="Payment password" style="overflow: hidden;">
                    <template #left-icon>
                        <van-image :src="ico_2" width="1.6rem" fit="cover" style="margin-left: -0.2rem;margin-top: -2px;"/>
                    </template>
                </van-field>
            </van-cell-group>
            <div style="margin: 2rem;">
                <van-button class="myBtn" round block type="primary" native-type="submit">Submit</van-button>
            </div>
        </van-form>
    </div>
</template>

<script lang="ts">
import { defineComponent} from 'vue';
import {Form, Field, CellGroup, Cell, Button, Icon,RadioGroup,Radio,Image,Loading,Dialog} from 'vant';
import Nav from '../../components/Nav.vue';

export default defineComponent({
    components:{
        Nav,
        [Form.name]:Form,
        [Field.name]:Field,
        [CellGroup.name]:CellGroup,
        [Cell.name]:Cell,
        [RadioGroup.name]:RadioGroup,
        [Radio.name]:Radio,
        [Icon.name]:Icon,
        [Image.name]:Image,
        [Loading.name]:Loading,
        [Button.name]:Button
    }
})
</script>

<script lang="ts" setup>
    import {useRoute} from "vue-router";
    import {useStore} from "vuex";
    import {onMounted, reactive} from "vue";
    import {Dialog} from'vant';
    import {_alert,lang} from "../../global/common";
    import {http} from "../../global/network/http";
    import md5 from "md5";
    import ClipboardJS from "clipboard";
    import {ico_2} from '../../global/assets';
    console.log(ico_2)

    const route=useRoute()
    const store=useStore()

    let iconMarginRight='0.2rem'
    const configForm=reactive({
        iconSize:'1.3rem',
        iconStyle:{
            display:'block',
            marginRight:iconMarginRight
        }
    })

    const dataForm=reactive({
        account:store.state.user.account,
        phone_flag:store.state.user.phone_flag,
        google_secret:'',
        google_qrcode:'',
        is_google:'0',
        password:'',
        sys_switch:[]
    })

    const onSubmit=()=>{
        http({
            url: 'c=Setting&a=google_update',
            data:{
                is_google:dataForm.is_google,
                password:md5(dataForm.password)
            }
        }).then((res: any) => {
            if (res.code != 1) {
                _alert(res.msg)
                return
            }
            dataForm.password=''
            _alert({
                type: 'success',
                message: res.msg,
                onClose: () => {
                    //...
                }
            })
        })
    }

    const updateGoogleSecret=()=>{
        Dialog.confirm({
            message: '<b style="color: #f30;">Are you sure to operate？</b>',
            confirmButtonText:'Confirm',
            cancelButtonText:'Cancel',
            width:'280px',
            allowHtml:true,
            beforeClose:(action:string):Promise<boolean>|boolean => {
                if(action!='confirm'){
                    return true
                }
                return new Promise((resolve) => {
                    http({
                        url: 'c=Setting&a=google_secret'
                    }).then((res: any) => {
                        if (res.code != 1) {
                            _alert(res.msg)
                            return
                        }
                        resolve(true);
                        dataForm.google_secret=res.data.google_secret
                        dataForm.google_qrcode=res.data.google_qrcode
                        _alert({
                            type: 'success',
                            message: res.msg
                        })
                    })
                })
            }
        }).catch(()=>{})
    }

    onMounted(()=>{
        http({
            url:'c=Setting&a=google'
        }).then((res:any)=>{
            if(res.code!=1){
                _alert(res.msg)
                return
            }
            let user=res.data.user
            dataForm.is_google=user.is_google.toString()
            dataForm.google_secret=user.google_secret
            dataForm.google_qrcode=user.google_qrcode
            dataForm.sys_switch=res.data.sys_switch
        })

        const clipboard = new ClipboardJS('.copyBtn');

        clipboard.on('success', function (e) {
            _alert('复制成功')
            e.clearSelection();
        });

    })
</script>