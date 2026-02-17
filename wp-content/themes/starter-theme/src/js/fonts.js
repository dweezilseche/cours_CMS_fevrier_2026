// Import font URLs from public directory using ?url query
import FranklinGothicATFMediumWoff2 from '/fonts/FranklinGothicATF-Medium.woff2?url';
import FranklinGothicATFMediumWoff from '/fonts/FranklinGothicATF-Medium.woff?url';
import FranklinGothicATFRegularWoff2 from '/fonts/FranklinGothicATF-Regular.woff2?url';
import FranklinGothicATFRegularWoff from '/fonts/FranklinGothicATF-Regular.woff?url';

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
  font-family: 'Franklin Gothic ATF';
  font-weight: 400;
  font-style: normal;
  font-display: swap;
  src: url('${getUrl(FranklinGothicATFRegularWoff2)}') format('woff2'),
       url('${getUrl(FranklinGothicATFRegularWoff)}') format('woff');
}

@font-face {
  font-family: 'Franklin Gothic ATF';
  font-weight: 500;
  font-style: normal;
  font-display: swap;
  src: url('${getUrl(FranklinGothicATFMediumWoff2)}') format('woff2'),
       url('${getUrl(FranklinGothicATFMediumWoff)}') format('woff');
}

`;

// Inject font faces into the document
const style = document.createElement('style');
style.textContent = fontFaces;
document.head.insertBefore(style, document.head.firstChild);
