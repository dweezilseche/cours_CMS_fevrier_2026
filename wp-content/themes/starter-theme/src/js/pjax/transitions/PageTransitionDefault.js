import { Transition } from '@unseenco/taxi'
import gsap from 'gsap'
import { ScrollTrigger } from 'gsap/ScrollTrigger'
gsap.registerPlugin(ScrollTrigger)

export default class PageTransitionDefault extends Transition {
  onEnter({ to, trigger, done }) {
    const from = to.parentNode.children[0]

    window.lenis?.stop()

    gsap.set(to, {
      position: 'fixed',
      inset: 0,
      zIndex: 1000,
      transformOrigin: 'top center'
    })

    const timeline = gsap.timeline({
      defaults: { 
        ease: 'expo.inOut', 
        duration: 1.5
      },
      onComplete: () => {
        done()

        window.scrollTo(0, 0)
        
        gsap.set(to, { clearProps: 'all' })

        from.remove()

        document.documentElement.style.cursor = ''
        document.body.className = ''

        window.lenis?.start()
        window.lenis?.resize()
      }
    })

    timeline.fromTo(to, {
      opacity: 0
    }, {
      opacity: 1,
      ease: 'power3.out',
      duration: 0.5
    }, 0)   

    return timeline
  }

  onLeave({ from, trigger, done }) {
    document.documentElement.style.cursor = 'wait'
    document.body.className = 'no-events oh'
    from.classList.add('no-events')
    from.classList.add('oh')
    
    done()
  }
}
