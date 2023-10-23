import { Alpine, startWhenReady } from '@/alpine';
import { plugin as lottiePlugin } from '@/components/lottie-icon';

Alpine.plugin(lottiePlugin);

startWhenReady();

