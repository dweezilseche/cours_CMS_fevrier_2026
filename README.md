# Cours CMS - Projet WordPress

> Projet WordPress avec thème custom et architecture moderne

## 📋 Vue d'ensemble

Ce projet WordPress est configuré pour versionner **uniquement le code personnalisé** (thème, mu-plugins) et exclure le core WordPress et les plugins tiers.

## 🎯 Stratégie Git

### ✅ Ce qui est versionné

- **`wp-content/themes/starter-theme/`** - Notre thème custom
- **`wp-content/mu-plugins/cours_cms/`** - Notre plugin Must-Use (logique métier)
- **Fichiers de configuration exemple** (`.env.example`)

### ❌ Ce qui est ignoré

- **Core WordPress** (`wp-admin/`, `wp-includes/`, fichiers racine)
- **Configuration sensible** (`wp-config.php`, `.env`)
- **Plugins tiers** (`wp-content/plugins/`)
- **Uploads** (`wp-content/uploads/`)
- **Dépendances** (`node_modules/`, `vendor/` optionnel)
- **Fichiers générés** (`dist/`, builds)

## 🚀 Installation

### 1. Prérequis

- **PHP** >= 8.0
- **Node.js** >= 18.x
- **Composer** >= 2.x
- **WordPress** >= 6.0
- **Serveur local** (MAMP, Local by Flywheel, XAMPP, etc.)

### 2. Cloner le projet

```bash
git clone [votre-repo] cours_CMS_fevrier_2026
cd cours_CMS_fevrier_2026
```

### 3. Télécharger WordPress

