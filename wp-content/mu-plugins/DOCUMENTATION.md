# Documentation MU-Plugin - Architecture MVC

## 📋 Vue d'ensemble

Ce **Must-Use Plugin** (mu-plugin) constitue le cœur de l'architecture MVC du projet. Il est chargé automatiquement par WordPress avant les plugins normaux et gère toute la logique métier de l'application.

## 🎯 Pourquoi un MU-Plugin ?

### Avantages

1. **Chargement automatique** : Pas besoin d'activation, le plugin est toujours actif
2. **Priorité de chargement** : Chargé avant les plugins standards
3. **Architecture centralisée** : Toute la logique métier au même endroit
4. **Séparation des responsabilités** : Code métier séparé du thème (présentation)
5. **Maintenabilité** : Structure claire et organisée
6. **Réutilisabilité** : Le code métier peut être partagé entre différents thèmes

### Inconvénients gérés

- ❌ Pas d'interface d'administration → ✅ Non nécessaire, tout est en code
- ❌ Pas de mises à jour automatiques → ✅ Géré via Git/Composer

## 📁 Structure détaillée

```
wp-content/mu-plugins/
├── bootstrap.php                    # Point d'entrée principal du mu-plugin
├── composer.json                    # Dépendances PHP (Timber, etc.)
├── vendor/                          # Dépendances Composer (autoload)
│
├── src/App/                         # Code source principal
│   ├── Configuration.php            # Configuration globale du site
│   ├── Theme.php                    # Fonctionnalités du thème
│   ├── Header.php                   # Configuration du header
│   ├── Footer.php                   # Configuration du footer
│   ├── ClassMapper.php              # Mapping templates → classes
│   │
│   ├── Acf/                         # Configuration ACF
│   │   └── Blocks.php               # Enregistrement des blocs Gutenberg ACF
│   │
│   ├── Controllers/                 # Controllers (logique métier)
│   │   └── ClientController.php     # Exemple de controller
│   │
│   ├── Pages/                       # Classes pour les pages
│   │   ├── Page.php                 # Classe de base pour toutes les pages
│   │   ├── FrontPage.php            # Page d'accueil
│   │   └── Archive.php              # Classe de base pour les archives
│   │
│   ├── Posts/                       # Classes pour les posts
│   │   ├── Post.php                 # Classe de base pour tous les posts
│   │   └── Event.php                # Exemple : Custom Post Type Event
│   │
│   ├── PostTypes/                  # Déclarations des CPT
│   │   └── Event.php                # Enregistrement du CPT Event
│   │
│   └── Taxonomies/                  # Déclarations des taxonomies
│       └── EventCategory.php        # Enregistrement de la taxonomie
│
└── templates/                       # Templates PHP pour les blocs ACF
    └── blocks/
        ├── hero.php                 # Template PHP du bloc Hero
        ├── cta.php                  # Template PHP du bloc CTA
        └── slider.php               # Template PHP du bloc Slider
```

## 🔧 Composants principaux

### 1. bootstrap.php

**Rôle** : Point d'entrée du mu-plugin, orchestre le chargement de tous les composants.

**Fonctionnement** :

```php
1. Charge l'autoloader Composer (PSR-4)
2. Initialise les classes principales (Configuration, Theme, Header, Footer, ClassMapper)
3. Charge automatiquement tous les Custom Post Types
4. Charge automatiquement toutes les Taxonomies
5. Enregistre les blocs ACF
```

**Hooks utilisés** :

- `after_setup_theme` (priorité 10) : Initialisation des classes principales
- `init` (priorité 5) : Enregistrement des CPT et taxonomies
- `acf/init` : Enregistrement des blocs ACF

---

### 2. Configuration.php

**Rôle** : Gère toutes les configurations globales du site.

**Fonctionnalités** :

1. **Pages d'options ACF**

   - Page principale : "Configuration du site"
   - Sous-pages : Header, Footer, Réseaux sociaux

2. **Variables globales dans le contexte Timber**

   - Configuration du site (nom, URL, etc.)
   - Réseaux sociaux
   - Langue courante (détection via URL `/en/`)
   - Coordonnées de contact

