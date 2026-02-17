# wp-content/mu-plugins/

**Must-Use Plugin** - Architecture MVC

## 📦 Installation

```bash
composer install
```

## 📖 Documentation

Voir **[DOCUMENTATION.md](DOCUMENTATION.md)** pour la documentation complète et détaillée.

## 🗂️ Structure

```
mu-plugins/
├── bootstrap.php              # Point d'entrée
├── composer.json              # Dépendances
├── src/App/                   # Code source
│   ├── Configuration.php      # Config globale
│   ├── Theme.php              # Fonctionnalités thème
│   ├── Header.php             # Config header
│   ├── Footer.php             # Config footer
│   ├── ClassMapper.php        # Mapping classes
│   ├── Acf/                   # Config ACF
│   ├── Controllers/           # Controllers
│   ├── Pages/                 # Classes pages
│   ├── Posts/                 # Classes posts
│   ├── PostTypes/            # Déclaration CPT
│   └── Taxonomies/            # Déclaration taxonomies
└── templates/blocks/          # Templates PHP blocs ACF
```

## 🚀 Utilisation

Le mu-plugin est chargé automatiquement par WordPress.

### Créer un Custom Post Type

1. Créer `src/App/PostTypes/MonType.php`
2. Créer `src/App/Posts/MonType.php`
3. Ajouter le mapping dans `ClassMapper.php`
4. Regénérer les permaliens

### Créer un bloc ACF

1. Enregistrer dans `src/App/Acf/Blocks.php`
2. Créer le template PHP dans `templates/blocks/`
3. Créer le template Twig dans le thème

Voir [DOCUMENTATION.md](DOCUMENTATION.md) pour plus de détails.
