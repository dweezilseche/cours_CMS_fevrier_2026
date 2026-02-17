export default class MediasAsyncLoad {
  constructor() {
    this.DOM = {
      pictures: document.querySelectorAll('picture[data-lazy]')
    }

    this.observer = new window.IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const source = entry.target.querySelector('source')
          const img = entry.target.querySelector('img')

          if (!source.srcset || !img.src) {
            // Picture srcset
            source.srcset = source.getAttribute('data-srcset')
            source.onload = _ => entry.target.classList.add('loaded')
          
            // Picture img
            img.src = img.getAttribute('data-src')
            img.onload = _ => entry.target.classList.add('loaded')
          }
        }
      })
    })

    this.DOM.pictures.forEach(picture => {
      this.observer.observe(picture)
    })
  }
}