3. **Détection de la langue**
   - Parse l'URL pour détecter `/en/`, `/fr/`, etc.
   - Langue par défaut : français

**Méthodes principales** :

```php
getInstance()               // Singleton
addToContext($context)      // Ajoute les variables au contexte Timber
getGlobalConfig()          // Récupère la config globale
getSocials()               // Récupère les liens sociaux
getCurrentLanguage()       // Détecte la langue courante
isEnglish()                // Vérifie si la langue est l'anglais
```

**Variables disponibles dans Twig** :

```twig
{{ config.site_name }}
{{ config.contact_email }}
{{ socials.facebook }}
{{ current_lang }}
{{ is_english }}
```

---

### 3. Theme.php

**Rôle** : Gère les fonctionnalités globales du thème WordPress.

**Fonctionnalités** :

1. **Configuration Timber**

   - Dossier des templates : `views/`
   - Cache activé en production
   - Autoescape désactivé

2. **Support WordPress**

   - Title tag
   - Post thumbnails
   - Formats de posts
   - HTML5
   - Logo personnalisé
   - Éditeur de blocs (align-wide, responsive-embeds)

3. **Menus de navigation**

   - `header_main` : Menu principal header
   - `header_secondary` : Menu secondaire header
   - `footer_main` : Menu principal footer
   - `footer_secondary` : Menu secondaire footer
   - `footer_legal` : Menu légal footer

4. **Tailles d'images personnalisées**
   - `card` : 600x400 (pour les cartes)
   - `hero` : 1920x1080 (pour les héros)
   - `thumbnail-large` : 400x400 (vignettes)
   - `gallery` : 800x600 (galeries)

**Variables disponibles dans Twig** :

```twig
{{ theme.name }}
{{ theme.version }}
{{ theme.uri }}
{{ menu_header_main }}
{{ menu_footer_main }}
{{ logo }}
```

---

### 4. Header.php

**Rôle** : Gère la configuration et les données du header du site.

**Fonctionnalités** :

1. **Logo**

   - Logo ACF personnalisé ou logo WordPress par défaut

2. **CTA (Call-to-Action)**

   - Texte, lien, style (primary, secondary, outline)
   - Activation/désactivation

3. **Options supplémentaires**
   - Recherche activée/désactivée
   - Header sticky
   - Header transparent

**Variables disponibles dans Twig** :

```twig
{{ header.logo }}
{{ header.cta.text }}
{{ header.cta.link }}
{{ header.search_enabled }}
{{ header.sticky }}
{{ header.transparent }}
```

---

### 5. Footer.php

**Rôle** : Gère la configuration et les données du footer du site.

**Fonctionnalités** :

1. **Logo et description**

   - Logo personnalisé ou logo principal
   - Description du site

2. **Newsletter**

   - Titre, description
   - ID du formulaire (Contact Form 7, Gravity Forms, etc.)

3. **Coordonnées de contact**

   - Email, téléphone, adresse

4. **Copyright**
   - Texte personnalisable avec variables dynamiques
   - Variables : `{year}`, `{site_name}`

**Variables disponibles dans Twig** :

```twig
{{ footer.logo }}
{{ footer.description }}
{{ footer.newsletter.title }}
{{ footer.contact.email }}
{{ footer.copyright }}
```

---

### 6. ClassMapper.php

**Rôle** : Mapping entre les templates WordPress et les classes Timber personnalisées.

**Fonctionnement** :

Le ClassMapper indique à Timber quelle classe PHP utiliser pour chaque type de contenu :

```php
// Au lieu de Timber\Post, utilise App\Posts\Event pour les événements
'app_event' => 'App\\Posts\\Event'
```

**Filtres utilisés** :

1. `Timber\PostClassMap` : Mapping des posts
2. `Timber\TermClassMap` : Mapping des taxonomies (optionnel)

**Mapping automatique** :

```php
// Posts standards
'post' → App\Posts\Post
'page' → App\Pages\Page

// Custom Post Types
'app_event' → App\Posts\Event
'app_news' → App\Posts\News
etc.
```

