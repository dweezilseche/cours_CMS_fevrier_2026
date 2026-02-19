# Starter Theme WordPress

> Thème WordPress moderne avec Vite, Timber et une stack JavaScript performante

## 🎯 Présentation

Starter Theme est un thème WordPress moderne conçu pour le développement rapide de sites web performants. Il utilise une architecture séparée entre le thème (présentation) et le MU-Plugin (logique métier), garantissant une meilleure maintenabilité du code.

## ✨ Fonctionnalités

- 🚀 **Vite** - Build tool ultra-rapide avec Hot Module Replacement (HMR)
- 🌲 **Timber** - Templating avec Twig pour une séparation propre PHP/HTML
- 🎨 **SCSS** - Préprocesseur CSS avec architecture modulaire
- ⚡ **JavaScript moderne** - Support ES6+ avec modules
- 📱 **Responsive** - Mobile-first avec Include Media
- 🎭 **Animations** - GSAP pour des animations fluides
- 🔄 **Transitions** - Taxi.js pour les transitions entre pages
- 📦 **Composants UI** - Swiper, Lenis, Plyr intégrés
- 🔧 **ACF Ready** - Synchronisation JSON des champs ACF
- 🎯 **WooCommerce Ready** - Compatible e-commerce

## 📋 Prérequis

- **PHP** >= 8.0
- **WordPress** >= 6.0
- **Node.js** >= 18.x
- **Composer** >= 2.x
- **npm** >= 9.x

## 🚀 Installation

### 1. Cloner le projet

```bash
cd wp-content/themes/
git clone [votre-repo] starter-theme
cd starter-theme
```

### 2. Installer les dépendances PHP

```bash
composer install
```

### 3. Installer les dépendances JavaScript

```bash
npm install
```

### 4. Configuration de l'environnement

Copier le fichier d'exemple de configuration :

```bash
cp .env.example .env
```

### 5. Activer le thème

Depuis l'administration WordPress :
**Apparence → Thèmes → Activer "Starter Theme"**

## 💻 Développement

### Démarrer le serveur de développement

```bash
npm run dev
```

Le serveur Vite démarre sur `http://localhost:5173` avec :

- ✅ Hot Module Replacement (HMR)
- ✅ Rechargement automatique des fichiers PHP et Twig
- ✅ Compilation SCSS à la volée

### Nettoyer le cache et redémarrer

En cas de problème de cache :

```bash
npm run dev:clean
```

### Build de production

```bash
npm run build
```

Les assets compilés et optimisés sont générés dans le dossier `dist/`

### Build avec surveillance

```bash
npm run watch
```

Surveille les modifications et rebuild automatiquement.

## 📁 Structure du projet

```
starter-theme/
├── 📄 functions.php          # Point d'entrée du thème
├── 📄 style.css              # Informations du thème WordPress
├── 📄 vite.config.js         # Configuration Vite
├── 📄 composer.json          # Dépendances PHP
├── 📄 package.json           # Dépendances JavaScript
│
├── 📂 src/                   # Sources
│   ├── 📂 js/                # JavaScript
│   │   ├── main.js           # Point d'entrée JS
│   │   ├── modules/          # Modules ES6
│   │   └── vendors/          # Librairies tierces
│   └── 📂 scss/              # SCSS
│       ├── main.scss         # Point d'entrée SCSS
│       ├── abstracts/        # Variables, mixins, functions
│       ├── base/             # Reset, typography, base styles
│       ├── components/       # Composants réutilisables
│       ├── layouts/          # Grilles, header, footer
│       └── pages/            # Styles spécifiques aux pages
│
├── 📂 views/                 # Templates Twig (Timber)
│   ├── 📂 layouts/           # Layouts de base
│   ├── 📂 pages/             # Templates de pages
│   ├── 📂 partials/          # Partials réutilisables
│   └── 📂 components/        # Composants Twig
│
├── 📂 static/                # Assets statiques
│   ├── 📂 fonts/             # Webfonts
│   ├── 📂 images/            # Images
│   └── 📂 videos/            # Vidéos
│
├── 📂 dist/                  # Build de production (généré)
├── 📂 acf-json/              # Synchronisation ACF
├── 📂 vendor/                # Dépendances Composer
└── 📂 node_modules/          # Dépendances npm
```

## 🛠 Technologies

### Backend

