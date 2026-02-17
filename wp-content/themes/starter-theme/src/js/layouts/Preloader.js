const mode = import.meta.env.MODE;
import FontFaceObserver from 'fontfaceobserver';

export default class Preloader {
  constructor() {
    this.listeners = {};

    this.fonts = {
      'Franklin Gothic ATF': [
        { weight: 400, style: 'normal' },
        { weight: 500, style: 'normal' },
      ],
    };

    this.loadedFonts = 0;
    this.totalFonts = this.calculateTotalFonts();

    this.init();
  }

  calculateTotalFonts() {
    return Object.keys(this.fonts).reduce((total, fontFamily) => {
      return total + this.fonts[fontFamily].length;
    }, 0);
  }

  init() {
    this.loadFonts();
  }

  loadFonts() {
    Object.entries(this.fonts).forEach(([family, variants]) => {
      variants.forEach(({ weight, style }) => {
        const font = new FontFaceObserver(family, {
          weight,
          style,
        });

        font
          .load(null, 5000)
          .then(() => {
            // if (mode === 'development') {
            //   console.log(`Font loaded: ${family} (weight: ${weight})`)
            // }

            this.onFontLoaded();
          })
          .catch(error => {
            if (mode === 'development') {
              console.warn(
                `Font ${family} (weight: ${weight}, style: ${style}) failed to load`,
                error
              );
            }

            this.onFontLoaded();
          });
      });
    });
  }

  onFontLoaded() {
    this.loadedFonts++;

    const progress = this.loadedFonts / this.totalFonts;

    this.emit('progress', progress);

    if (this.loadedFonts === this.totalFonts) {
      this.onComplete();
    }
  }

  onComplete() {
    this.emit('complete');
  }

  // Event emitter methods (browser-compatible)
  on(event, callback) {
    if (!this.listeners[event]) {
      this.listeners[event] = [];
    }
    this.listeners[event].push(callback);
  }

  emit(event, ...args) {
    if (this.listeners[event]) {
      this.listeners[event].forEach(callback => callback(...args));
    }
  }
}