**Helper getPageClass()** :

Permet de récupérer automatiquement la bonne classe dans les templates PHP :

```php
// front-page.php
$context['post'] = new (App\ClassMapper::getPageClass());
```

---

## 📦 Classes Pages/

### Page.php (Classe de base)

**Rôle** : Classe de base pour toutes les pages, étend `Timber\Post`.

**Méthodes principales** :

```php
getBlocks()                    // Récupère les blocs Gutenberg
hasTemplate($template)         // Vérifie si la page a un template spécifique
getSeoTitle()                  // Titre SEO (Yoast/Rank Math ou titre page)
getSeoDescription()            // Meta description
```

**Usage dans un template PHP** :

```php
// page.php
$context = Timber::context();
$context['post'] = new App\Pages\Page();
Timber::render('pages/page.twig', $context);
```

---

### FrontPage.php

**Rôle** : Gère la logique spécifique de la page d'accueil.

**Méthodes spécifiques** :

```php
getLatestEvents($count = 3)         // Derniers événements
getLatestNews($count = 3)           // Dernières actualités
getFeaturedTestimonials()           // Témoignages mis en avant
```

**Usage** :

```php
// front-page.php
$context = Timber::context();
$context['post'] = new App\Pages\FrontPage();
$context['latest_events'] = $context['post']->getLatestEvents(3);
Timber::render('pages/front-page.twig', $context);
```

---

### Archive.php

**Rôle** : Classe de base pour toutes les pages d'archives.

**Propriétés** :

```php
$post_type    // Type de post de l'archive
$posts        // Posts de l'archive
```

**Méthodes** :

```php
getPosts()                    // Récupère les posts
getPagination()              // Récupère la pagination
getArchiveTitle()            // Titre de l'archive
getArchiveDescription()      // Description de l'archive
getFilters()                 // Filtres disponibles (taxonomies)
```

**Usage** :

```php
// archive-app_event.php
$context = Timber::context();
$archive = new App\Pages\Archive();
$context['posts'] = $archive->getPosts();
$context['pagination'] = $archive->getPagination();
$context['filters'] = $archive->getFilters();
Timber::render('pages/archive-event.twig', $context);
```

---

## 📦 Classes Posts/

### Post.php (Classe de base)

**Rôle** : Classe de base pour tous les types de posts personnalisés.

**Méthodes communes** :

```php
getFormattedDate($format)         // Date formatée en français
getRelatedPosts($count)           // Posts liés (même catégorie)
getReadingTime()                  // Temps de lecture estimé
isRecent()                        // Post récent (< 7 jours)
getShareUrl($network)             // URL de partage social
```

**Usage** :

```twig
{# Dans Twig #}
{{ post.getFormattedDate('d F Y') }}
{{ post.getReadingTime() }} minutes de lecture
{{ post.getShareUrl('facebook') }}
```

---

### Event.php (Exemple de CPT)

**Rôle** : Représente un événement avec ses méthodes spécifiques.

**Méthodes spécifiques** :

```php
getEventDate()                    // Date de l'événement formatée
getEventTime()                    // Heure de l'événement
getEventLocation()                // Lieu (adresse, ville, etc.)
isPast()                          // Événement passé ?
isUpcoming()                      // Événement à venir ?
getRegistrationLink()             // Lien d'inscription
isRegistrationOpen()              // Inscriptions ouvertes ?
```

**Usage** :

```twig
{# single-app_event.twig #}
<h1>{{ post.title }}</h1>
<time>{{ post.getEventDate() }} à {{ post.getEventTime() }}</time>

{% if post.isUpcoming() and post.isRegistrationOpen() %}
  <a href="{{ post.getRegistrationLink() }}">S'inscrire</a>
{% endif %}
```

---

## 🗂️ Custom Post Types

### Déclaration (PostTypes/)

**Fichier** : `Event.php`

**Structure** :

```php
namespace App\PostTypes;

add_action('init', function() {
    register_post_type('app_event', [
        'label' => 'Événements',
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite' => ['slug' => 'evenements'],
    ]);
});
```

