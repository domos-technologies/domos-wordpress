import { Luminous, LuminousGallery } from 'luminous-lightbox';
import {isLightboxEnabled} from "@/options";

export function plugin(Alpine) {
	Alpine.directive('lightbox', (el, { expression }, { effect, cleanup }) => {
		if (!isLightboxEnabled()) {
			return;
		}

		let luminous;

		if (expression && expression !== '') {
			const elements = el.querySelectorAll(expression);

			luminous = new LuminousGallery(
				elements,
				{
					appendToNode: document.body,
					arrowNavigation: false,
				},
				{
					onOpen: () => {
						console.log('open');
						document.body.classList.add('overflow-hidden');
					},
					onClose: () => {
						console.log('close');
						document.body.classList.remove('overflow-hidden');
					},
				}
			);
		} else {
			luminous = new Luminous(el, {
				appendToNode: document.body,
				onOpen: () => {
					document.body.classList.add('overflow-hidden');
				},
				onClose: () => {
					document.body.classList.remove('overflow-hidden');
				},
			});
		}

		cleanup(() => {
			luminous.destroy();
		});
	});
}