- **WordPress** - CMS
- **Timber/Twig** - Moteur de templates
- **PHP 8.0+** - Langage serveur
- **Composer** - Gestionnaire de dépendances PHP

### Frontend

- **Vite** - Build tool moderne
- **SCSS** - Préprocesseur CSS
- **JavaScript ES6+** - Modules natifs

### Librairies JavaScript

- **GSAP** - Animations performantes
- **Taxi.js** - Transitions entre pages (alternative à BarbaJS)
- **Swiper** - Carrousels/sliders
- **Lenis** - Smooth scroll
- **Plyr** - Lecteur vidéo/audio
- **FontFaceObserver** - Chargement optimisé des fonts

## 🔧 Configuration

### Vite

Le fichier `vite.config.js` est configuré pour :

- Servir le thème sur `localhost:5173`
- Recharger automatiquement les fichiers PHP et Twig
- Utiliser SCSS avec l'API moderne
- Copier les assets statiques dans `dist/`

### Timber

Les templates Twig sont dans le dossier `views/`. La configuration Timber est gérée par le MU-Plugin.

### ACF

Les configurations ACF sont synchronisées dans `acf-json/`. En développement :

- Les modifications dans l'admin WP génèrent des fichiers JSON
- Les fichiers JSON sont versionnés sur Git
- Les autres environnements importent automatiquement les configurations

## 🏗 Architecture

Ce thème suit une architecture séparée :

### Thème (Présentation)

- Chargement des assets (Vite)
- Templates Twig
- Styles et JavaScript

### MU-Plugin (Logique)

- Custom Post Types
- Taxonomies
- Configuration WordPress
- Menus et fonctionnalités
- Intégrations tierces

Cette séparation garantit :

- ✅ Code maintenable et modulaire
- ✅ Changement de thème sans perdre les données
- ✅ Logique métier indépendante de la présentation

## 📝 Scripts disponibles

| Commande              | Description                               |
| --------------------- | ----------------------------------------- |
| `npm run dev`         | Lance le serveur de développement Vite    |
| `npm run dev:clean`   | Nettoie le cache et lance le serveur      |
| `npm run build`       | Build de production                       |
| `npm run build:clean` | Nettoie et build de production            |
| `npm run watch`       | Build avec surveillance des modifications |
| `npm run preview`     | Prévisualisation du build de production   |
| `npm run clean`       | Nettoie dist/ et le cache                 |
| `npm run clean:cache` | Nettoie uniquement le cache Vite          |

## 🚀 Déploiement

### Préparer pour la production

1. **Build des assets**

   ```bash
   npm run build
   ```

2. **Vérifier les fichiers**
   - ✅ Le dossier `dist/` contient les assets compilés
   - ✅ Le fichier `dist/manifest.json` est présent

3. **Déployer**
   - Transférer le thème complet (avec `dist/`)
   - **NE PAS** transférer `node_modules/` ni `src/`
   - Fichiers essentiels : `dist/`, `views/`, `vendor/`, `*.php`

### Fichiers à exclure du déploiement

Configurés dans `.gitignore` :

- `node_modules/`
- `dist/` (sauf si pré-build)
- `.env`
- `.DS_Store`
- Fichiers de cache

## 🐛 Dépannage

### Le HMR ne fonctionne pas

```bash
npm run dev:clean
```

### Erreurs de cache Vite

```bash
npm run clean:cache
npm run dev
```

### Problèmes de dépendances

```bash
rm -rf node_modules package-lock.json
npm install
```

### SCSS non compilé

Vérifier que le serveur Vite est démarré et que le fichier `manifest.json` existe dans `dist/` en production.

## 📚 Documentation

- [WordPress Codex](https://codex.wordpress.org/)
- [Timber Documentation](https://timber.github.io/timber/)
- [Vite Documentation](https://vitejs.dev/)
- [GSAP Documentation](https://greensock.com/docs/)
- [Taxi.js Documentation](https://taxi.js.org/)

## 👤 Auteur

**Dweezil Sèche**

- Site web : [dweezilseche.fr](https://dweezilseche.fr)

## 📄 Licence

GNU General Public License v2 or later - [GPL-2.0+](http://www.gnu.org/licenses/gpl-2.0.html)

## 🤝 Contribution

Ce thème est un projet éducatif. Les contributions sont les bienvenues :

1. Fork le projet
2. Créer une branche (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

---

**Made with ❤️ for WordPress development**
