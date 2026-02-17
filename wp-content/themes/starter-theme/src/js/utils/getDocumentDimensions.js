/**
 * Update full dimensions (100vh/100vw) with CSS variables
 */

export const getDocumentDimensions = () => {
  const windowWidth = document.documentElement.clientWidth * 0.01
  const windowHeight = document.documentElement.clientHeight * 0.01

  document.documentElement.style.setProperty('--vh', `${windowHeight}px`)
  document.documentElement.style.setProperty('--vw', `${windowWidth}px`)
}
