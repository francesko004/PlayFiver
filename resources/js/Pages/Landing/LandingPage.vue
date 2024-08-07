<template>
    <LandingLayout>
        <LoadingComponent :isLoading="isLoading">
            <div class="flex justify-center items-center h-full">
                <a v-if="setting" href="/" class="logo-animation"> <!-- Adicionando a classe items-center para centralizar verticalmente -->
                    <img :src="`/storage/` + setting.software_logo_black" alt="" class="h-10 mr-3 block dark:hidden" />
                    <img :src="`/storage/` + setting.software_logo_white" alt="" class="h-10 mr-3 hidden dark:block" />
                </a>
            </div>
        </LoadingComponent>

        <div v-if="!isAuthenticated" class="container mx-auto p-4 mt-16">
            <div class="grid mb-4 mt-1">
                <div class="relative" style="height: 100vh;">
                    <LandingModal :showModal="showModal" @close="CloseModal" @loaded="HandleLoaded"/>
                </div>
            </div>
        </div>
    </LandingLayout>
</template>

<script>
    import { ref } from "vue";

    import LandingLayout from '@/Layouts/LandingLayout.vue';
    import { useAuthStore } from "@/Stores/Auth.js";
    import LoadingComponent from "@/Components/UI/LoadingComponent.vue";
    import LandingModal from '@/Components/LandingSpin/LandingModal.vue';

    export default {
        props: [],
        components: {
            LoadingComponent,
            LandingLayout,
            LandingModal
        },
        data() {
            return {
                isLoading: true,
                showModal: false
            }
        },
        setup(props) {
            const ckCarouselOriginals = ref(null);
            return {
                ckCarouselOriginals
            };
        },
        computed: {
            isAuthenticated() {
                const authStore = useAuthStore();
                return authStore.isAuth;
            }
        },
        mounted() {
            if (this.isAuthenticated) {
                location.href='/';
            }
        },
        methods: {
            CloseModal() {
                this.showModal=false;
                location.href='/';
            },
            HandleLoaded() {
                this.showModal = true;
                this.isLoading = false;
            }
        }
    };
</script>

<style>
    html, document, body {
        user-select: none;
    }
</style>
