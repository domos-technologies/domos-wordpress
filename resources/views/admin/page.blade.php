<?php
/** @var string $url */
?>

<script src="https://unpkg.com/alpinejs@3.13.1"></script>
<link rel="stylesheet" href="{{ domos_plugin_url('public/css/admin.css') }}" />

<div
    class="w-full mx-auto flex flex-col items-center justify-center p-10 space-y-10"
    x-data="{
        url: '{{ $url }}',
        error: null,
        synchronizing: false,

        execution: null,

        async sync() {
            // Fetch data from the server, with credentials from the current user.
            this.synchronizing = true;
            this.error = null;
            this.execution = null;

            const response = await fetch('/wp-json/domos/admin/sync', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings.nonce,
                },
            });

            try {
                if (!response.ok) {
                    const data = await response.json();

                    this.error = `${data.message} (${data.code})`;
                } else {
                    const {created, deleted, updated} = await response.json();

                    this.execution = {
                        created,
                        deleted,
                        updated,
                    };
                }
            } catch (e) {
                console.error(e);

                this.error = 'Ein unbekannter Fehler ist aufgetreten.';
            }

            this.synchronizing = false;
        }
    }"
>
    <a href="https://domos.de" target="_blank">
        <img src="{{ domos_plugin_url('resources/images/domos-logo.svg') }}" alt="DOMOS Logo" class="w-32"/>
    </a>

    <div
        x-data="{
            tabSelected: {{ $url === null ? 2 : 1 }},
            tabId: $id('tabs'),

            init() {
                this.$nextTick(() => {
                    this.tabRepositionMarker(this.$refs.tabButtons.children[this.tabSelected - 1]);
                });
            },

            tabButtonClicked(tabButton) {
                const id = tabButton.id.replace(this.tabId + '-', '');
                console.log(id);
                if (this.url === null && id === '1') {
                    return;
                }

                this.tabSelected = id;
                this.tabRepositionMarker(tabButton);
            },
            tabRepositionMarker(tabButton){
                this.$refs.tabMarker.style.width=tabButton.offsetWidth + 'px';
                this.$refs.tabMarker.style.height=tabButton.offsetHeight + 'px';
                this.$refs.tabMarker.style.left=tabButton.offsetLeft + 'px';
            },
            tabContentActive(tabContent){
                return this.tabSelected == tabContent.id.replace(this.tabId + '-content-', '');
            }
        }"
        class="relative w-full max-w-sm"
    >

        <div x-ref="tabButtons"
             class="relative inline-grid items-center justify-center w-full h-10 grid-cols-2 p-1 text-gray-500 bg-gray-100 rounded-lg select-none">
            <button :id="$id(tabId)" @click="tabButtonClicked($el);" type="button"
                    class="relative z-20 inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap">
                Sync
            </button>
            <button :id="$id(tabId)" @click="tabButtonClicked($el);" type="button"
                    x-ref="apiSettingsButton"
                    class="relative z-20 inline-flex items-center justify-center w-full h-8 px-3 text-sm font-medium transition-all rounded-md cursor-pointer whitespace-nowrap">
                API-Einstellungen
            </button>
            <div x-ref="tabMarker" class="absolute left-0 z-10 w-1/2 h-full duration-300 ease-out" x-cloak>
                <div class="w-full h-full bg-white rounded-md shadow-sm"></div>
            </div>
        </div>
        <div class="relative w-full mt-2 content">
            <div :id="$id(tabId + '-content')" x-show="tabContentActive($el)" class="relative">
                <!-- Tab Content 1 - Replace with your content -->
                <div class="border rounded-lg shadow-sm bg-gray-50 text-neutral-900 overflow-hidden">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Sync</h3>
                        <p class="text-sm text-neutral-500">Synchronisiere WordPress mit deiner domos-Instanz.</p>
                    </div>
                    <div class="p-6 pt-0 space-y-2">
                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-white transition-colors duration-200 rounded-md bg-neutral-950 hover:bg-neutral-900 focus:ring-2 focus:ring-offset-2 focus:ring-neutral-900 focus:shadow-outline focus:outline-none"
                            @click="sync"
                            :disabled="synchronizing"
                        >
                            <span x-show="!synchronizing">Jetzt synchronisieren</span>
                            <span x-show="synchronizing">Synchronisiert...</span>
                        </button>
                    </div>
                    <template x-if="error">
                        <div class="px-6 py-4 space-y-1 bg-rose-200 text-rose-950 border-t border-rose-300">
                            <p><strong>Die Syncronisation ist fehlgeschlagen.</strong></p>
                            <p x-text="error"></p>
                        </div>
                    </template>
                    <template x-if="synchronizing">
                        <div class="px-6 py-4 space-y-1 bg-gray-100 text-gray-950 border-t border-gray-200 flex justify-between items-center">
                            <p class="font-semibold">Synchronisiert...</p>
                            <x-icons.loader class="w-6 h-6 animate animate-spin" />
                        </div>
                    </template>
                    <template x-if="execution">
                        <div class="px-6 py-4 space-y-1 bg-teal-50 text-teal-950 border-t border-teal-100">
                            <p class="font-semibold mb-2">Objekte erfolgreich synchronisiert.</p>

                            <div class="grid grid-cols-3">
                                <figure>
                                    <p
                                        class="text-xl font-semibold text-teal-700"
                                        x-text="execution.created"
                                    ></p>
                                    <figcaption>Erstellt</figcaption>
                                </figure>

                                <figure>
                                    <p
                                        class="text-xl font-semibold"
                                        x-text="execution.updated"
                                    ></p>
                                    <figcaption>Aktualisiert</figcaption>
                                </figure>

                                <figure>
                                    <p
                                        class="text-xl font-semibold text-rose-700"
                                        x-text="execution.deleted"
                                    ></p>
                                    <figcaption>Gelöscht</figcaption>
                                </figure>
                            </div>
                        </div>
                    </template>
                </div>
                <!-- End Tab Content 1 -->
            </div>

            <div :id="$id(tabId + '-content')" x-show="tabContentActive($el)" class="relative" x-cloak>
                <!-- Tab Content 2 - Replace with your content -->
                <div class="border rounded-lg shadow-sm bg-gray-50 text-neutral-900">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">API-Einstellungen</h3>
                        <p class="text-sm text-neutral-500">Verbindung zu domos-API herstellen.</p>
                    </div>

                    <div class="p-6 pt-0 space-y-2">
                        <div class="space-y-1">
                            <label
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                for="name">DOMOS-URL</label>
                            <input x-model="url"
                                    type="url" placeholder="URL" id="url"
                                   value="{{ $url }}"
                                   class="flex w-full h-10 px-3 py-2 text-sm bg-white border rounded-md peer border-neutral-300 ring-offset-background placeholder:text-neutral-400 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400 disabled:cursor-not-allowed disabled:opacity-50"/>
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                for="username"
                            >
                                API-Token (optional)
                            </label>
                            <input type="text" placeholder="XXXXXXXX..." id="token"
                                   value=""
                                   class="flex w-full h-10 px-3 py-2 text-sm bg-white border rounded-md peer border-neutral-300 ring-offset-background placeholder:text-neutral-400 focus:border-neutral-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-400 disabled:cursor-not-allowed disabled:opacity-50"/>
                        </div>
                    </div>

                    <div class="flex items-center p-6 pt-0">
                        <button type="button"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-white transition-colors duration-200 rounded-md bg-neutral-950 hover:bg-neutral-900 focus:ring-2 focus:ring-offset-2 focus:ring-neutral-900 focus:shadow-outline focus:outline-none">
                            Save password
                        </button>
                    </div>
                </div>
                <!-- End Tab Content 2 -->
            </div>

        </div>
    </div>
</div>