**Nomenclature** :

- Préfixe : `app_` (évite les conflits)
- Singulier : `app_event`, `app_news`, etc.
- Slug : en français (`evenements`, `actualites`)

**Liste des CPT disponibles** :

- `app_event` : Événements
- `app_news` : Actualités
- `app_story` : Histoires/témoignages
- `app_partner` : Partenaires
- `app_project` : Projets
- `app_school` : Écoles
- `app_society` : Sociétés
- `app_team` : Équipe
- `app_testimonial` : Témoignages

---

## 🏷️ Taxonomies

### Déclaration (Taxonomies/)

**Fichier** : `EventCategory.php`

**Structure** :

```php
namespace App\Taxonomies;

add_action('init', function() {
    register_taxonomy('app_event_category', ['app_event'], [
        'label' => 'Catégories d\'événements',
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite' => ['slug' => 'evenement-categorie'],
    ]);
});
```

**Types de taxonomies** :

- **Hierarchical = true** : Comme les catégories (parents/enfants)
- **Hierarchical = false** : Comme les tags (étiquettes)

---

## 🎨 Blocs ACF (Acf/)

### Configuration (Acf/Blocks.php)

**Rôle** : Enregistre les blocs Gutenberg ACF personnalisés.

**Structure d'un bloc** :

```php
acf_register_block_type([
    'name' => 'hero',                              // Identifiant unique
    'title' => 'Hero',                             // Nom affiché
    'description' => 'Bloc hero...',               // Description
    'render_template' => '...blocks/hero.php',     // Template PHP
    'category' => 'layout',                        // Catégorie Gutenberg
    'icon' => 'cover-image',                       // Icône Dashicons
    'keywords' => ['hero', 'banner'],              // Mots-clés recherche
    'supports' => [
        'align' => true,                           // Alignements
        'mode' => true,                            // Mode edit/preview
        'jsx' => true,                             // Support JSX
    ],
    'mode' => 'preview',                           // Mode par défaut
    'example' => [...]                             // Aperçu dans l'inserter
]);
```

**Catégories Gutenberg** :

- `layout` : Mise en page
- `media` : Médias
- `formatting` : Formatage
- `widgets` : Widgets
- `embed` : Intégrations

---

### Templates de blocs (templates/blocks/)

**Fichier** : `hero.php`

**Rôle** : Template PHP appelé par ACF pour rendre le bloc.

**Structure** :

```php
use Timber\Timber;

// 1. Récupération de l'ID et des classes du bloc
$block_id = 'hero-' . $block['id'];
$classes = 'block-hero';
if (!empty($block['align'])) {
    $classes .= ' align' . $block['align'];
}

// 2. Préparation du contexte pour Twig
$context = Timber::context();
$context['block'] = [
    'id' => $block_id,
    'classes' => $classes,
    'title' => get_field('hero_title'),
    'subtitle' => get_field('hero_subtitle'),
    // ... autres champs ACF
];

// 3. Rendu du template Twig
Timber::render('blocks/hero.twig', $context);
```

**Variables disponibles** :

- `$block` : Informations du bloc (ID, classes, align, etc.)
- `$is_preview` : Mode preview dans l'éditeur
- `get_field()` : Récupération des champs ACF du bloc

---

## 🎮 Controllers (Controllers/)

### Rôle des Controllers

Les controllers gèrent la **logique métier** :

- Actions AJAX
- API REST personnalisées
- Traitements de formulaires
- Shortcodes
- Webhooks

**Ils ne doivent PAS** :

- Contenir du HTML
- Gérer l'affichage directement
- Être couplés au thème

---

### Exemple : ClientController.php

**Fonctionnalités** :

1. **Action AJAX** : `get_clients`

   - URL : `/wp-admin/admin-ajax.php?action=get_clients`
   - Sécurité : Vérification du nonce
   - Retourne JSON

2. **Shortcode** : `[clients_list count="6"]`
   - Affiche une liste de clients
   - Utilise un template Twig

**Structure** :

