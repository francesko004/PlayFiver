<template>
    <NavTopComponent :simple="false" />

    <div :class="'mt-[68px]'">

        <div class="relative">
            <slot></slot>

        </div>

    </div>

</template>

<script>
import { initFlowbite } from 'flowbite';
import { onMounted } from "vue";

import { useAuthStore } from "@/Stores/Auth.js";

import NavTopComponent from "@/Components/Nav/NavTopComponent.vue";
import SideBarComponent from "@/Components/Nav/SideBarComponent.vue";
import FooterComponent from "@/Components/UI/FooterComponent.vue";
import BottomNavComponent from "@/Components/Nav/BottomNavComponent.vue";
import CookiesComponent from "@/Components/UI/CookiesComponent.vue";

export default {
    props: [],
    components: {
        CookiesComponent,
        BottomNavComponent,
        FooterComponent,
        SideBarComponent,
        NavTopComponent,
    },
    data() {
        return {
            logo: '/assets/images/logo_verde.png',
            isLoading: false,
            tabsMission: null,
            modalMission: null,
        }
    },
    setup(props) {

        onMounted(() => {
            initFlowbite();
        });

        return {

        };
    },
    computed: {
        isAuthenticated() {
            const authStore = useAuthStore();
            return authStore.isAuth;
        },
    },
    mounted() {
    },
    methods: {
    },
    watch: {
    },
};
</script>
