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
    this.secondaryCloseTimeout = null;
    this.secondaryClassRemoveTimeout = null;
    this.secondaryCloseDelay = 150;

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
    this.openSecondary = this.openSecondary.bind(this);
    this.closeSecondary = this.closeSecondary.bind(this);
    this.toggleSecondary = this.toggleSecondary.bind(this);
    this.toggleSecondaryMobile = this.toggleSecondaryMobile.bind(this);
    this.scheduleCloseSecondary = this.scheduleCloseSecondary.bind(this);
    this.cancelCloseSecondary = this.cancelCloseSecondary.bind(this);
    this.toggleCollection = this.toggleCollection.bind(this);
    this.openCollection = this.openCollection.bind(this);
    this.closeCollection = this.closeCollection.bind(this);

    if (this.DOM.burgerMenu && this.DOM.headerSecondaryMobile) {
      this.DOM.burgerMenu.addEventListener('click', this.toggleSecondaryMobile);
    }

    if (this.DOM.shopLink && this.DOM.headerSecondary) {
      this.DOM.shopLink.addEventListener('mouseenter', () => {
        this.cancelCloseSecondary();
        this.openSecondary();
      });
      this.DOM.shopLink.addEventListener('mouseleave', this.scheduleCloseSecondary);

      this.DOM.headerSecondary.addEventListener('mouseenter', () => {
        this.cancelCloseSecondary();
        this.openSecondary();
      });
      this.DOM.headerSecondary.addEventListener('mouseleave', this.scheduleCloseSecondary);
    }

    if (this.DOM.openCollectionButton) {
      this.DOM.openCollectionButton.addEventListener('click', this.toggleCollection);
    }

    this.updateCartButtonState = this.updateCartButtonState.bind(this);
    this.updateCartButtonState();
    if (typeof window.jQuery !== 'undefined') {
      window
        .jQuery(document.body)
        .on('updated_cart_totals updated_checkout', this.updateCartButtonState);
    }
  }

  /**
   * Met à jour la classe has-items du bouton panier après mise à jour des fragments WooCommerce
   */
  updateCartButtonState() {
    if (!this.DOM.cartButton) return;
    const countEl = this.DOM.cartButton.querySelector('.cart-count');
    const count = countEl ? parseInt(countEl.textContent || '0', 10) : 0;
    this.DOM.cartButton.classList.toggle('has-items', count > 0);
  }

  openSecondary() {
    this.cancelCloseSecondary();
    this.DOM.headerSecondary.classList.add('is-open');
    this.DOM.headerPrimary.classList.add('is-secondary-open');
  }

  closeSecondary() {
    this.DOM.headerSecondary.classList.remove('is-open');
    if (this.secondaryClassRemoveTimeout) clearTimeout(this.secondaryClassRemoveTimeout);
    this.secondaryClassRemoveTimeout = setTimeout(() => {
      this.DOM.headerPrimary.classList.remove('is-secondary-open');
      this.secondaryClassRemoveTimeout = null;
    }, 550); // 550ms is the transition duration of the secondary menu
  }

  openSecondaryMobile() {
    if (this.DOM.headerSecondaryMobile) {
      this.DOM.headerSecondaryMobile.classList.add('is-open');
      this.DOM.headerPrimary.classList.add('is-secondary-open-mobile');
    }
  }

  closeSecondaryMobile() {
    this.closeCollection();
    if (this.DOM.headerSecondaryMobile) {
      this.DOM.headerSecondaryMobile.classList.remove('is-open');
      this.DOM.headerPrimary.classList.remove('is-secondary-open-mobile');
    }
  }

  toggleSecondaryMobile(event) {
    event?.preventDefault();
    if (this.DOM.headerSecondaryMobile?.classList.contains('is-open')) {
      this.closeSecondaryMobile();
    } else {
      this.openSecondaryMobile();
    }
  }

  scheduleCloseSecondary() {
    this.cancelCloseSecondary();
    this.secondaryCloseTimeout = setTimeout(() => {
      this.closeSecondary();
      this.secondaryCloseTimeout = null;
    }, this.secondaryCloseDelay);
  }

  cancelCloseSecondary() {
    if (this.secondaryCloseTimeout) {
      clearTimeout(this.secondaryCloseTimeout);
      this.secondaryCloseTimeout = null;
    }
    if (this.secondaryClassRemoveTimeout) {
      clearTimeout(this.secondaryClassRemoveTimeout);
      this.secondaryClassRemoveTimeout = null;
    }
  }

  toggleSecondary(event) {
    event?.preventDefault();
    if (this.DOM.headerSecondary?.classList.contains('is-open')) {
      this.closeSecondary();
    } else {
      this.openSecondary();
    }
  }

  toggleCollection() {
    if (this.DOM.collection?.classList.contains('is-open')) {
      this.closeCollection();
    } else {
      this.openCollection();
    }
  }

  openCollection() {
    this.DOM.collection.classList.add('is-open');
    this.DOM.openCollectionButton.classList.add('is-collection-open');
  }

  closeCollection() {
    this.DOM.collection.classList.remove('is-open');
    this.DOM.openCollectionButton.classList.remove('is-collection-open');
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
