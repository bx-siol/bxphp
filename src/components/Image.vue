<template>
    <div class="myImageBox" :style="myBoxStyle">
        <img class="myImageBox__img" :src="src" :style="{objectFit:fit}" />
    </div>
</template>

<script lang="ts">
    import {defineComponent} from 'vue';

    export default defineComponent({
        components:{

        }
    })
</script>

<script lang="ts" setup>
    import {reactive,ref} from 'vue';

    const props=defineProps({
        src:{type:String,default:''},
        fit:{type:String,default:''},
        width:{type:String},
        height:{type:String},
        radius:{type:String},
        showError:{type:Boolean,default:true},
        showLoading:{type:Boolean,default:true}
    })

    const imgSrc=ref('')
/*    import(props.src).then(res=>{
        let str=res.default
        imgSrc.value='src'+str.slice(4)
        console.log(imgSrc.value)
    })*/


    interface BoxStyle{
        width?:string,
        height?:string,
        borderRadius?:string
    }
    const myBoxStyle=reactive<BoxStyle>({})
    if(props.width){
        if(parseInt(props.width)==props.width){
            myBoxStyle.width=props.width+'px'
        }else{
            myBoxStyle.width=props.width
        }
    }
    if(props.height){
        if(parseInt(props.height)==props.height){
            myBoxStyle.height=props.height+'px'
        }else{
            myBoxStyle.height=props.height
        }
    }
    if(props.radius){
        if(parseInt(props.radius)==props.radius){
            myBoxStyle.borderRadius=props.radius+'px'
        }else{
            myBoxStyle.borderRadius=props.radius
        }
    }
</script>

<style scoped>
    .myImageBox{
        position: relative;display: inline-block;
    }
    .myImageBox .myImageBox__img,.myImageBox .myImageBox__error,.myImageBox .myImageBox__loading{
        display: block;width: 100%;height: 100%;
    }
</style>