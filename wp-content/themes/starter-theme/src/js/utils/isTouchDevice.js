/**
 * Detect touch devices - with Safari desktop exception
 */
export const isTouchDevice = () => {
  // Méthode 1: Vérification de l'existence des événements tactiles
  const hasTouchEvents = 'ontouchstart' in window || 
                          navigator.maxTouchPoints > 0 ||
                          navigator.msMaxTouchPoints > 0;

  // Méthode 2: Vérification de l'API matchMedia pour les appareils qui préfèrent l'interaction tactile
  const prefersTouch = window.matchMedia && 
                        window.matchMedia('(pointer: coarse)').matches;

  // Méthode 3: Détection des appareils mobiles par l'agent utilisateur (moins fiable mais utile en complément)
  const userAgent = navigator.userAgent.toLowerCase();
  const isMobileDevice = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i.test(userAgent);

  // Retourne vrai si au moins deux des méthodes détectent un appareil tactile
  // Cette approche hybride améliore la fiabilité
  const detectionPoints = [hasTouchEvents, prefersTouch, isMobileDevice];
  const detectionScore = detectionPoints.filter(Boolean).length;

  return detectionScore >= 2;
}
