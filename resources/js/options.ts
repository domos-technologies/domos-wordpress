declare global {
	interface Window {
		DOMOS: {
			lightbox: boolean;
		};
	}
}

export function isLightboxEnabled() {
	return window.DOMOS && window.DOMOS.lightbox;
}