```php
class ClientController {
    // Singleton
    public static function getInstance() {...}

    // Initialisation (hooks, shortcodes)
    private function init() {
        add_action('wp_ajax_get_clients', [$this, 'getClients']);
        add_shortcode('clients_list', [$this, 'renderClientsList']);
    }

    // Action AJAX
    public function getClients() {
        // Vérification nonce
        // Récupération des données
        // Retour JSON
        wp_send_json_success($data);
    }

    // Shortcode
    public function renderClientsList($atts) {
        // Parse des attributs
        // Récupération des posts
        // Rendu Twig
        return Timber::compile('...', $context);
    }
}
```

**Initialisation** :

Dans `bootstrap.php` :

```php
if (class_exists('App\Controllers\ClientController')) {
    App\Controllers\ClientController::getInstance();
}
```

---

## 🔄 Flux de données

### 1. Chargement d'une page

```
1. WordPress charge le mu-plugin (bootstrap.php)
   ↓
2. Bootstrap initialise les classes (Configuration, Theme, etc.)
   ↓
3. WordPress détermine le template à charger (front-page.php, page.php, etc.)
   ↓
4. Le template PHP crée le contexte et instancie la bonne classe
   ↓
5. ClassMapper mappe automatiquement vers la bonne classe
   ↓
6. Les données sont préparées et passées au template Twig
   ↓
7. Timber compile le template Twig et génère le HTML
```

---

### 2. Affichage d'un bloc ACF

```
1. Utilisateur ajoute un bloc dans Gutenberg
   ↓
2. ACF appelle le template PHP du bloc (templates/blocks/hero.php)
   ↓
3. Le template PHP récupère les champs ACF avec get_field()
   ↓
4. Les données sont passées au contexte Timber
   ↓
5. Timber rend le template Twig (views/blocks/hero.twig)
   ↓
6. Le HTML du bloc est injecté dans la page
```

---

### 3. Action AJAX

```
1. JavaScript envoie une requête AJAX à admin-ajax.php
   ↓
2. WordPress déclenche l'action correspondante (wp_ajax_*)
   ↓
3. Le controller traite la requête
   ↓
4. Vérification du nonce (sécurité)
   ↓
5. Récupération/traitement des données
   ↓
6. Retour JSON (wp_send_json_success/error)
   ↓
7. JavaScript reçoit la réponse et met à jour le DOM
```

---

## 🛠️ Installation et configuration

### 1. Installation initiale

```bash
# 1. Aller dans le dossier mu-plugins
cd wp-content/mu-plugins

# 2. Installer les dépendances PHP
composer install

# 3. Activer ACF Pro (requis)
# Télécharger ACF Pro et l'installer dans wp-content/plugins/
```

---

### 2. Configuration requise

**PHP** :

- Version : >= 8.0
- Extensions : mbstring, gd, curl

**WordPress** :

- Version : >= 6.0

**Plugins requis** :

- Advanced Custom Fields Pro

**Plugins recommandés** :

- Yoast SEO ou Rank Math (SEO)
- Contact Form 7 (formulaires)

---

### 3. Après installation

1. **Aller dans Réglages > Permaliens**

   - Cliquer sur "Enregistrer" pour régénérer les règles de réécriture
   - Important après avoir créé des CPT

2. **Configurer les options ACF**

   - Aller dans "Configuration" dans l'admin WordPress
   - Remplir les champs : logo, coordonnées, réseaux sociaux

3. **Créer les menus**

   - Aller dans Apparence > Menus
   - Créer les menus : header_main, footer_main, etc.

4. **Synchroniser les champs ACF**
   - Aller dans "Groupes de champs"
   - Si vous voyez "Synchroniser disponible", cliquez dessus

---

## 📝 Bonnes pratiques

### Nomenclature

**Custom Post Types** :

- Préfixe : `app_`
- Singulier : `app_event`, `app_news`
- Pas d'espaces ni de majuscules

**Taxonomies** :

- Préfixe : `app_`
- Format : `app_{post_type}_{taxonomy}`
- Ex : `app_event_category`

**Champs ACF** :

