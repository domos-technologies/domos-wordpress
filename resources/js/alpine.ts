import AlpineCore from 'alpinejs';
import persist from '@alpinejs/persist';

export const Alpine = AlpineCore;

// @ts-ignore
window.Alpine = AlpineCore;

export function startWhenReady() {
	document.addEventListener('DOMContentLoaded', () => {
		Alpine.start();
	});
}

window.addEventListener('alpine:init', () => {
	Alpine.plugin(persist);

	const domosEstatesElements = Array.from(document.querySelectorAll('.domos-estate'));

	for (const element of domosEstatesElements) {
		// @ts-ignore
		Alpine.initTree(element.shadowRoot);
	}
});
