<template>
    <SportLayout>
        <LoadingComponent :isLoading="isLoading">
            <div class="text-center">
                <span>{{ $t('Filling with the best odds') }}</span>
            </div>
        </LoadingComponent>

        <div v-if="!isLoading">
            <div v-if="!isLoading && game" class="mx-auto relative sport-page w-full">
                <iframe :src="gameUrl" class="game-full w-full h-full"></iframe>
            </div>

            <div v-if="undermaintenance" class="flex flex-col items-center justify-center text-center py-24">
                <h1 class="text-2xl mb-4">JOGO EM MANUTENÇÃO</h1>
                <img :src="`/assets/images/work-in-progress.gif`" alt="" width="400">
            </div>
        </div>

    </SportLayout>
</template>


<script>
import { RouterLink } from "vue-router";
import { Carousel, Navigation, Slide } from 'vue3-carousel';
import 'vue3-carousel/dist/carousel.css';
import SportLayout from "@/Layouts/SportLayout.vue";
import SportCard from "@/Pages/Sport/Components/SportCard.vue";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";
import BettingBulletinComponent from "@/Components/UI/BettingBulletinComponent.vue";
import HeaderNav from "@/Pages/Sport/Components/HeaderNav.vue";
import HeaderOptions from "@/Pages/Sport/Components/HeaderOptions.vue";
import {useToast} from "vue-toastification";
import HttpApi from "@/Services/HttpApi.js";
import SportCardLive from "@/Pages/Sport/Components/SportCardLive.vue";
import {useSettingStore} from "@/Stores/SettingStore.js";
import { useAuthStore } from "@/Stores/Auth.js";

export default {
    props: [],
    components: {
        RouterLink,
        LoadingComponent,
        SportLayout,
    },
    data() {
        return {
            game: null,
            gameUrl: null,
            token: null,
            gameId: null,
            undermaintenance: false,
        }
    },
    setup(props) {

    },
    computed: {
        isAuthenticated() {
            const authStore = useAuthStore();
            return authStore.isAuth;
        },
        setting() {
            const authStore = useSettingStore();
            return authStore.setting;
        }
    },
    mounted() {

    },
    beforeDestroy() {
    },
    methods: {
        getGame: async function() {
            const _this = this;

            return await HttpApi.get('games/single/1580')
                .then(async response =>  {

                    if(response.data?.action === 'deposit') {
                        _this.$nextTick(() => {
                            _this.router.push({ name: 'profileDeposit' });
                        });

                    }

                    const game = response.data.game;
                    _this.game = game;

                    _this.gameUrl = response.data.gameUrl;
                    _this.token = response.data.token;
                    _this.isLoading = false;

                })
                .catch(error => {

                    _this.isLoading = false;
                    _this.undermaintenance = true;
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {

                    });
                });
        },
    },
    async created() {
        if(this.isAuthenticated) {
            await this.getGame();
        }else{
            this.router.push({ name: 'login', params: { action: 'openlogin' } });
        }
    },
    watch: {

    },
};
</script>

<style>
.sport-page {
    overflow: hidden;
    height: calc(100vh - 65px);
}
</style>

