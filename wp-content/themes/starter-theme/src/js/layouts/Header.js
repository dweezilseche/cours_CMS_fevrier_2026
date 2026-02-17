import gsap from 'gsap'
import DrawSVGPlugin from 'gsap/DrawSVGPlugin'
gsap.registerPlugin(DrawSVGPlugin)

export default class Header {
  constructor({ lenis }) {
    // DOM
    this.DOM = { 
      header: document.querySelector('#header'),
      menuButton: document.querySelector('.menu-button'),
      headerNavButtons: [...document.querySelectorAll('.header-nav__button')],
      headerSubmenus: [...document.querySelectorAll('.header-submenu')],
      headerSubmenuBackButtons: [...document.querySelectorAll('.header-submenu__head__back')],
    }

    // Options
    this.lenis = lenis
    this.lastScrollY = this.lenis?.scroll || 0
    this.scrollThreshold = 50
    this.isMenuOpen = false
    this.currentSubmenu = null

    if (!this.DOM.header) return

    this.addEventListeners()
  }

  /**
   * Events
   */

  addEventListeners() {
    this.openMenu = this.openMenu.bind(this)
    this.closeMenu = this.closeMenu.bind(this)
    this.toggleMenu = this.toggleMenu.bind(this)

    this.toggleSubmenu = this.toggleSubmenu.bind(this)
    this.openSubmenu = this.openSubmenu.bind(this)
    this.closeSubmenu = this.closeSubmenu.bind(this)
    this.handleClickOutside = this.handleClickOutside.bind(this)
    this.closeAllSubmenus = this.closeAllSubmenus.bind(this)

    this.DOM.menuButton.addEventListener('click', this.toggleMenu)

    this.DOM.headerSubmenuBackButtons?.forEach(button => {
      button.addEventListener('click', this.closeAllSubmenus)
    })

    this.DOM.headerNavButtons?.forEach(button => {
      button.addEventListener('click', this.toggleSubmenu)
    })

    // Click outside of the submenu
    document.addEventListener('click', this.handleClickOutside)
  }

  /**
   * Menu states for mobile
   */
  toggleMenu(event) {
    event?.preventDefault()

    this.isMenuOpen = !this.isMenuOpen

    if (this.isMenuOpen) {
      this.openMenu()
    } else {
      this.closeMenu()
    }
  }

  openMenu() {
    this.DOM.header.classList.add('is-menu-open')
    this.DOM.menuButton.setAttribute('aria-label', 'Fermer le menu')
    this.DOM.menuButton.setAttribute('aria-expanded', 'true')

    document.body.style.overflow = 'hidden'
    
    // Set lenis prevent for mobile menu
    if (window.innerWidth < 768) {
      this.setLenisPrevent(true)
    }
  }

  closeMenu() {
    this.DOM.header.classList.remove('is-menu-open')
    this.DOM.menuButton.setAttribute('aria-label', 'Ouvrir le menu')
    this.DOM.menuButton.setAttribute('aria-expanded', 'false')

    document.body.style.overflow = ''
    
    // Remove lenis prevent when menu is closed
    this.setLenisPrevent(false)
    
    // Close all submenus when menu is closed
    this.closeAllSubmenus()

    this.isMenuOpen = false
  }

  /**
   * Submenus
   */
  toggleSubmenu(event) {
    event?.preventDefault()
    event?.stopPropagation()

    const currentButton = event.currentTarget
    const currentButtonIndex = this.DOM.headerNavButtons.indexOf(currentButton)
    const relatedSubmenu = this.DOM.headerSubmenus[currentButtonIndex]

    if (!relatedSubmenu) return

    if (this.currentSubmenu === relatedSubmenu) {
      this.closeSubmenu(relatedSubmenu, currentButton)
    } else {
      this.openSubmenu(relatedSubmenu, currentButton)
    }
  }

  openSubmenu(submenu, button) {
    // If submenu is open, close it
    if (this.currentSubmenu && this.currentSubmenu !== submenu) {
      this.currentSubmenu.classList.remove('is-open')
      // Remove the active class from the previous button
      const previousButtonIndex = this.DOM.headerSubmenus.indexOf(this.currentSubmenu)
      if (previousButtonIndex !== -1 && this.DOM.headerNavButtons[previousButtonIndex]) {
        this.DOM.headerNavButtons[previousButtonIndex].classList.remove('is-active')
      }
    }

    // Open the new submenu
    this.currentSubmenu = submenu
    this.currentSubmenu.classList.add('is-open')
    button?.classList.add('is-active')
  }

  closeSubmenu(submenu, button) {
    submenu.classList.remove('is-open')
    button?.classList.remove('is-active')
    this.currentSubmenu = null
  }

  closeAllSubmenus() {
    if (this.currentSubmenu) {
      const currentButtonIndex = this.DOM.headerSubmenus.indexOf(this.currentSubmenu)
      const currentButton = this.DOM.headerNavButtons[currentButtonIndex]
      
      this.closeSubmenu(this.currentSubmenu, currentButton)
    }
  }

  handleClickOutside(event) {
    if (!this.currentSubmenu) return

    const currentButtonIndex = this.DOM.headerSubmenus.indexOf(this.currentSubmenu)
    const currentButton = this.DOM.headerNavButtons[currentButtonIndex]

    // Check if the click is outside the button AND the submenu
    const isClickOutsideButton = currentButton && !currentButton.contains(event.target)
    const isClickOutsideSubmenu = !this.currentSubmenu.contains(event.target)

    if (isClickOutsideButton && isClickOutsideSubmenu) {
      this.closeAllSubmenus()
    }
  }

  /**
   * Resize
   */
  onScroll({ scroll, direction }) {
    if (this.isMenuOpen) return

    // Si on scrolle vers le bas
    if (direction === 1) {
      if (scroll >= this.scrollThreshold) {
        this.DOM.header.classList.add('has-scroll')
      }
    } 
    // Si on scrolle vers le haut
    else if (direction === -1) {
      if (scroll < this.scrollThreshold) {
        this.DOM.header.classList.remove('has-scroll')
      }
    }
  
    this.lastScrollY = scroll
  }

  /**
   * Resize
   */
  onResize() {
    this.closeMenu()
  }

  /**
   * Reset
   */

  reset() {
    this.closeMenu()
    this.closeAllSubmenus()

    if (this.lenis?.scroll <= this.scrollThreshold) {
      this.DOM.header.classList.remove('has-scroll')
    }
  }

   /**
   * Lenis prevent management
   */
   setLenisPrevent(prevent) {
    if (this.DOM.header) {
      if (prevent) {
        this.DOM.header.setAttribute('data-lenis-prevent', 'true')
      } else {
        this.DOM.header.removeAttribute('data-lenis-prevent')
      }
    }
  }

  /**
   * Destroy
   */

  destroy() {
    // Retirer les event listeners
    this.DOM.headerNavButtons?.forEach(button => {
      button.removeEventListener('click', this.toggleSubmenu)
    })
    
    document.removeEventListener('click', this.handleClickOutside)
    
    // Fermer tous les sous-menus ouverts
    this.closeAllSubmenus()
  }
}