- Format : `{context}_{field_name}`
- Ex : `hero_title`, `footer_logo`, `event_date`

**Classes PHP** :

- PascalCase : `EventController`, `FrontPage`
- Namespaces : `App\Controllers`, `App\Pages`

**Méthodes** :

- camelCase : `getEventDate()`, `isRegistrationOpen()`
- Préfixes : `get`, `is`, `has`, `set`

---

### Sécurité

1. **Actions AJAX** : Toujours vérifier le nonce

```php
if (!check_ajax_referer('my_nonce', 'nonce', false)) {
    wp_send_json_error(['message' => 'Nonce invalide']);
}
```

2. **Échappement des données** :

```php
// PHP
echo esc_html($title);
echo esc_url($link);

// Twig
{{ title|e }}
{{ link|e('url') }}
```

3. **Sanitisation des entrées** :

```php
$value = sanitize_text_field($_POST['value']);
$email = sanitize_email($_POST['email']);
```

---

### Performance

1. **Caching Timber** :

   - Activer en production dans `Theme.php`
   - Vider le cache après modifications : `Timber::$cache = false;`

2. **Requêtes optimisées** :

```php
// ❌ Mauvais : N+1 queries
foreach ($posts as $post) {
    $author = $post->author();
}

// ✅ Bon : 1 seule query
$posts = Timber::get_posts([
    'post_type' => 'post',
    'posts_per_page' => 10
]);
```

3. **Lazy loading des images** :

```twig
<img src="{{ post.thumbnail.src }}" loading="lazy" />
```

---

## 🐛 Débogage

### Activer le mode debug WordPress

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Debug Timber

```php
// Dans un template PHP
Timber::$debug = true;

// Afficher une variable dans Twig
{{ dump(post) }}
```

### Vérifier les classes chargées

```php
// Dans un template PHP
var_dump(get_class($post)); // Affiche : App\Posts\Event
```

---

## 📚 Ressources

### Documentation officielle

