<x-filament-panels::page>
    <style>
        @keyframes border-color-animation {
            0% { border-color: #7f3cb9; }
            25% { border-color: #9356c8; }
            50% { border-color: #ba93dc; }
            75% { border-color: #c4a3e1; }
            100% { border-color: #7f3cb9; }
        }
    </style>
     <div class="w-full p-4 bg-gray-500 shadow-lg" style="background-color: #51515163; border-radius: 20px; border: 5px solid; animation: border-color-animation 4s infinite;">
        <h2 class="mb-5 text-3xl">PlayConnect </h2>
        <p>Velocidade e Estabilidade inagualável!
        </p>
        <br>
        <div class="flex justify-between w-full gap-4">
            <div class="flex flex-col gap-4 w-full justify-between">
                <button wire:click="loadProviderPlayConnect" style="border-radius: 10px" class="bg-primary-600 px-3 py-2 w-full text-center">
                    <div wire:loading wire:target="loadProviderPlayConnect">Carregando Provedores</div>
                    <div wire:loading.remove wire:target="loadProviderPlayConnect">Carregar Provedores</div>
                </button>

            </div>
            <button wire:click="loadGamesPlayConnect" style="border-radius: 10px; overflow: hidden; position: relative; background-color: #7f3cb9" class="bg-primary-500 px-3 py-2 w-full">
                <div style="flex flex-col gap-4 w-full justify-between; color: #ffffff;">
                    <div wire:loading wire:target="loadGamesPlayConnect">
                        <div style="display: flex; align-items: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="display: block; shape-rendering: auto; animation-play-state: running; animation-delay: 0s; max-width: 32px; margin-right: 8px;" width="32px" height="32px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                                <path fill="none" stroke="#ffffff" stroke-width="8" stroke-dasharray="42.76482137044271 42.76482137044271" d="M24.3 30C11.4 30 5 43.3 5 50s6.4 20 19.3 20c19.3 0 32.1-40 51.4-40 C88.6 30 95 43.3 95 50s-6.4 20-19.3 20C56.4 70 43.6 30 24.3 30z" stroke-linecap="round" style="transform: scale(0.8); transform-origin: 50px 50px 0px; animation-play-state: running; animation-delay: 0s;">
                                    <animate attributeName="stroke-dashoffset" repeatCount="indefinite" dur="1s" keyTimes="0;1" values="0;256.58892822265625" style="animation-play-state: running; animation-delay: 0s;"></animate>
                                </path>
                            </svg>
                            <span style="white-space: nowrap;">Carregando Jogos...</span>
                        </div>
                    </div>
                    <div wire:loading.remove wire:target="loadGamesPlayConnect">
                        Carregar Jogos
                    </div>
                </div>
            </button>

        </div>
    </div>
</x-filament-panels::page>
