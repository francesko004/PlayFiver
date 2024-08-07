<template>
    <LoadingComponent :isLoading="isLoading">
        <div class="flex items-center justify-center h-full">
            <a v-if="setting" href="/" class="logo-animation">
                <img
                    :src="`/storage/` + setting.software_logo_black"
                    alt=""
                    class="block h-10 mr-3 dark:hidden"
                />
                <img
                    :src="`/storage/` + setting.software_logo_white"
                    alt=""
                    class="hidden h-10 mr-3 dark:block"
                />
            </a>
        </div>
    </LoadingComponent>

    <div v-if="!isLoading && game" class="relative w-full mx-auto">
        <div class="game-screen" id="game-screen">
            <fullscreen v-model="fullscreen" :page-only="pageOnly">
                <iframe
                    v-if="
                        game.distribution === 'play_fiver' ||
                        game.distribution === 'source'
                    "
                    :src="gameUrl"
                    class="game-full fullscreen-wrapper"
                ></iframe>
            </fullscreen>
        </div>
    </div>

    <div
        v-if="undermaintenance"
        class="flex flex-col items-center justify-center py-24 text-center"
    >
        <h1 class="mb-4 text-2xl">JOGO EM MANUTENÇÃO</h1>
        <img
            :src="`/assets/images/work-in-progress.gif`"
            alt=""
            width="400"
        />
    </div>

    <!-- Adicionando o botão de volta com uma bolinha preta e ícone de casinha branca -->
    <div class="absolute top-4 left-4">
        <a href="/" class="home-button">
            <i class="fas fa-home"></i>
        </a>
    </div>
</template>

<script>
import { initFlowbite, Tabs, Modal } from "flowbite";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/Stores/Auth.js";
import { component } from "vue-fullscreen";
import LoadingComponent from "@/Components/UI/LoadingComponent.vue";
import HttpApi from "@/Services/HttpApi.js";

import { defineComponent, toRefs, reactive } from "vue";

export default {
    props: [],
    components: {
        LoadingComponent,
        RouterLink,
        component,
    },
    data() {
        return {
            isLoading: true,
            game: null,
            modeMovie: false,
            gameUrl: null,
            token: null,
            gameId: null,
            tabs: null,
            undermaintenance: false,
        };
    },
    setup() {
        const router = useRouter();
        const state = reactive({
            fullscreen: false,
            pageOnly: false,
        });
        function togglefullscreen() {
            console.log("CLICOU");
            state.fullscreen = !state.fullscreen;
        }

        return {
            ...toRefs(state),
            togglefullscreen,
            router,
        };
    },
    computed: {
        userData() {
            const authStore = useAuthStore();
            return authStore.user;
        },
        isAuthenticated() {
            const authStore = useAuthStore();
            return authStore.isAuth;
        },
    },
    mounted() {
        const userAgent = navigator.userAgent.toLowerCase();
        const isSafari =
            userAgent.includes("safari") && !userAgent.includes("chrome");
        const isSamsungInternet =
            userAgent.includes("samsung") &&
            userAgent.includes("safari") &&
            !userAgent.includes("chrome");
        const isIOS =
            userAgent.includes("iphone") || userAgent.includes("ipad");

        if (isSafari || isSamsungInternet || isIOS) {
            this.showButton = true;
        }
    },
    methods: {
        loadingTab: function () {
            const tabsElement = document.getElementById("tabs-info");
            if (tabsElement) {
                const tabElements = [
                    {
                        id: "default",
                        triggerEl: document.querySelector("#default-tab"),
                        targetEl: document.querySelector("#default-panel"),
                    },
                    {
                        id: "descriptions",
                        triggerEl: document.querySelector("#description-tab"),
                        targetEl: document.querySelector("#description-panel"),
                    },
                    {
                        id: "reviews",
                        triggerEl: document.querySelector("#reviews-tab"),
                        targetEl: document.querySelector("#reviews-panel"),
                    },
                ];

                const options = {
                    defaultTabId: "default",
                    activeClasses:
                        "text-green-600 hover:text-green-600 dark:text-green-500 dark:hover:text-green-400 border-green-600 dark:border-green-500",
                    inactiveClasses:
                        "text-gray-500 hover:text-gray-600 dark:text-gray-400 border-gray-100 hover:border-gray-300 dark:border-gray-700 dark:hover:text-gray-300",
                    onShow: () => {},
                };

                const instanceOptions = {
                    id: "default",
                    override: true,
                };

                /*
                 * tabElements: array of tab objects
                 * options: optional
                 * instanceOptions: optional
                 */
                this.tabs = new Tabs(
                    tabsElement,
                    tabElements,
                    options,
                    instanceOptions
                );
            }
        },
        openModal(gameUrl) {
            window.open(gameUrl);
        },

        getGame: async function () {
            const _this = this;

            return await HttpApi.get("games/single/" + _this.gameId)
                .then(async (response) => {
                    if (response.data?.action === "deposit") {
                        _this.$nextTick(() => {
                            _this.router.push({ name: "profileDeposit" });
                        });
                    }

                    const game = response.data.game;
                    _this.game = game;

                    _this.gameUrl = response.data.gameUrl;
                    _this.token = response.data.token;
                    _this.isLoading = false;

                    _this.$nextTick(() => {
                        _this.loadingTab();
                    });
                })
                .catch((error) => {
                    _this.isLoading = false;
                    _this.undermaintenance = true;
                    Object.entries(
                        JSON.parse(error.request.responseText)
                    ).forEach(([key, value]) => {});
                });
        },
        toggleFavorite: function () {
            const _this = this;
            return HttpApi.post("games/favorite/" + _this.game.id, {})
                .then((response) => {
                    _this.getGame();
                    _this.isLoading = false;
                })
                .catch((error) => {
                    _this.isLoading = false;
                });
        },
        toggleLike: async function () {
            const _this = this;
            return await HttpApi.post("games/like/" + _this.game.id, {})
                .then(async (response) => {
                    await _this.getGame();
                    _this.isLoading = false;
                })
                .catch((error) => {
                    _this.isLoading = false;
                });
        },
    },
    async created() {
        if (this.isAuthenticated) {
            const route = useRoute();
            this.gameId = route.params.id;

            await this.getGame();
        } else {
            this.router.push({
                name: "login",
                params: { action: "openlogin" },
            });
        }
    },
    watch: {},
};
</script>

<style>
.game-screen {
    width: 100%;
    min-height: 100%;
}

.game-screen {
    width: 100%;
    min-height: 100%;
}

.game-full {
    width: 100%;
    height: 100%;
}

.fullscreen-wrapper {
    width: 100%;
    min-height: calc(100vh - 70px);
}

.home-button {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background-color: black;
    border-radius: 50%;
}

.home-button i {
    color: white;
    font-size: 20px;
}
</style>
