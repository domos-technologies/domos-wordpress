import { Luminous } from 'luminous-lightbox';
import {isLightboxEnabled} from "@/options";

export function plugin(Alpine) {
    Alpine.directive('lightbox', (el, { value }, { cleanup }) => {
		if (!isLightboxEnabled()) {
			return;
		}

        const luminous = new Luminous(el, {
            appendToNode: document.body,

        });

        cleanup(() => {
            luminous.destroy();
        });
    });
}
