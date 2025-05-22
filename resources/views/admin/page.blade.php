<?php
/** @var string $url */
/** @var ?string $token */
?>

<script src="{{ domos_plugin_url('resources/js/cdn/alpine-3.13.8.min.js') }}"></script>
<link rel="stylesheet" href="{{ domos_plugin_url('public/css/admin.css') }}" />

<div
    class="w-full mx-auto flex flex-col items-center justify-center p-10 space-y-10"
    x-data="{
        url: '{{ $url }}',
        token: {{ $token ? "'$token'" : 'null'}},

        nonce: '{{ wp_create_nonce('wp_rest') }}',

        sync: {
        	synchronizing: false,
        	error: null,
        	execution: null,
		},

        settings: {
        	status: 'idle',
        	message: null,
        },

        async syncNow() {
        	if (this.sync.synchronizing) {
        		return;
			}

            // Fetch data from the server, with credentials from the current user.
            this.sync.synchronizing = true;
            this.sync.error = null;
            this.sync.execution = null;

            try {
				const response = await fetch('/wp-json/domos/admin/sync', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': this.nonce,
					},
				});

                if (!response.ok) {
                    const data = await response.json();

                    this.sync.error = `${data.message} (${data.code})`;
                } else {
                    const {created, deleted, updated} = await response.json();

                    this.sync.execution = {
                        created,
                        deleted,
                        updated,
                    };
                }
            } catch (e) {
                console.error(e);

                this.sync.error = 'Ein unbekannter Fehler ist aufgetreten.';
            }

            this.sync.synchronizing = false;
        },

        async saveSettings() {
        	if (this.settings.status === 'saving') {
				return;
			}

			this.settings.status = 'saving';
			this.settings.message = null;

			try {
				const response = await fetch('/wp-json/domos/admin/save-api-settings', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': this.nonce,
					},
					body: JSON.stringify({
						url: this.url,
						token: this.token,
					}),
				});

				const data = await response.json();

				if (!response.ok) {
					this.settings.message = `${data.message} (${data.code})`;
					this.settings.status = 'error';
				} else {
					this.settings.message = data.message ?? 'Einstellungen erfolgreich gespeichert.';
					this.settings.status = 'saved';
				}
			} catch (e) {
				console.error(e);

				this.settings.message = 'Ein unbekannter Fehler ist aufgetreten.';
				this.settings.status = 'idle';
			}
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

			{{-- Sync Page --}}
            <div :id="$id(tabId + '-content')" x-show="tabContentActive($el)" class="relative">
                <div class="border rounded-lg shadow-sm bg-gray-50 text-gray-900 overflow-hidden">
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">Sync</h3>
                        <p class="text-sm text-gray-500">Synchronisiere WordPress mit deiner domos-Instanz.</p>
                    </div>
                    <div class="p-6 pt-0 space-y-2">
                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-white transition-colors duration-200 rounded-md bg-gray-950 hover:bg-gray-900 focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 focus:shadow-outline focus:outline-none"
                            @click="syncNow"
                            :disabled="sync.synchronizing"
                        >
                            <span x-show="!sync.synchronizing">Jetzt synchronisieren</span>
                            <span x-show="sync.synchronizing">Synchronisiert...</span>
                        </button>
                    </div>
                    <template x-if="sync.error">
                        <div class="px-6 py-4 space-y-1 bg-rose-200 text-rose-950 border-t border-rose-300">
                            <p><strong>Die Synchronisation ist fehlgeschlagen.</strong></p>
                            <p x-text="sync.error"></p>
                        </div>
                    </template>
                    <template x-if="sync.synchronizing">
                        <div class="px-6 py-4 space-y-1 bg-gray-100 text-gray-950 border-t border-gray-200 flex justify-between items-center">
                            <p class="font-semibold">Synchronisiert...</p>
                            <x-icons.loader class="w-6 h-6 animate animate-spin" />
                        </div>
                    </template>
                    <template x-if="sync.execution">
                        <div class="px-6 py-4 space-y-1 bg-teal-50 text-teal-950 border-t border-teal-100">
                            <p class="font-semibold mb-2">Objekte erfolgreich synchronisiert.</p>

                            <div class="grid grid-cols-3">
                                <figure>
                                    <p
                                        class="text-xl font-semibold text-teal-700"
                                        x-text="sync.execution.created"
                                    ></p>
                                    <figcaption>Erstellt</figcaption>
                                </figure>

                                <figure>
                                    <p
                                        class="text-xl font-semibold"
                                        x-text="sync.execution.updated"
                                    ></p>
                                    <figcaption>Aktualisiert</figcaption>
                                </figure>

                                <figure>
                                    <p
                                        class="text-xl font-semibold text-rose-700"
                                        x-text="sync.execution.deleted"
                                    ></p>
                                    <figcaption>Gelöscht</figcaption>
                                </figure>
                            </div>
                        </div>
                    </template>
                </div>
            </div>


			{{-- Settings Page --}}
            <div :id="$id(tabId + '-content')" x-show="tabContentActive($el)" class="relative" x-cloak>
                <form
					class="border rounded-lg shadow-sm overflow-hidden bg-gray-50 text-gray-900"
					@submit.prevent="saveSettings"
				>
                    <div class="flex flex-col space-y-1.5 p-6">
                        <h3 class="text-lg font-semibold leading-none tracking-tight">API-Einstellungen</h3>
                        <p class="text-sm text-gray-500">Verbindung zu domos-API herstellen.</p>
                    </div>

                    <div class="p-6 pt-0 space-y-2">
                        <div class="space-y-1">
                            <label
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                for="name">DOMOS-URL</label>
                            <input x-model="url"
								   type="url"
								   placeholder="URL"
								   id="url"
								   required
                                   value="{{ $url }}"
                                   class="flex w-full h-10 px-3 py-2 text-sm bg-white border rounded-md peer border-gray-300 ring-offset-background placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 disabled:cursor-not-allowed disabled:opacity-50"/>
                        </div>
                        <div class="space-y-1">
                            <label
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                for="username"
                            >
                                API-Token (optional)
                            </label>
                            <input type="text" placeholder="XXXXXXXX..." id="token"
                                   value="{{ $token }}"
								   x-model="token"
                                   class="flex w-full h-10 px-3 py-2 text-sm bg-white border rounded-md peer border-gray-300 ring-offset-background placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 disabled:cursor-not-allowed disabled:opacity-50"/>
                        </div>
                    </div>

                    <div class="flex items-center p-6 pt-0">
                        <button type="submit"
                                class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-white transition-colors duration-200 rounded-md bg-gray-950 hover:bg-gray-900 focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 focus:shadow-outline focus:outline-none">
                            Speichern
                        </button>
                    </div>

					<template x-if="settings.status === 'error'">
						<div class="px-6 py-4 space-y-1 bg-rose-200 text-rose-950 border-t border-rose-300">
							<p><strong>Die Einstellungen konnten nicht gespeichert werden.</strong></p>
							<p x-text="settings.message"></p>
						</div>
					</template>
					<template x-if="settings.status === 'saving'">
                        <div class="px-6 py-4 space-y-1 bg-gray-100 text-gray-950 border-t border-gray-200 flex justify-between items-center">
                            <p class="font-semibold">Speichert...</p>
                            <x-icons.loader class="w-6 h-6 animate animate-spin" />
                        </div>
                    </template>
					<template x-if="settings.status === 'saved'">
						<div class="flex justify-between items-center px-6 py-4 space-y-1 bg-teal-50 text-teal-950 border-t border-teal-100">
							<div>
								<p><strong>Einstellungen erfolgreich gespeichert.</strong></p>
								<p x-text="settings.message"></p>
							</div>
							<x-icons.check class="w-6 h-6 flex-shrink-0 text-teal-500 rounded-full border-2 bg-teal-100 p-1 border-teal-500 stroke-[4px]" />
						</div>
					</template>
                </form>
            </div>
        </div>
    </div>
</div>
