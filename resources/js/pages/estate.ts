import { Alpine, startWhenReady } from '@/alpine';
import { plugin as lottiePlugin } from '@/components/lottie-icon';
import { plugin as sliderPlugin } from '@/components/slider';
import { plugin as lightboxPlugin } from '@/components/lightbox';
import Autosize from '@marcreichel/alpine-autosize';

Alpine.plugin(lottiePlugin);
Alpine.plugin(sliderPlugin);
Alpine.plugin(lightboxPlugin);
Alpine.plugin(Autosize);

startWhenReady();
