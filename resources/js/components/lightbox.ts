import { Luminous } from 'luminous-lightbox';

export function plugin(Alpine) {
    Alpine.directive('lightbox', (el, { value }, { cleanup }) => {
        const luminous = new Luminous(el, {
            appendToNode: document.body,
        });

        cleanup(() => {
            luminous.destroy();
        });
    });
}