Télécharger WordPress depuis [wordpress.org](https://wordpress.org/download/) et extraire les fichiers dans le dossier racine :

```bash
# Les fichiers WordPress de base doivent être présents :
# wp-admin/, wp-includes/, wp-*.php, etc.
```

### 4. Configuration de WordPress

Créer le fichier `wp-config.php` :

```bash
cp wp-config-sample.php wp-config.php
```

Éditer `wp-config.php` avec vos informations de base de données :

```php
define('DB_NAME', 'votre_base');
define('DB_USER', 'votre_utilisateur');
define('DB_PASSWORD', 'votre_mot_de_passe');
define('DB_HOST', 'localhost');
```

### 5. Installer le thème

```bash
cd wp-content/themes/starter-theme

# Installer les dépendances PHP
composer install

# Installer les dépendances JavaScript
npm install

# Lancer le serveur de développement
npm run dev
```

### 6. Activer le thème

1. Accéder à l'administration WordPress : `http://localhost/cours_CMS_fevrier_2026/wp-admin`
2. Aller dans **Apparence → Thèmes**
3. Activer **"Starter Theme"**

## 🏗 Architecture du projet

```
cours_CMS_fevrier_2026/
│
├── 📁 wp-admin/              ❌ Non versionné (WordPress core)
├── 📁 wp-includes/           ❌ Non versionné (WordPress core)
├── 📄 wp-*.php               ❌ Non versionné (WordPress core)
│
├── 📁 wp-content/            ✅ RACINE DU CODE VERSIONNÉ
│   │
│   ├── 📁 themes/
│   │   └── 📁 starter-theme/ ✅ Notre thème custom
│   │       ├── functions.php
│   │       ├── style.css
│   │       ├── package.json
│   │       ├── composer.json
│   │       ├── vite.config.js
│   │       ├── 📁 src/       # Sources JS/SCSS
│   │       ├── 📁 views/     # Templates Twig
│   │       ├── 📁 static/    # Assets statiques
│   │       └── 📁 dist/      ❌ Non versionné (build)
│   │
│   ├── 📁 mu-plugins/        ✅ Plugins Must-Use custom
│   │   └── 📁 cours_cms/     # Logique métier (CPT, taxonomies, etc.)
│   │
│   ├── 📁 plugins/           ❌ Non versionné (plugins tiers)
│   ├── 📁 uploads/           ❌ Non versionné (médias)
│   └── 📁 languages/         ❌ Non versionné (traductions)
│
├── 📄 wp-config.php          ❌ Non versionné (config sensible)
├── 📄 .gitignore             ✅ Versionné
└── 📄 README.md              ✅ Versionné (ce fichier)
```

## 🔧 Configuration

### Variables d'environnement

Le thème peut utiliser un fichier `.env` pour les configurations spécifiques :

```bash
cd wp-content/themes/starter-theme
cp .env.example .env
```

### Base de données

**⚠️ Important** : La base de données n'est **jamais versionnée** sur Git.

Pour partager la BDD entre environnements :

1. Exporter via phpMyAdmin ou WP-CLI
2. Partager le fichier `.sql` via un moyen sécurisé (pas Git)
3. Importer sur l'environnement cible

```bash
# Export avec WP-CLI
wp db export backup.sql

# Import avec WP-CLI
wp db import backup.sql
```

## 💻 Développement

### Travailler sur le thème

```bash
cd wp-content/themes/starter-theme

# Mode développement (HMR activé)
npm run dev

# Build de production
npm run build
```

Le serveur Vite est accessible sur `http://localhost:5173`

### Structure du thème

- **Backend** : Timber/Twig + PHP
- **Frontend** : Vite + SCSS + JavaScript ES6+
- **Build** : Vite avec Hot Module Replacement

Voir [wp-content/themes/starter-theme/README.md](wp-content/themes/starter-theme/README.md) pour plus de détails.

## 📦 Déploiement

### Préparer pour la production

1. **Build des assets**

   ```bash
   cd wp-content/themes/starter-theme
   npm run build
   ```

2. **Ce qu'il faut déployer**
   - ✅ WordPress core (télécharger séparément)
   - ✅ `wp-content/themes/starter-theme/` (avec `dist/`)
   - ✅ `wp-content/mu-plugins/cours_cms/`
   - ✅ `wp-config.php` (créer sur le serveur)
   - ✅ Plugins WordPress nécessaires (installer via admin)

3. **Ce qu'il NE faut PAS déployer**
   - ❌ `node_modules/`
   - ❌ `src/` (sources, déjà compilées dans `dist/`)
   - ❌ `.env`, `.git/`
   - ❌ Fichiers de dev (`.vscode/`, `.idea/`)

### Workflow de déploiement recommandé

1. **Git clone** sur le serveur ou en local
2. **Télécharger WordPress** et l'installer
3. **Copier `wp-content/`** du repo vers WordPress
4. **Créer `wp-config.php`** avec les bons identifiants
5. **Installer les dépendances** (`composer install`)
6. **Build les assets** (`npm run build`)
7. **Configurer le serveur web** (Apache/Nginx)

## 🔐 Sécurité

### Fichiers sensibles NON versionnés

- `wp-config.php` - Identifiants de base de données
- `.env` - Variables d'environnement
- `wp-content/uploads/` - Médias utilisateurs
- Dumps SQL de la base de données

### Bonnes pratiques

- ✅ Ne jamais commiter `wp-config.php`
- ✅ Utiliser des clés de sécurité uniques (via [api.wordpress.org](https://api.wordpress.org/secret-key/1.1/salt/))
- ✅ Garder WordPress et les plugins à jour
- ✅ Utiliser des mots de passe forts
- ✅ Limiter les tentatives de connexion

## 🛠 Outils et technologies

### Backend

- **WordPress** - CMS
- **PHP 8.0+** - Langage serveur
- **Timber** - Moteur de templates Twig
- **Composer** - Gestionnaire de dépendances PHP

### Frontend

- **Vite** - Build tool moderne avec HMR
- **SCSS** - Préprocesseur CSS
- **JavaScript ES6+** - Modules natifs
- **GSAP** - Animations
- **Taxi.js** - Transitions entre pages
- **Swiper** - Carrousels

### Développement

- **Git** - Versionning
- **npm** - Gestionnaire de paquets JavaScript
- **MAMP/Local** - Serveur local

## 📚 Documentation

- [Documentation WordPress](https://developer.wordpress.org/)
- [Documentation Timber](https://timber.github.io/timber/)
- [Documentation Vite](https://vitejs.dev/)
- [Thème Starter - README](wp-content/themes/starter-theme/README.md)

## 🐛 Problèmes courants

### "Page not found" après installation

**Solution** : Aller dans **Réglages → Permaliens** et cliquer sur "Enregistrer" pour régénérer les règles de réécriture.

### Erreur "Établir connexion à la base de données"

**Solution** : Vérifier les identifiants dans `wp-config.php` et que le serveur MySQL est démarré.

### Assets non chargés (404 sur CSS/JS)

**Solution** :

```bash
cd wp-content/themes/starter-theme
npm run build
```

### HMR ne fonctionne pas

**Solution** :

```bash
npm run dev:clean
```

## 📞 Support

Pour toute question sur le projet, contacter l'enseignant ou consulter la documentation du thème.

## 📄 Licence

Ce projet est un projet éducatif dans le cadre du cours CMS - Février 2026.

---

**📌 Note** : Ce README concerne la structure globale du projet WordPress. Pour la documentation spécifique au thème, voir [wp-content/themes/starter-theme/README.md](wp-content/themes/starter-theme/README.md)
