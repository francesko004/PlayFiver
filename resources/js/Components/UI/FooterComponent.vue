<template>
    <div class="footer pb-32 md:pb-5 mt-5 footer-color p-4 md:p-8">
        <div class="md:w-4/6 2xl:w-4/6 mx-auto p-4">
            <div class="text-center mt-5 flex items-center justify-center flex-wrap">
                <div class="flex justify-center items-center">
                    <img :src="`/storage/${setting.software_logo_black}`" :alt="setting.software_name"
                        style="width: 150px;">
                </div>
                <div class="mt-2" style="margin-bottom: 20px;">
                    <p class="text-xs md:text-sm text-white text-gray-600 dark:text-gray-400" style="font-size: 12px;">
                        {{ disclaimer }}
                    </p>
                </div>
                <div class="text-center">
                    <hr>
                    <br>
                    <div class="container">
                        <button type="button" class="small-btn"><img
                                src="/public/storage/bmlabs.png"
                                alt="Image 1"></button>
                        <button type="button" class="small-btn"><img
                                src="/public/storage/gambalis.png"
                                alt="Image 2"></button>
                        <button type="button" class="small-btn"><img
                                src="/public/storage/gov.png"
                                alt="Image 3"></button>
                        <button type="button" class="small-btn"><img
                                src="/public/storage/malta.png"
                                alt="Image 4"></button>
                        <button type="button" class="small-btn"><img
                                src="/public/storage/pg.png"
                                alt="Image 5"></button>
                    </div>


                    <br>

                    <div class="flex justify-center items-center" style="margin-top: 10px;">
                        <p class="text-[12px]">© {{ year }} {{ setting.software_name }} - {{ $t('@ Todos os direitos reservados') }}.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>


<script>
import { useSettingStore } from '@/Stores/SettingStore.js';

export default {
    data() {
        return {
            year: new Date().getFullYear(),
            footerSections: [
            ],
            disclaimer: ` É uma das mais renomadas empresas internacionais de operação de cassino online, oferecendo uma ampla variedade de jogos empolgantes, como jogos ao vivo com crupiê real, slots, pesca, loterias, esportes e muito mais. Estamos autorizados e regulamentados pelo governo de Curaçao, operando com a licença número 43435-234 `,
            setting: null,
        };
    },
    methods: {
        getSetting() {
            const settingStore = useSettingStore();
            const settingData = settingStore.setting;
            if (settingData) {
                this.setting = settingData;
            }
        },
    },
    created() {
        this.getSetting();
    },
};
</script>

<style scoped>
.dark\:text-gray-400:is(.dark *) {
    --tw-text-opacity: 1;
    color: rgb(255 255 255);
}
.container {
    display: flex;
    /* Usa flexbox para alinhar os elementos */
    justify-content: center;
    /* Centraliza os botões horizontalmente */
    align-items: center;
    /* Centraliza os botões verticalmente */
}

.small-btn {
    margin: 5px;
    /* Espaço entre os botões */
    padding: 0;
    /* Remove o padding padrão */
}

.small-btn img {
    width: 100px;
    /* Define a largura das imagens */
    height: auto;
    /* Mantém a proporção das imagens */
}

.text-\[12px\] {
    font-size: 12px;
    color: #fff;
}
</style>