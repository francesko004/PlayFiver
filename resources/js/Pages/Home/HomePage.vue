<template>
    <BaseLayout>
        <LoadingComponent :isLoading="isLoading">
            <div class="flex justify-center items-center h-full">
                <a v-if="setting" href="/" class="logo-animation"> <!-- Adicionando a classe items-center para centralizar verticalmente -->
                    <img :src="`/storage/` + setting.software_logo_black" alt="" class="h-10 mr-3 block dark:hidden" />
                    <img :src="`/storage/` + setting.software_logo_white" alt="" class="h-10 mr-3 hidden dark:block" />
                </a>
            </div>
        </LoadingComponent>

        <div v-if="!isLoading" class="">

            <!-- Banners carousel -->
            <div class="carousel-banners">
            </div>

            <div class="md:w-5/6 2xl:w-5/6 mx-auto p-4">
                <div class="md:mt-5">
                        <Carousel v-bind="settings" :breakpoints="breakpoints" ref="carouselBanner">
                            <Slide v-for="(banner, index) in banners" :key="index">
                                <div class="carousel__item rounded w-full">
                                    <a :href="banner.link" class="w-full h-full bg-blue-800 rounded">
                                        <img :src="`/storage/`+banner.image" alt="" class="h-full w-full rounded">
                                    </a>
                                </div>
                            </Slide>
                        </Carousel>
                </div>

                <div class="mt-10">
                    <Carousel v-bind="settingsRecommended" :breakpoints="breakpointsRecommended" ref="carouselSubBanner">
                        <Slide v-for="(banner, index) in bannersHome" :key="index">
                            <div class="carousel__item h-full rounded w-full mr-4">
                                <a :href="banner.link" class="w-full h-full rounded">
                                    <img :src="`/storage/`+banner.image" alt="" class="h-full w-full rounded">
                                </a>
                            </div>
                        </Slide>

                        <!-- <template #addons>
                            <Pagination />
                        </template> -->
                    </Carousel>
                </div>

                <br>
                <br>

                <!-- Searchbar action -->
                <div class="mb-5 cursor-pointer w-full">
                    <div class="flex">
                        <div class="relative w-full">
                            <input @click.prevent="toggleSearch" type="search"
                                   readonly
                                   class="block dark:focus:border-green-500 p-2.5 w-full z-20 text-sm text-gray-900 rounded-e-lg input-color-primary border-none focus:outline-none dark:border-s-gray-800  dark:border-gray-800 dark:placeholder-gray-400 dark:text-white "
                                   placeholder="Nome do jogo | Provedor" required>

                            <button type="submit" class="absolute top-0 end-0 h-full p-2.5 text-sm font-medium text-white rounded-e-lg
                                 dark:bg-[#1C1E22] ">
                                <svg class="w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 19-4-4m0-7A7 7 0 1 1 1 8a7 7 0 0 1 14 0Z"/>
                                </svg>
                                <span class="sr-only">Search</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- categories -->
                <!-- <div v-if="categories" class="category-list">
                    <div class="flex mb-5 gap-4" style="max-height: 200px; overflow-x: auto; overflow-y: hidden;">
                        <div class="flex flex-row justify-between items-center w-full" style="min-width: 100%; white-space: nowrap;">
                            <RouterLink :to="{ name: 'casinosAll', params: { provider: 'all', category: category.slug }}" v-for="(category, index) in categories"
                                        class="flex flex-col justify-center items-center min-w-[80px] text-center">
                                <div class="nui-mask nui-mask-hexed dark:bg-muted-800 flex size-16 scale-95 items-center justify-center input-color-primary p-4 shadow-lg">
                                    <img :src="`/storage/`+category.image" alt="" width="35" class="">
                                </div>
                                <p class="mt-3">{{ $t(category.name) }}</p>
                            </RouterLink>
                        </div>
                    </div>
                </div> -->


                <!-- featured -->
                <div v-if="featured_games" class="mt-3">

                    <div class="w-full flex justify-between mb-2">
                        <div class="flex items-center">
                            <h2 class="text-xl font-bold">{{ $t('Featured') }}</h2>
                            <button @click.prevent="fgCarousel.prev()"
                                class="item-game px-2 py-1 rounded-lg text-[12px] ml-2 mr-2">
                                <i class="fa-solid fa-angle-left"></i>
                            </button>
                            <button @click.prevent="fgCarousel.next()"
                                class="item-game px-2 py-1 rounded-lg text-[12px]">
                                <i class="fa-solid fa-angle-right"></i>
                            </button>
                        </div>
                        <div class="flex">

                        </div>
                    </div>

                    <Carousel ref="fgCarousel" v-bind="settingsGames" :breakpoints="breakpointsGames"
                        @init="onCarouselInit(index)" @slide-start="onSlideStart(index)">
                        <Slide v-if="isLoading" v-for="(i, iloading) in 10" :index="iloading">
                            <div role="status"
                                class="w-full flex items-center justify-center h-48 mr-6 max-w-sm bg-gray-300 rounded-lg animate-pulse dark:bg-gray-700 text-4xl">
                                <i class="fa-duotone fa-gamepad-modern"></i>
                            </div>
                        </Slide>

                        <Slide v-if="featured_games && !isLoading" v-for="(game, providerId) in featured_games"
                            :index="providerId" class="p-2">
                            <CassinoGameCard :index="providerId" :title="game.game_name" :cover="game.cover"
                                :gamecode="game.game_code" :type="game.distribution" :game="game" />
                        </Slide>
                    </Carousel>
                </div>


                <!-- Nova seção para jogos com distribution === 'source' -->
                <div v-if="source_games">
                    <div class="w-full flex justify-between mb-4">
                        <h2 class="text-xl font-bold">SOURCE</h2>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-5">
                        <CassinoGameCard
                            v-for="(game, index) in source_games"
                            :key="'source_' + index"
                            :index="index"
                            :title="game.game_name"
                            :cover="game.cover"
                            :gamecode="game.game_code"
                            :type="game.distribution"
                            :game="game"
                        />
                    </div>
                </div>


                <!-- games -->
                <div class="mt-5">
                    <ShowProviders v-for="(provider, index) in providers" :provider="provider" :index="index" />
                </div>

            </div>
        </div>
    </BaseLayout>
</template>

<script>
import {Carousel, Navigation, Pagination, Slide} from 'vue3-carousel';
import {onMounted, ref} from "vue";

import BaseLayout from "@/Layouts/BaseLayout.vue";
import MakeDeposit from "@/Components/UI/MakeDeposit.vue";
import {RouterLink, useRoute} from "vue-router";
import {useAuthStore} from "@/Stores/Auth.js";
import LanguageSelector from "@/Components/UI/LanguageSelector.vue";
import CassinoGameCard from "@/Pages/Cassino/Components/CassinoGameCard.vue";
import HttpApi from "@/Services/HttpApi.js";
import ShowCarousel from "@/Pages/Home/Components/ShowCarousel.vue";
import {useSettingStore} from "@/Stores/SettingStore.js";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";
import ShowProviders from "@/Pages/Home/Components/ShowProviders.vue";
import {searchGameStore} from "@/Stores/SearchGameStore.js";
import CustomPagination from "@/Components/UI/CustomPagination.vue";



export default {
    props: [],
    components: {
        CustomPagination,
        Pagination,
        ShowProviders,
        LoadingComponent,
        ShowCarousel,
        CassinoGameCard,
        Carousel,
        Navigation,
        Slide,
        LanguageSelector,
        MakeDeposit,
        BaseLayout,
        RouterLink
    },
    data() {
        return {
            isLoading: true,

            /// banners settings
            settings: {
                itemsToShow: 1,
                snapAlign: 'center',
                autoplay: 6000,
                wrapAround: true
            },
            breakpoints: {
                700: {
                    itemsToShow: 1,
                    snapAlign: 'center',
                },
                1024: {
                    itemsToShow: 1,
                    snapAlign: 'center',
                },
            },

            settingsRecommended: {
                itemsToShow: 2,
                snapAlign: 'start',
            },
            breakpointsRecommended: {
                700: {
                    itemsToShow: 3,
                    snapAlign: 'center',
                },
                1024: {
                    itemsToShow: 3,
                    snapAlign: 'start',
                },
            },

            /// banners
            banners: null,
            bannersHome: null,

            settingsGames: {
                itemsToShow: 2.5,
                snapAlign: 'start',
            },
            breakpointsGames: {
                700: {
                    itemsToShow: 3.5,
                    snapAlign: 'center',
                },
                1024: {
                    itemsToShow: 4.5,
                    snapAlign: 'start',
                },
            },
            providers: null,
            
            source_games: null,
            featured_games: null,
            categories: null,
        }
    },
    setup(props) {
        const ckCarouselOriginals = ref(null)
        const fgCarousel = ref(null)

        onMounted(() => {

        });

        return {
            ckCarouselOriginals,
            fgCarousel
        };
    },
    computed: {
        searchGameStore() {
            return searchGameStore();
        },
        userData() {
            const authStore = useAuthStore();
            return authStore.user;
        },
        isAuthenticated() {
            const authStore = useAuthStore();
            return authStore.isAuth;
        },
        setting() {
            const settingStore = useSettingStore();
            return settingStore.setting;
        }
    },
    mounted() {

    },
    methods: {
        getCasinoCategories: async function() {
            const _this = this;
            await HttpApi.get('categories')
                .then(response => {
                    _this.categories = response.data.categories;
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {

                    });
                });
        },
        toggleSearch: function() {
            this.searchGameStore.setSearchGameToogle();
        },
        getBanners: async function() {
            const _this = this;

            try {
                const response = await HttpApi.get('settings/banners');
                const allBanners = response.data.banners;

                _this.banners = allBanners.filter(banner => banner.type === 'carousel');
                _this.bannersHome = allBanners.filter(banner => banner.type === 'home');
            } catch (error) {
                console.error(error);
            } finally {

            }
        },
        getAllGames: async function() {
            const _this = this;
            return await HttpApi.get('games/all')
                .then(async response =>  {
                    if(response.data !== undefined) {
                        _this.providers = response.data.providers;
                        _this.isLoading = false;
                    }
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        console.log(`${value}`);
                    });
                    _this.isLoading = false;
                });
        },
        getFeaturedGames: async function() {
            const _this = this;
            return await HttpApi.get('featured/games')
                .then(async response =>  {


                    _this.featured_games = response.data.featured_games;
                    _this.isLoading = false;
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        console.log(`${value}`);
                    });
                    _this.isLoading = false;
                });
        },
        getSourceGames: async function() {
            const _this = this;
            return await HttpApi.get('source/games')
                .then(async response =>  {
                    _this.source_games = response.data.source_games;
                    _this.isLoading = false;
                })
                .catch(error => {
                    Object.entries(JSON.parse(error.request.responseText)).forEach(([key, value]) => {
                        console.log(`${value}`);
                    });
                    _this.isLoading = false;
                });
        },
        initializeMethods: async function() {
            await this.getCasinoCategories();
            await this.getBanners();
            await this.getAllGames();
            await this.getFeaturedGames();
            await this.getSourceGames();
        }

    },
    async created() {
        await this.initializeMethods();
    },
    watch: {


    },
};
</script>


