# Odyssee MU-Plugin

Point d’entrée : chargé via `wp-content/mu-plugins/cours_cms.php`.

## Structure

- **bootstrap.php** : constantes `APP_PATH`, `APP_URL`, autoload Composer + PSR-4 `App\`, initialisation `Wkn\Wokine::init([...])`.
- **src/App/** : Configuration, Theme, Header, Footer, Pagination, ClassMapper, Controllers, PostsTypes, Taxonomies, Pages, Posts, Acf, Utils.
- **src/Wkn/** : stubs du framework (Wokine, Theme, ControllerAbstract, PostTypeAbstract, TaxonomyAbstract).

## Options ACF

- **Configuration** : page parente `mon-site-settings` (slug). Pour garder l’ancien menu, remplacer par `site-configuration` dans `Configuration.php`.
- **Header** / **Footer** : sous-pages avec `post_id` `header` et `footer` ; en Twig : `{{ config('key') }}`, `{{ header('key') }}`, `{{ footer('key') }}`.

## Blocs ACF

Les blocs sont enregistrés depuis le thème : `get_template_directory().'/views/blocks'`. Chaque bloc est un sous-dossier contenant `block.json`. Pour migrer les anciens blocs (ex. `mu-plugins/templates/blocks/*.php`), créer dans le thème `views/blocks/<slug>/block.json` et `views/blocks/<slug>/<slug>.twig`, puis utiliser le rendu Timber dans `App\Acf\AcfBlocks::render_block()` si besoin.

## Composer

```bash
cd wp-content/mu-plugins/cours_cms && composer install
```

Ne pas committer `vendor/` (voir `.gitignore`).

## Formulaire Sur-mesure (Charm Request)

Le formulaire de la page Sur-mesure est sécurisé par :

- **Nonce CSRF** (WordPress)
- **Honeypot** (champ invisible pour bloquer les bots)
- **Rate limiting** : 3 envois max par IP par heure (transient)
- **Validation** : longueurs max (nom/prénom 100, message 5000, téléphone 30), `is_email()`, pièce jointe 5 Mo, MIME types autorisés
- **reCAPTCHA v3** (optionnel) : si les clés sont définies dans `wp-config.php`, vérification côté serveur avant enregistrement

### Activer reCAPTCHA v3

1. Créer un site reCAPTCHA v3 sur [Google reCAPTCHA Admin](https://www.google.com/recaptcha/admin).
2. Dans `wp-config.php` (avant `/* That's all, stop editing! */`) :

```php
define('CHARM_REQUEST_RECAPTCHA_SITE_KEY', 'ta_cle_site');
define('CHARM_REQUEST_RECAPTCHA_SECRET_KEY', 'ta_cle_secret');
// Optionnel : seuil du score (0.0–1.0), défaut 0.5
define('CHARM_REQUEST_RECAPTCHA_THRESHOLD', 0.5);
```

Sans ces constantes, le formulaire fonctionne avec honeypot + rate limiting + nonce.