- [Timber](https://timber.github.io/docs/)
- [Twig](https://twig.symfony.com/doc/)
- [ACF](https://www.advancedcustomfields.com/resources/)
- [WordPress Codex](https://codex.wordpress.org/)

### Exemples de code

Voir les fichiers d'exemple dans le mu-plugin :

- Pages : `src/App/Pages/`
- Posts : `src/App/Posts/`
- Controllers : `src/App/Controllers/`

---

## 🎓 Exemples d'utilisation

### Créer un nouveau Custom Post Type

1. **Créer le fichier de déclaration**

```php
// src/App/PostTypes/News.php
namespace App\PostTypes;

add_action('init', function() {
    register_post_type('app_news', [
        'label' => 'Actualités',
        'public' => true,
        'has_archive' => true,
        'show_in_rest' => true,
        'menu_icon' => 'dashicons-megaphone',
        'supports' => ['title', 'editor', 'thumbnail'],
        'rewrite' => ['slug' => 'actualites'],
    ]);
});
```

2. **Créer la classe Post**

```php
// src/App/Posts/News.php
namespace App\Posts;

class News extends Post {
    public function getPublicationDate() {
        return get_field('publication_date', $this->ID);
    }
}
```

3. **Ajouter le mapping**

```php
// src/App/ClassMapper.php
'app_news' => 'App\\Posts\\News'
```

4. **Créer les templates**

```php
// themes/starter-theme/single-app_news.php
$context = Timber::context();
$context['post'] = Timber::get_post();
Timber::render('pages/single-news.twig', $context);
```

5. **Regénérer les permaliens**
   - Admin > Réglages > Permaliens > Enregistrer

---

### Créer un nouveau bloc ACF

1. **Enregistrer le bloc**

```php
// src/App/Acf/Blocks.php
acf_register_block_type([
    'name' => 'testimonial',
    'title' => 'Témoignage',
    'render_template' => WP_CONTENT_DIR . '/mu-plugins/templates/blocks/testimonial.php',
    'category' => 'formatting',
    'icon' => 'format-quote',
]);
```

2. **Créer le template PHP**

```php
// templates/blocks/testimonial.php
use Timber\Timber;

$context = Timber::context();
$context['block'] = [
    'quote' => get_field('testimonial_quote'),
    'author' => get_field('testimonial_author'),
    'photo' => get_field('testimonial_photo'),
];

Timber::render('blocks/testimonial.twig', $context);
```

3. **Créer le template Twig**

```twig
{# themes/starter-theme/views/blocks/testimonial.twig #}
<blockquote class="testimonial">
  <p>{{ block.quote }}</p>
  <footer>
    <img src="{{ block.photo.url }}" alt="{{ block.author }}">
    <cite>{{ block.author }}</cite>
  </footer>
</blockquote>
```

4. **Créer les champs ACF**
   - Admin > Groupes de champs > Ajouter
   - Emplacement : Bloc = Témoignage
   - Champs : quote (textarea), author (text), photo (image)

---

### Créer un controller AJAX

1. **Créer le controller**

```php
// src/App/Controllers/FormController.php
namespace App\Controllers;

class FormController {
    public static function getInstance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    private function __construct() {
        add_action('wp_ajax_submit_form', [$this, 'handleSubmit']);
        add_action('wp_ajax_nopriv_submit_form', [$this, 'handleSubmit']);
    }

    public function handleSubmit() {
        check_ajax_referer('form_nonce', 'nonce');

        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);

        // Traitement...

        wp_send_json_success(['message' => 'Formulaire envoyé']);
    }
}
```

2. **Initialiser dans bootstrap.php**

```php
if (class_exists('App\Controllers\FormController')) {
    App\Controllers\FormController::getInstance();
}
```

3. **Appeler depuis JavaScript**

```javascript
fetch("/wp-admin/admin-ajax.php", {
  method: "POST",
  body: new URLSearchParams({
    action: "submit_form",
    nonce: window.formNonce,
    name: "John Doe",
    email: "john@example.com",
  }),
})
  .then((r) => r.json())
  .then((data) => console.log(data));
```

---

## ✅ Checklist de mise en place

- [ ] Copier le dossier `mu-plugins/` dans `wp-content/`
- [ ] Installer les dépendances : `composer install`
- [ ] Installer et activer ACF Pro
- [ ] Regénérer les permaliens (Réglages > Permaliens > Enregistrer)
- [ ] Créer les menus de navigation
- [ ] Configurer les options dans "Configuration"
- [ ] Synchroniser les champs ACF
- [ ] Créer les Custom Post Types nécessaires
- [ ] Créer les blocs ACF
- [ ] Tester les templates Twig

---

## 🆘 Problèmes fréquents

### Les Custom Post Types ne s'affichent pas

**Solution** : Regénérer les permaliens

- Aller dans Réglages > Permaliens
- Cliquer sur "Enregistrer" sans rien modifier

### Erreur "Class not found"

**Solutions** :

1. Vérifier que Composer est installé : `composer install`
2. Vérifier le namespace et l'autoload PSR-4 dans `composer.json`
3. Régénérer l'autoload : `composer dump-autoload`

### Les blocs ACF ne s'affichent pas

**Solutions** :

1. Vérifier qu'ACF Pro est installé et activé
2. Vérifier que le chemin du template est correct
3. Vérifier que le fichier `Blocks.php` est chargé dans `bootstrap.php`

### Les champs ACF sont vides

**Solutions** :

1. Vérifier que les champs sont bien configurés (emplacement)
2. Synchroniser les champs (Groupes de champs > Synchroniser)
3. Vérifier que `acf-json/` contient les fichiers JSON

---

## 📖 Conclusion

Ce mu-plugin constitue le **cœur de l'architecture MVC** du projet. Il sépare clairement :

- **Logique métier** → MU-Plugin (`wp-content/mu-plugins/`)
- **Présentation** → Thème (`wp-content/themes/starter-theme/`)
- **Données** → WordPress + ACF

Cette séparation permet une **maintenabilité optimale**, une **réutilisabilité du code**, et une **collaboration efficace** entre développeurs.

---

**Auteur** : Documentation créée le 9 janvier 2026  
**Version** : 1.0.0
