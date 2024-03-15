<template>
    <div class="monthly">
        <MyNav leftText=''></MyNav>
        <div class="title">
            <p style="margin-bottom: 0.65rem;">Dear Nestlé-India members:</p>
            <p>As Nestlé India continues to grow, we have adjusted the monthly salary system. After this revision, there
                will be no changes in the next three years.</p>
        </div>
        <div class="imgs">
            <img :src="monthly">
        </div>
    </div>
</template>
<script lang="ts" setup>
import MyNav from "../../components/Nav.vue";
import monthly from '../../assets/img/user/monthlypay.png';

</script>

<style lang="scss" scoped>
.monthly {
    padding: 1rem;

    p {
        font: bold 14px/24px "微软雅黑";
    }

    .imgs {
        margin: 1rem 0;
        width: 22rem;
        height: 25rem;
        position: relative;
        left: 50%;
        transform: translateX(-50%);

        img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            position: relative;
            left: 50%;
            transform: translateX(-50%);
        }
    }

}
</style>