# Documentation complète — Thème Starter

Toute la documentation du thème est regroupée dans ce fichier.

---

## Table des matières

1. [Vue d'ensemble](#1-vue-densemble)
2. [Installation](#2-installation)
3. [Structure du projet](#3-structure-du-projet)
4. [Développement et commandes](#4-développement-et-commandes)
5. [Configuration (Vite, functions.php)](#5-configuration-vite-functionsphp)
6. [Composants Twig](#6-composants-twig)
7. [Templates et pages](#7-templates-et-pages)
8. [JavaScript (app.js, modules)](#8-javascript-appjs-modules)
9. [SCSS et styles](#9-scss-et-styles)
10. [Blocs ACF](#10-blocs-acf)
11. [WooCommerce (thème)](#11-woocommerce-thème)
12. [Personnalisation](#12-personnalisation)
13. [Build et déploiement](#13-build-et-déploiement)
14. [Checklist et dépannage](#14-checklist-et-dépannage)

---

## 1. Vue d'ensemble

Le thème **starter-theme** est la couche **présentation** du boilerplate WordPress + WooCommerce. Il ne contient que :

- **Assets** : chargement via Vite (dev ou prod), point d’entrée JS/SCSS
- **Templates Twig** : base, layouts, components, pages, woocommerce
- **Templates PHP** : front-page.php, page.php, single.php, etc. qui préparent le contexte et appellent `Timber::render()`

La **logique métier** (CPT, menus, ACF, WooCommerce, etc.) est dans le **MU-Plugin**.

### Technologies

- **Timber/Twig** — Templates
- **Vite** — Build, HMR
- **Sass/SCSS** — Styles (BEM, ITCSS)
- **JavaScript ES6+** — GSAP, Swiper, Lenis, Taxi.js (PJAX), Plyr, Tarteaucitron (scripts dans `src/js/tarteaucitron/`, chargés hors npm ; adapter si besoin)

### Prérequis

- PHP 8.0+, Node.js 18+, Composer
- WordPress 6.0+, MU-Plugin du projet installé
- ACF Pro (si blocs ACF), WooCommerce (si boutique)

---

## 2. Installation

```bash
# Dans le dossier du thème
cd wp-content/themes/starter-theme

# Dépendances PHP (Timber via thème si présent)
composer install

# Dépendances Node
npm install
```

Puis dans WordPress : **Apparence > Thèmes** → Activer **Starter Theme**.  
Vérifier que le MU-Plugin a bien fait `composer install` dans `wp-content/mu-plugins`.

---

## 3. Structure du projet

```
starter-theme/
├── src/
│   ├── js/
│   │   ├── app.js              # Point d'entrée JS
│   │   ├── animations/         # GSAP
│   │   ├── classes/            # Slider, etc.
│   │   ├── layouts/            # header.js
│   │   ├── partials/           # Sections
│   │   ├── pjax/               # Taxi.js
│   │   ├── tarteaucitron/      # Cookies/RGPD
│   │   └── utils/              # smooth-scroll, etc.
│   └── scss/
│       ├── app.scss            # Point d'entrée SCSS
│       ├── base/               # variables, mixins, typography, reset
│       ├── blocks/             # Styles blocs ACF
│       ├── components/         # button, card, form, link
│       ├── layouts/            # header, footer, navigation, grid
│       ├── pages/              # Styles par page
│       ├── partials/           # Sections
│       └── utils/              # spacing, display, text, colors
│
├── views/
│   ├── layouts/                # base.twig, header.twig, footer.twig
│   ├── components/             # button, card, etc.
│   ├── pages/                  # front-page, page, single, archive-*, 404
│   └── woocommerce/            # archive-product, single-product, checkout, etc.
│
├── dist/                       # Généré par Vite (build)
├── static/                     # Images, fonts
├── acf-json/                   # Export JSON ACF
├── functions.php               # Chargement assets uniquement
├── woocommerce.php             # Routeur pages WooCommerce
├── front-page.php, page.php, single.php, archive-*.php, 404.php
├── vite.config.js
├── package.json
└── style.css
```

**Règle** : pas de `register_post_type`, `register_nav_menus`, `Timber::init`, controllers AJAX ou config ACF dans le thème — tout cela est dans le MU-Plugin.

---

## 4. Développement et commandes

### Commandes npm

```bash
npm run dev      # Serveur Vite + HMR (http://localhost:5173)
npm run watch    # Watch (recompilation auto)
npm run build    # Build production (équivalent prod)
npm run prod     # Build production si défini
npm run preview  # Prévisualiser le build
```

### Commandes utiles

```bash
# Réinstaller dépendances
rm -rf node_modules package-lock.json && npm install

# Composer (dans le thème si vendor présent)
composer install
```

### Workflow

1. Lancer `npm run dev` (ou `npm run watch`).
2. Modifier SCSS dans `src/scss/`, JS dans `src/js/`, Twig dans `views/`.
3. Le navigateur se recharge automatiquement (HMR ou live reload).

---

## 5. Configuration (Vite, functions.php)

### Vite (`vite.config.js`)

- Entrées : `src/js/app.js`, `src/scss/app.scss`
- Sortie : `dist/`
- HMR sur `localhost:5173`
- Live reload sur `*.php` et `views/**/*.twig` si configuré

### functions.php

- Détecte si Vite dev server tourne → charge les assets depuis `http://localhost:5173`.
- Sinon charge depuis `dist/` via le manifest.
- Peut ajouter `type="module"` aux scripts.
- **Ne contient pas** : menus, CPT, Timber::init, ACF, logique métier.

---

## 6. Composants Twig

### Emplacement

`views/components/` — ex. `button.twig`, `card.twig`, `link.twig`.

### Utilisation

```twig
{% include 'components/button.twig' with {
  text: 'Mon bouton',
  url: '/page',
  variant: 'primary',
  size: 'md'
} %}

{% include 'components/card.twig' with {
  image: post.thumbnail,
  title: post.title,
  excerpt: post.excerpt,
  link: post.link
} %}
```

### Créer un composant

1. Créer `views/components/mon-composant.twig` (structure HTML, BEM).
2. Créer `src/scss/components/_mon-composant.scss` et l’importer dans `app.scss`.
3. Utiliser avec `{% include 'components/mon-composant.twig' with { ... } %}`.

---

## 7. Templates et pages

### Base

- `views/layouts/base.twig` : structure HTML, `wp_head`, header, `{% block content %}`, footer, `wp_footer`.

### Pages

- **Front** : `front-page.php` → `views/pages/front-page.twig`
- **Page** : `page.php` → `views/pages/page.twig`
- **Single** : `single.php` → `views/pages/single.twig`
- **Archive** : `archive-app_event.php` → `views/pages/archive-event.twig`
- **404** : `404.php` → `views/pages/404.twig`

Chaque template PHP : `$context = Timber::context()`, enrichissement, `Timber::render('pages/xxx.twig', $context)`.

### Créer une page custom

1. Créer `page-ma-page.php` avec `Timber::render('pages/ma-page.twig', $context)`.
2. Créer `views/pages/ma-page.twig` qui étend `layouts/base.twig` et remplit `{% block content %}`.
3. Dans WordPress, attribuer le template « Ma Page » à la page concernée.

---

## 8. JavaScript (app.js, modules)

### Point d’entrée

`src/js/app.js` : imports (GSAP, Lenis, Header, Animations, Slider, etc.), initialisation au `DOMContentLoaded`.

### Modules principaux

- **layouts/header.js** : header sticky, menu mobile.
- **utils/smooth-scroll.js** : Lenis.
- **animations/** : animations GSAP (fade-in, slide-in, scale-in, counter, etc.).
- **classes/Slider.js** : Swiper (init, presets).

### Classes CSS d’animation (GSAP)

- `.fade-in`, `.slide-in-left`, `.slide-in-right`, `.scale-in`
- `data-speed` pour parallaxe
- `.counter` avec `data-target`, `data-duration` pour compteur

---

## 9. SCSS et styles

### Organisation (ITCSS + BEM)

- **base/** : variables, mixins, reset, typography
- **layouts/** : header, footer, navigation, grid
- **components/** : button, card, form, link
- **blocks/** : styles des blocs ACF
- **pages/** : styles par page (dont _woocommerce.scss)
- **utils/** : spacing, display, text, colors

### Variables

Dans `src/scss/base/_variables.scss` : couleurs, espacements, polices, breakpoints. Adapter pour la charte du projet.

### Import

Tout est importé depuis `src/scss/app.scss`.

---

## 10. Blocs ACF

L’**enregistrement** des blocs est dans le **MU-Plugin** (`Acf/Blocks.php`, `templates/blocks/*.php`).

Dans le **thème** :

- **Templates Twig** : `views/blocks/nom-bloc.twig`
- **Styles** : `src/scss/blocks/_nom-bloc.scss` (import dans app.scss)

Le template PHP du bloc (mu-plugin) fait `Timber::render('blocks/nom-bloc.twig', $context)` ; le thème fournit le Twig et le SCSS.

---

## 11. WooCommerce (thème)

### Fichiers du thème

- **woocommerce.php** : routeur (is_shop → archive-product.twig, is_product → single-product.twig, is_cart → cart.twig, is_checkout → checkout.twig).
- **views/woocommerce/** : `woocommerce.twig`, `archive-product.twig`, `single-product.twig`, `cart.twig`, `checkout.twig`.
- **Styles** : `src/scss/pages/_woocommerce.scss` (à importer dans app.scss).

### Logique WooCommerce

Toute la config (support thème, colonnes, produits par page, fragments panier, champs checkout, méthodes utilitaires) est dans **mu-plugins/src/App/WooCommerce.php**.

### Compteur panier dans le header

Dans `views/layouts/header.twig` (ou partial header) :

```twig
<a href="{{ function('wc_get_cart_url') }}" class="cart-link">
  <span class="cart-count" data-cart-count>{{ function('WC')->cart.get_cart_contents_count() }}</span>
  <span class="cart-total">{{ function('WC')->cart.get_cart_total() }}</span>
</a>
```

Le fragment AJAX dans `WooCommerce::cartCountFragment()` met à jour `.cart-count` après ajout au panier.

### Personnalisation styles WooCommerce

Modifier les variables et classes dans `src/scss/pages/_woocommerce.scss` (grille, cartes produit, breadcrumb, checkout, messages).

---

## 12. Personnalisation

### Couleurs et polices

- **Couleurs** : `src/scss/base/_variables.scss` (primary, secondary, etc.).
- **Typographie** : `src/scss/base/_typography.scss` (font-family, tailles).

### Header / Footer

- **Header** : `views/layouts/header.twig` (ou partials/header.twig selon la structure).
- **Footer** : `views/layouts/footer.twig`.  
Les données (logo, menus, CTA) viennent du MU-Plugin (Header.php, Footer.php, Configuration).

### Nom du thème

Modifier `style.css` (Theme Name, Description, etc.). Si le dossier du thème est renommé, vérifier les chemins dans `WooCommerce::locateTemplate` si besoin.

---

## 13. Build et déploiement

### Build production

```bash
npm run build
# ou
npm run prod
```

Vérifier que `dist/` contient CSS, JS et éventuellement images. En production, le thème charge les assets depuis `dist/` (pas depuis le serveur Vite).

### Déploiement

- Versionner : le thème sans `node_modules/`, sans `dist/`, sans `vendor/` (selon .gitignore).
- Sur le serveur : `npm install` puis `npm run build` (ou `npm run prod`) dans le thème ; `composer install` si le thème a un vendor.

---

## 14. Checklist et dépannage

### Checklist installation

- [ ] `composer install` (mu-plugins + thème si besoin)
- [ ] `npm install` dans le thème
- [ ] Thème activé dans WordPress
- [ ] ACF Pro activé (si blocs ACF)
- [ ] WooCommerce activé (si boutique)
- [ ] Permaliens enregistrés
- [ ] `npm run dev` lance Vite sur http://localhost:5173

### Problèmes fréquents

- **Vite ne démarre pas** : `rm -rf node_modules package-lock.json && npm install` puis `npm run dev`.
- **Styles / JS ne chargent pas** : vérifier que Vite tourne en dev ou que `dist/` existe et que le manifest est lu en prod ; vérifier la console navigateur.
- **Timber / Class not found** : `composer install` dans `wp-content/mu-plugins` (et thème si Timber est dans le thème).
- **Blocs ACF invisibles** : ACF Pro installé et activé ; blocs enregistrés dans le MU-Plugin.
- **Page blanche** : activer `WP_DEBUG` dans wp-config.php et consulter les erreurs PHP.

---

**Documentation unifiée du thème starter-theme.**  
La doc globale du projet (installation, repo GitHub, MU-Plugin, WooCommerce détaillé) est dans **DOCUMENTATION.md** à la racine du projet.
