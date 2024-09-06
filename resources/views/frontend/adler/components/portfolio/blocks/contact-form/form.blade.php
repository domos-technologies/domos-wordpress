@props(['estate'])

@php
/** @var \SchemaImmo\Estate $estate */

$privacyPolicyUrl = \Domos\Core\DOMOS::instance()->getPrivacyPolicyUrl();
@endphp

<form
	x-data="{
		status: 'idle',
		message: null,

		data: {
			name: '',
			email: '',
			phone: '',
			message: '',
			privacy: false
		},

		errors: {
			name: null,
			email: null,
			phone: null,
			message: null,
			privacy: null
		},

		closePopup() {
			this.message = null;
		},

		async submit() {
			if (this.status === 'submitting')
				return;

			this.status = 'submitting';
			this.errors = {
				name: false,
				email: false,
				phone: false,
				message: false,
				privacy: false
			};

			try {
				const response = await fetch('{{ rest_url('domos/inquiry/submit') }}', {
					method: 'POST',
					headers: {
						'Content-Type': 'application/json'
					},
					body: JSON.stringify(this.data)
				});

				const data = await response.json();

				if (response.ok) {
					this.data = {
						name: '',
						email: '',
						phone: '',
						message: '',
						privacy: false
					};

					this.status = 'success';
					this.message = data.data.message;
				} else {
					const error = data.data;

					if (error) {
						if (error.code === 'validation') {
							for (const [field, messages] of Object.entries(error.fields)) {
								this.errors[field] = messages[0];
							}
						} else {
							this.message = error.message;
						}
					} else {
						this.message = data.message ?? 'Ein unbekannter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
					}

					this.status = 'error';
				}
			} catch (error) {
				console.error(error);

				this.message = 'Ein unbekannter Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.';
				this.status = 'error';
			}

			// If an error is not null, scroll to the input
			for (const [field, message] of Object.entries(this.errors)) {
				if (message) {
					const input = document.getElementById(field);
					const y = input.getBoundingClientRect().top + window.scrollY - 100;
					window.scroll({
						top: y,
						behavior: 'smooth'
					});
					break;
				}
			}
		}
	}"
	class="domos-inquiry-form space-y-5"
	@submit.prevent="submit"
>
	<div class="domos-inquiry-form--name">
		<x-adler::portfolio.blocks.contact-form.form.label for="name">
			Name
		</x-adler::portfolio.blocks.contact-form.form.label>
		<x-adler::portfolio.blocks.contact-form.form.input
			type="text"
			name="name"
			id="name"
			placeholder="Vor- und Nachname"
			required
			maxlength="255"
			x-model="data.name"
		/>
		<x-adler::portfolio.blocks.contact-form.form.error
			error="errors.name"
		/>
	</div>

	<div class="domos-inquiry-form--company">
		<x-adler::portfolio.blocks.contact-form.form.label for="company">
			Firma
		</x-adler::portfolio.blocks.contact-form.form.label>
		<x-adler::portfolio.blocks.contact-form.form.input
			type="text"
			name="company"
			id="company"
			placeholder="Firma"
			required
			maxlength="255"
			x-model="data.company"
		/>
		<x-adler::portfolio.blocks.contact-form.form.error
			error="errors.company"
		/>
	</div>

	<div class="domos-inquiry-form--email">
		<x-adler::portfolio.blocks.contact-form.form.label for="email">
			E-Mail
		</x-adler::portfolio.blocks.contact-form.form.label>
		<x-adler::portfolio.blocks.contact-form.form.input
			type="email"
			name="email"
			id="email"
			placeholder="E-Mail-Adresse"
			required
			maxlength="255"
			x-model="data.email"
		/>
		<x-adler::portfolio.blocks.contact-form.form.error
			error="errors.email"
		/>
	</div>

	<div class="domos-inquiry-form--phone">
		<x-adler::portfolio.blocks.contact-form.form.label for="phone">
			Telefon
		</x-adler::portfolio.blocks.contact-form.form.label>
		<x-adler::portfolio.blocks.contact-form.form.input
			type="tel"
			name="phone"
			id="phone"
			placeholder="Telefonnummer"
			required
			x-model="data.phone"
		/>
		<x-adler::portfolio.blocks.contact-form.form.error
			error="errors.phone"
		/>
	</div>

	<div class="domos-inquiry-form--message">
		<x-adler::portfolio.blocks.contact-form.form.label for="message">
			Nachricht
		</x-adler::portfolio.blocks.contact-form.form.label>
		<x-adler::portfolio.blocks.contact-form.form.textarea
			name="message"
			id="message"
			class="w-full bg-transparent [&_.fi-fo-textarea]"
			placeholder="Ihre Nachricht"
			required
			maxlength="7000"
			x-model="data.message"
			x-data="{}"
			x-autosize
		 ></x-adler::portfolio.blocks.contact-form.form.textarea>
		<x-adler::portfolio.blocks.contact-form.form.error
			error="errors.message"
		/>
	</div>

	<div class="domos-inquiry-form--privacy">
		<label for="privacy" class="inline-flex items-center">
			<input
				required
				type="checkbox"
				name="privacy"
				id="privacy"
				class="w-4 h-4 mr-2"
				x-model="data.privacy"
			/>
			<span class="text-sm font-medium leading-6 expose-text">
				Ich habe die <a class="expose-text-accent hover:opacity-70" href="{{ $privacyPolicyUrl }}">Datenschutzerklärung</a> gelesen und akzeptiere diese.<sup class="text-red-600 font-medium">*</sup>
            </span>
		</label>
	</div>

	<div class="domos-inquiry-form--submit w-full flex justify-end">
		<x-adler::portfolio.blocks.contact-form.form.button
			x-text="status === 'submitting' ? 'Wird gesendet...' : 'Anfrage senden'"
		/>
	</div>

	<template x-if="message !== null">
		<div class="fixed inset-0 bg-gray-500/70 flex items-center justify-center" @click.self="closePopup">
			<div class="bg-white max-w-lg w-full px-6 py-4">
				<header class="flex items-start justify-between mb-3">
					<h2
						class="font-semibold text-2xl"
						x-text="status === 'success' ? 'Anfrage erhalten' : 'Fehler'"
					>
					</h2>
					<button type="button" @click="closePopup">
						✕
					</button>
				</header>
				<p x-text="message"></p>
			</div>
		</div>
	</template>
</form>
