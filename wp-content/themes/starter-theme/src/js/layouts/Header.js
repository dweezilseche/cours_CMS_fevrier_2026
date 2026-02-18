import gsap from 'gsap';

export default class Header {
  constructor({ lenis }) {
    // DOM
    this.DOM = {
      header: document.querySelector('#header'),
      headerPrimary: document.querySelector('.header__container'),
      headerSecondary: document.querySelector('.header__secondary'),
      headerSecondaryMobile: document.querySelector('.header__secondary-mobile'),
      shopLink: document.querySelector('.header__nav-menu-link.is-shop'),
      cartButton: document.querySelector('.header__nav-menu-button.is-cart'),
      burgerMenu: document.querySelector('.burger-menu'),
      collection: document.querySelector('.header__secondary-mobile-collection'),
      openCollectionButton: document.querySelector('[data-open-collection-button]'),
    };

    // Options
    this.lenis = lenis;
    this.lastScrollY = this.lenis?.scroll || 0;
    this.scrollThreshold = 50;
    this.isMenuOpen = false;

    if (!this.DOM.header) return;

    this.addEventListeners();
  }

  /**
   * Events
   */

  addEventListeners() {
    this.openMenu = this.openMenu.bind(this);
    this.closeMenu = this.closeMenu.bind(this);
    this.toggleMenu = this.toggleMenu.bind(this);
  }

  /**
   * Menu states for mobile
   */
  toggleMenu(event) {
    event?.preventDefault();

    this.isMenuOpen = !this.isMenuOpen;

    if (this.isMenuOpen) {
      this.openMenu();
    } else {
      this.closeMenu();
    }
  }

  openMenu() {
    this.DOM.header.classList.add('is-open');
    document.body.style.overflow = 'hidden';

    // Set lenis prevent for mobile menu
    if (window.innerWidth < 768) {
      this.setLenisPrevent(true);
    }
  }

  closeMenu() {
    this.DOM.header.classList.remove('is-open');
    document.body.style.overflow = '';

    // Remove lenis prevent when menu is closed
    this.setLenisPrevent(false);

    this.isMenuOpen = false;
  }

  /**
   * Resize
   */
  onScroll({ scroll, direction }) {
    if (this.isMenuOpen) return;

    // If we scroll down
    if (direction === 1) {
      // Fix the iPhone issue with Safari - only hide if scroll > 0
      if (scroll > 100) {
        this.DOM.header.classList.add('has-scrolled');
        this.DOM.header.classList.remove('is-visible');
      }
    }
    // If we scroll up
    else if (direction === -1) {
      this.DOM.header.classList.remove('has-scrolled');
      this.DOM.header.classList.add('is-visible');

      // If we scroll up to the top
      if (this.lastScrollY <= 10) {
        this.DOM.header.classList.remove('is-visible');
      }
    }

    this.lastScrollY = scroll;
  }

  /**
   * Resize
   */
  onResize() {
    this.closeMenu();
  }

  /**
   * Update theme
   */
  updateTheme(page) {
    if (!page) return;

    // page.dataset.header === 'dark'
    //   ? this.DOM.header.classList.add('is-dark')
    //   : this.DOM.header.classList.remove('is-dark')
  }

  /**
   * Reset
   */

  reset() {
    this.closeMenu();

    if (this.lenis?.scroll <= this.scrollThreshold) {
      this.DOM.header.classList.remove('has-scroll');
    }
  }

  /**
   * Lenis prevent management
   */
  setLenisPrevent(prevent) {
    if (this.DOM.header) {
      if (prevent) {
        this.DOM.header.setAttribute('data-lenis-prevent', 'true');
      } else {
        this.DOM.header.removeAttribute('data-lenis-prevent');
      }
    }
  }

  /**
   * Destroy
   */

  destroy() {
    if (this.DOM.header) {
      this.DOM.header.removeEventListener('click', this.toggleMenu);
    }
    if (this.DOM.burgerMenu) {
      this.DOM.burgerMenu.removeEventListener('click', this.toggleSecondaryMobile);
    }
  }
}
