// Import font URLs from public directory using ?url query
import ErodeRegularWoff2 from '/fonts/Erode-Regular.woff2?url';
import ErodeRegularWoff from '/fonts/Erode-Regular.woff?url';
import ErodeItalicWoff2 from '/fonts/Erode-Italic.woff2?url';
import ErodeItalicWoff from '/fonts/Erode-Italic.woff?url';
import ErodeMediumWoff2 from '/fonts/Erode-Medium.woff2?url';
import ErodeMediumWoff from '/fonts/Erode-Medium.woff?url';
import ErodeMediumItalicWoff2 from '/fonts/Erode-MediumItalic.woff2?url';
import ErodeMediumItalicWoff from '/fonts/Erode-MediumItalic.woff?url';
import ManropeRegularWoff2 from '/fonts/Manrope-Regular.woff2?url';
import ManropeRegularWoff from '/fonts/Manrope-Regular.woff?url';
import ManropeBoldWoff2 from '/fonts/Manrope-Bold.woff2?url';
import ManropeBoldWoff from '/fonts/Manrope-Bold.woff?url';

// In dev mode, Vite returns relative URLs - prefix with Vite server URL
const isDev = import.meta.env.DEV;
const viteUrl = 'http://localhost:5173';

const getUrl = url => {
  // If in dev mode and URL is relative, prepend Vite server URL
  return isDev && url.startsWith('/') ? viteUrl + url : url;
};

// Create @font-face rules with imported URLs
const fontFaces = `
@font-face {
  font-family: 'Erode';
  font-weight: 400;
  font-style: normal;
  font-display: swap;
  src: url('${getUrl(ErodeRegularWoff2)}') format('woff2'),
       url('${getUrl(ErodeRegularWoff)}') format('woff');
}

@font-face {
  font-family: 'Erode';
  font-weight: 400;
  font-style: italic;
  font-display: swap;
  src: url('${getUrl(ErodeItalicWoff2)}') format('woff2'),
       url('${getUrl(ErodeItalicWoff)}') format('woff');
}

@font-face {
  font-family: 'Erode';
  font-weight: 500;
  font-style: normal;
  font-display: swap;
  src: url('${getUrl(ErodeMediumWoff2)}') format('woff2'),
       url('${getUrl(ErodeMediumWoff)}') format('woff');
}

@font-face {
  font-family: 'Erode';
  font-weight: 500;
  font-style: italic;
  font-display: swap;
  src: url('${getUrl(ErodeMediumItalicWoff2)}') format('woff2'),
       url('${getUrl(ErodeMediumItalicWoff)}') format('woff');
}

@font-face {
  font-family: 'Manrope';
  font-weight: 400;
  font-style: normal;
  font-display: swap;
  src: url('${getUrl(ManropeRegularWoff2)}') format('woff2'),
       url('${getUrl(ManropeRegularWoff)}') format('woff');
}

@font-face {
  font-family: 'Manrope';
  font-weight: 700;
  font-style: normal;
  font-display: swap;
  src: url('${getUrl(ManropeBoldWoff2)}') format('woff2'),
       url('${getUrl(ManropeBoldWoff)}') format('woff');
}
`;

// Inject font faces into the document
const style = document.createElement('style');
style.textContent = fontFaces;
document.head.insertBefore(style, document.head.firstChild);
