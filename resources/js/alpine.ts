import AlpineCore from 'alpinejs';
import persist from '@alpinejs/persist';

export const Alpine = AlpineCore;

export function startWhenReady() {
	document.addEventListener('DOMContentLoaded', () => {
		Alpine.start();
	});
}

window.addEventListener('alpine:init', () => {
	Alpine.plugin(persist);
});
