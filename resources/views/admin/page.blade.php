<?php
/** @var string $url */
/** @var ?string $token */
/** @var string[] $languages */
/** @var string $default_language */
?>

<script src="{{ domos_plugin_url('resources/js/cdn/alpine-3.13.8.min.js') }}"></script>
{{-- The version query matters: without it a browser keeps serving the previous
     release's stylesheet after an upgrade, so new classes (a new panel, say)
     render unstyled until the cache expires. --}}
<link rel="stylesheet" href="{{ domos_plugin_url('public/css/admin.css') }}?ver={{ DOMOS_CORE_VERSION }}" />

<div
    class="w-full mx-auto flex flex-col items-center justify-center p-10 space-y-10"
    x-data="{
        url: @js($url),
        token: @js($token),
        languages: @js($languages ?? []),
        default_language: @js($default_language ?? 'de'),

        nonce: '{{ wp_create_nonce('wp_rest') }}',

        sync: {
        	synchronizing: false,
        	error: null,
        	run: null,
		},

        settings: {
        	status: 'idle',
        	message: null,
        },

        async init() {
            // A reload must not hide a running sync: the background queue keeps
            // working regardless of who is watching, so pick the run up again
            // instead of showing an idle screen.
            const { run } = await this.fetchSyncStatus();

            if (!run || run.status !== 'running') {
                return;
            }

            this.sync.run = run;
            this.sync.synchronizing = true;

            await this.pollSyncStatus();

            this.sync.synchronizing = false;
        },

        /**
         * Returns ok: false when the request itself failed, so the caller can keep
         * polling instead of treating it as 'no run'.
         */
        async fetchSyncStatus() {
            try {
                const response = await fetch('{{ rest_url('domos/admin/sync-status') }}', {
                    headers: { 'X-WP-Nonce': this.nonce },
                });

                if (!response.ok) {
                    return { ok: false, run: null };
                }

                return { ok: true, run: await response.json() };
            } catch (e) {
                return { ok: false, run: null };
            }
        },

        async syncNow() {
            // Deliberately no early return for a tracked run: a run whose worker
            // died keeps reporting as running, and starting a fresh one is the only
            // way out of that state. A run that really is alive answers
            // already_running and is picked up again below.
            this.sync.synchronizing = true;
            this.sync.error = null;
            this.sync.run = null;

            try {
                const response = await fetch('{{ rest_url('domos/admin/sync') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce,
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(`${data.message} (${data.code})`);
                }

                this.sync.run = data.run;

                await this.pollSyncStatus();
            } catch (e) {
                console.error(e);
                this.sync.error = e.message ?? 'Ein unbekannter Fehler ist aufgetreten.';
            }

            this.sync.synchronizing = false;
        },

        async cancelSync() {
            this.sync.error = null;

            try {
                const response = await fetch('{{ rest_url('domos/admin/sync-cancel') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': this.nonce,
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(`${data.message} (${data.code})`);
                }

                // The run is finished now, so the poll loop ends by itself and the
                // cancelled panel replaces the progress one.
                this.sync.run = data.run;
                this.sync.synchronizing = false;
            } catch (e) {
                console.error(e);
                this.sync.error = e.message ?? 'Ein unbekannter Fehler ist aufgetreten.';
            }
        },

        /**
         * Is the run we are watching one that stopped moving? The queue's worker is
         * reached through a request to the site itself, and its healthcheck cron is
         * the only thing that could still pick a dead run up - which is part of what
         * is broken when this happens. The threshold comes from the server
         * (SyncRunStatus::STALL_AFTER) so both sides agree on when a run is dead.
         */
        isStalled() {
            const run = this.sync.run;

            if (!run || run.status !== 'running') {
                return false;
            }

            const lastProgress = (run.progress_at ?? run.started_at ?? 0) * 1000;
            const threshold = (run.stall_after ?? 900) * 1000;

            return lastProgress > 0 && (Date.now() - lastProgress) > threshold;
        },

        async pollSyncStatus() {
            // Poll for up to 60 minutes; the queue survives independently either way.
            for (let i = 0; i < 1800; i++) {
                await new Promise((resolve) => setTimeout(resolve, 2000));

                const { ok, run } = await this.fetchSyncStatus();

                if (!ok) {
                    // transient network error — keep polling
                    continue;
                }

                if (run) {
                    this.sync.run = run;
                }

                if (!run || run.status !== 'running') {
                    return;
                }
            }
        },

        async saveSettings() {
        	if (this.settings.status === 'saving') {
				return;
			}

			this.settings.status = 'saving';
			this.settings.message = null;

			try {
				const response = await fetch('{{ rest_url('domos/admin/save-api-settings') }}', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json',
						'X-WP-Nonce': this.nonce,
					},
					body: JSON.stringify({
						url: this.url,
						token: this.token,
						languages: this.languages,
					}),
				});

				const data = await response.json();

				if (!response.ok) {
					this.settings.message = `${data.message} (${data.code})`;
					this.settings.status = 'error';
				} else {
					this.settings.message = data.message ?? 'Einstellungen erfolgreich gespeichert.';
					this.settings.status = 'saved';

                    if (data.languages) {
                        this.languages = data.languages;
                    }

                    if (data.default_language) {
                        this.default_language = data.default_language;
                    }
				}
			} catch (e) {
				console.error(e);

				this.settings.message = 'Ein unbekannter Fehler ist aufgetreten.';
				this.settings.status = 'idle';
			}
        }
    }"
