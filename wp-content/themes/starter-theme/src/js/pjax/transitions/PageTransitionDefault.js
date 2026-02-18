import { Transition } from '@unseenco/taxi';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

export default class PageTransitionDefault extends Transition {
  onEnter({ to, done }) {
    const parent = to.parentNode;
    const from = parent.children[0] === to ? parent.children[1] : parent.children[0];

    window.lenis?.stop();

    // Éviter l'empilement : les deux [data-taxi-view] sont en overlay pendant la transition
    // (Taxi avec removeOldContent: false ajoute la nouvelle sans retirer l'ancienne)
    const wrapHeight = from.offsetHeight;
    gsap.set(parent, { position: 'relative', minHeight: wrapHeight });
    gsap.set([from, to], {
      position: 'absolute',
      top: 0,
      left: 0,
      right: 0,
      width: '100%',
      opacity: 0,
    });

    if (window.location.hash === '') window.scrollTo(0, 0);

    const tl = gsap.timeline({
      onComplete: () => {
        window.scrollTo(0, 0);

        from.remove();
        gsap.set(to, { clearProps: 'all' });
        gsap.set(parent, { clearProps: 'minHeight,position' });

        // Remettre le header en position et opacité normales (animé en onLeave)
        const header = document.getElementById('header');
        if (header) gsap.set(header, { clearProps: 'all' });

        document.documentElement.style.cursor = '';
        document.body.className = '';

        window.lenis?.start();
        window.lenis?.resize();
        ScrollTrigger.refresh();

        // Ré-init galeries produit WooCommerce (FlexSlider) après navigation Taxi
        document.dispatchEvent(new CustomEvent('taxi:afterEnter'));

        done();
      },
    });

    tl.to(to, { opacity: 1, ease: 'power3.out', duration: 1.25 }, 0);

    return tl;
  }

  onLeave({ from, done }) {
    document.documentElement.style.cursor = 'wait';

    const tl = gsap.timeline({ onComplete: done });

    tl.to(from, { opacity: 0, duration: 0 }, 0);
    tl.to('#header', { yPercent: -100, opacity: 0, ease: 'expo.inOut', duration: 1.25 }, 0);

    return tl;
  }
}
