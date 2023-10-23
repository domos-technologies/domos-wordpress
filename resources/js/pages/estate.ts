import { Alpine, startWhenReady } from '@/alpine';
import { plugin as lottiePlugin } from '@/components/lottie-icon';
import { plugin as sliderPlugin } from '@/components/slider';
import { plugin as lightboxPlugin } from '@/components/lightbox';

Alpine.plugin(lottiePlugin);
Alpine.plugin(sliderPlugin);
Alpine.plugin(lightboxPlugin);

startWhenReady();