>
    <a href="https://immocore.com" target="_blank">
        <img src="https://assets.immocore.com/logo/logo.svg" alt="immocore Logo" class="h-8"/>
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
                        <p class="text-sm text-gray-500">Synchronisiere WordPress mit deiner immocore-Instanz.</p>
                    </div>
                    <div class="p-6 pt-0 space-y-2">
                        <button
                            type="button"
                            class="inline-flex w-full items-center justify-center px-4 py-2 text-sm font-medium tracking-wide text-white transition-colors duration-200 rounded-md bg-gray-950 hover:bg-gray-900 focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 focus:shadow-outline focus:outline-none"
                            @click="syncNow"
                        >
                            <span x-show="!sync.synchronizing">Jetzt synchronisieren</span>
                            <span x-show="sync.synchronizing">Synchronisiert...</span>
                        </button>
                    </div>
                    <template x-if="sync.synchronizing && sync.run && sync.run.status === 'running'">
                        <div class="px-6 py-4 space-y-1 bg-gray-100 text-gray-950 border-t border-gray-200 ">
                            <div class="flex justify-between items-center gap-1">
                                <p class="font-semibold">Synchronisiert... (<span x-text="sync.run.processed"></span>/<span x-text="sync.run.total"></span>)</p>
                                <x-icons.loader class="w-6 h-6 animate animate-spin" />
                            </div>

                            <template x-if="isStalled()">
                                <p class="text-sm text-amber-700">
                                    Seit über 15 Minuten kein Fortschritt. Der Hintergrund-Worker antwortet nicht mehr -
                                    ein neuer Sync räumt den hängenden Lauf auf, oder er wird hier abgebrochen.
                                </p>
                            </template>

                            <div class="pt-2">
                                <button
                                    type="button"
                                    class="inline-flex items-center justify-center px-3 py-1.5 text-sm font-medium rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400"
                                    @click="cancelSync"
                                >
                                    Sync abbrechen
                                </button>
                            </div>
                        </div>
                    </template>
                    <template x-if="sync.run && sync.run.status === 'finished' && !sync.run.skipped && !sync.run.cancelled">
                        <div class="px-6 py-4 space-y-1 bg-teal-50 text-teal-950 border-t border-teal-100">
                            <p class="font-semibold mb-2">Objekte erfolgreich synchronisiert</p>

                            <div class="grid grid-cols-3">
                                <figure>
                                    <p
                                        class="text-xl font-semibold text-teal-700"
                                        x-text="sync.run.created"
                                    ></p>
                                    <figcaption>Erstellt</figcaption>
                                </figure>

                                <figure>
                                    <p
                                        class="text-xl font-semibold"
                                        x-text="sync.run.updated"
                                    ></p>
                                    <figcaption>Aktualisiert</figcaption>
                                </figure>

                                <figure>
                                    <p
                                        class="text-xl font-semibold text-rose-700"
                                        x-text="sync.run.deleted"
                                    ></p>
                                    <figcaption>Gelöscht</figcaption>
                                </figure>
                            </div>

                            <template x-if="sync.run.failures && Object.keys(sync.run.failures).length > 0">
                                <p class="text-sm text-rose-700 mt-2">
                                    Fehlgeschlagen: <span x-text="Object.keys(sync.run.failures).length"></span>
                                </p>
                            </template>
                        </div>
                    </template>

                    <template x-if="sync.run && sync.run.status === 'finished' && sync.run.skipped">
                        <div class="px-6 py-4 space-y-1 bg-amber-50 text-amber-950 border-t border-amber-100">
                            <p class="font-semibold">Nichts zu synchronisieren</p>

                            <p class="text-sm">
                                In immocore gibt es derzeit keine veröffentlichten Objekte mit Exposé, die
                                synchronisiert werden könnten.
                            </p>

                            <p class="text-sm">
                                Bereits veröffentlichte Objekte bleiben unverändert. Das ist ein
                                Schutzmechanismus: ein leeres, pausiertes oder falsch konfiguriertes Portfolio
                                darf die Website nicht leeren.
                            </p>

                            <p class="text-sm">
                                Sollen die Objekte von der Website verschwinden, müssen sie manuell in WordPress
                                gelöscht werden.
                            </p>
                        </div>
                    </template>

                    <template x-if="sync.run && sync.run.status === 'finished' && sync.run.cancelled">
                        <div class="px-6 py-4 space-y-1 bg-gray-100 text-gray-950 border-t border-gray-200">
                            <p class="font-semibold">Sync abgebrochen</p>

                            <p class="text-sm">
                                <span x-text="sync.run.processed"></span> von <span x-text="sync.run.total"></span>
                                Objekten wurden vor dem Abbruch verarbeitet.
                            </p>

                            <p class="text-sm">
                                Bereits importierte Objekte bleiben unverändert, es wurde nichts entfernt. Der nächste
                                Sync arbeitet die restlichen Objekte ab.
                            </p>
                        </div>
                    </template>

                    <template x-if="sync.error">
                        <div class="px-6 py-4 space-y-1 bg-rose-200 text-rose-950 border-t border-rose-300">
                            <p><strong>Die Synchronisation ist fehlgeschlagen.</strong></p>
                            <p x-text="sync.error"></p>
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
                        <p class="text-sm text-gray-500">Verbindung zu immocore-API herstellen.</p>
                    </div>

                    <div class="p-6 pt-0 space-y-2">
                        <div class="space-y-1">
                            <label
                                class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                for="name">immocore-URL</label>
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
                        <div class="space-y-1 pt-5 pb-2">
                            <div class="flex items-center justify-between mb-2">
                                <label
                                    class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
                                    for="languages"
                                >
                                    Sprachen
                                </label>
                                <a 
                                    href="https://developers.immocore.com/immocore-api-documentation#model/language" 
                                    target="_blank"
                                    class="flex items-center gap-1.5"
                                >
                                    <span>Verfügbare Sprachen</span>
                                    <x-icons.chevron-right class="w-3 h-3" />
                                </a>
                            </div>
                            <div class="flex items-center">
                                <input 
                                    type="text" 
                                    disabled
                                    class="flex w-full h-10 px-3 py-2 text-sm bg-white border rounded-md peer border-gray-300 ring-offset-background placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 disabled:cursor-not-allowed disabled:opacity-50"
                                    x-bind:value="default_language + ' (Standard Exposé-Sprache)'"
                                />
                                <button type="button" disabled class="text-sm text-gray-300 hover:text-gray-500 w-8 h-full flex items-center justify-center">
                                    &nbsp;
                                </button>
                            </div>
                            <datalist id="country">
                                @foreach(\Domos\Core\Options\LanguagesOption::getSuggestions() as $suggestion)
                                    <option value="{{ $suggestion }}"></option>
                                @endforeach
                            </datalist>
                            <template x-for="(language, index) in languages" :key="index">
                                <div class="flex items-center">
                                    <input 
                                        name="languages[]"
                                        type="text" 
                                        x-model="languages[index]" 
                                        placeholder="z.B. de, en, fr, etc." 
                                        class="flex w-full h-10 px-3 py-2 text-sm bg-white border rounded-md peer border-gray-300 ring-offset-background placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 disabled:cursor-not-allowed disabled:opacity-50"
                                        list="country"
                                        maxlength="2"
                                        minlength="2"
                                        pattern="[a-z]{2}"
                                    />
                                    <button type="button" @click="languages.splice(languages.indexOf(language), 1)" class="text-sm text-gray-300 hover:text-gray-500 w-8 h-full flex items-center justify-center">
                                        x
                                    </button>
                                </div>
                            </template>
                            <button 
                                class="block w-full text-center text-xs text-gray-400 hover:text-gray-500 py-2"
                                type="button" 
                                @click="languages.push('')"
                                x-text="languages.length === 0 ? 'Sprache hinzufügen +' : 'Weitere Sprache hinzufügen +'"
                            >
                                Sprache hinzufügen +
                            </button>
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
