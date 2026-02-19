# WP-Content - Code personnalisé

Ce dossier contient **uniquement le code personnalisé** du projet.

## 📂 Structure versionnée

```
wp-content/
├── themes/
│   └── starter-theme/       ✅ Thème custom (Vite + Timber)
│
├── mu-plugins/              ✅ Must-Use Plugins
│   └── cours_cms/           # Logique métier (CPT, Taxonomies, Config)
│
├── plugins/                 ❌ Ignoré (plugins tiers WordPress)
├── uploads/                 ❌ Ignoré (médias utilisateurs)
└── languages/               ❌ Ignoré (traductions auto)
```

## 🎯 Philosophie

### Thème = Présentation

Le dossier `themes/starter-theme/` contient :

- Templates Twig (`views/`)
- Assets frontend (SCSS, JS)
- Configuration Vite
- Chargement des assets

### MU-Plugin = Logique

Le dossier `mu-plugins/cours_cms/` contient :

- Custom Post Types
- Taxonomies
- Configuration WordPress
- Menus et navigation
- Hooks et filters métier
- Intégrations ACF, WooCommerce, etc.

## ⚙️ Avantages de cette séparation

✅ **Maintenabilité** - Code organisé et modulaire  
✅ **Indépendance** - Changement de thème sans perte de données  
✅ **Clarté** - Responsabilités bien définies  
✅ **Évolutivité** - Ajout de fonctionnalités facilité

## 🚀 Installation

Voir le [README principal](../README.md) pour les instructions complètes.

## 📚 Documentation

- [Thème Starter](themes/starter-theme/README.md) - Documentation du thème
- [README principal](../README.md) - Configuration WordPress complète
