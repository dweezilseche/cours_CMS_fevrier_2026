# Fonts Directory

## Placement des fichiers

Placez vos fichiers de fonts ici dans les formats suivants :

- `.woff2` (prioritaire, meilleure compression)
- `.woff` (fallback pour anciens navigateurs)

## Fonts actuellement configurées

Les fonts suivantes sont configurées dans `src/js/fonts.js` :

### OpenSans

- `OpenSans-Regular.woff2` / `OpenSans-Regular.woff` (weight: 400)
- `OpenSans-Bold.woff2` / `OpenSans-Bold.woff` (weight: 700)

### Thunder

- `Thunder-BoldLC.woff2` / `Thunder-BoldLC.woff` (weight: 700)

## Comment ajouter une nouvelle font

1. **Placer les fichiers** dans ce dossier (`static/fonts/`)

   ```
   static/fonts/
   ├── MaNouvelleFonte.woff2
   └── MaNouvelleFonte.woff
   ```

2. **Importer dans `src/js/fonts.js`** :

   ```javascript
   import MaFonteWoff2 from '/fonts/MaNouvelleFonte.woff2?url';
   import MaFonteWoff from '/fonts/MaNouvelleFonte.woff?url';
   ```

3. **Ajouter la règle @font-face** dans `fontFaces` :

   ```javascript
   @font-face {
     font-family: 'MaNouvelleFonte';
     font-weight: 400;
     font-style: normal;
     font-display: swap;
     src: url('${getUrl(MaFonteWoff2)}') format('woff2'),
          url('${getUrl(MaFonteWoff)}') format('woff');
   }
   ```

4. **Utiliser dans le SCSS** :
   ```scss
   body {
     font-family: 'MaNouvelleFonte', sans-serif;
   }
   ```

## Documentation complète

Voir `FONTS-SETUP.md` à la racine du projet pour plus de détails sur le fonctionnement du système de chargement des fonts avec Vite.
