# Documentation - Subscribers avec ACF

## Description

Les données de tous les subscribers (et customers) sont automatiquement récupérées avec leurs champs ACF et disponibles dans le contexte Timber global.

## Structure des données

Chaque subscriber contient :

```php
[
    'id' => 123,                    // ID de l'utilisateur
    'username' => 'john_doe',       // Nom d'utilisateur
    'display_name' => 'John Doe',   // Nom affiché
    'email' => 'john@example.com',  // Email
    'registered' => '2026-01-15',   // Date d'inscription
    'avatar' => [                   // Image ACF (array ou null)
        'url' => '...',
        'width' => 150,
        'height' => 150,
        // ... autres infos de l'image
    ],
    'points' => 42                  // Nombre de points (int)
]
```

Les subscribers sont **automatiquement triés par points décroissants**.

## Utilisation dans Twig

### Dans n'importe quel template

Les données sont disponibles via la variable `subscribers` :

```twig
{# Afficher tous les subscribers #}
{% for subscriber in subscribers %}
    <div>
        <h3>{{ subscriber.display_name }}</h3>
        <p>Points: {{ subscriber.points }}</p>

        {% if subscriber.avatar %}
            <img src="{{ subscriber.avatar.url }}" alt="{{ subscriber.display_name }}">
        {% endif %}
    </div>
{% endfor %}
```

### Limiter le nombre de résultats

```twig
{# Afficher seulement les 5 premiers #}
{% for subscriber in subscribers|slice(0, 5) %}
    ...
{% endfor %}
```

### Utiliser le partial leaderboard

```twig
{# Afficher le classement complet #}
{% include 'partials/subscribers-leaderboard.twig' with {
    subscribers: subscribers
} %}

{# Afficher seulement le top 10 #}
{% include 'partials/subscribers-leaderboard.twig' with {
    subscribers: subscribers,
    limit: 10
} %}
```

### Exemples avancés

#### Afficher le nombre total de joueurs

```twig
<p>{{ subscribers|length }} joueurs inscrits</p>
```

#### Afficher le total de points

```twig
{% set total_points = 0 %}
{% for subscriber in subscribers %}
    {% set total_points = total_points + subscriber.points %}
{% endfor %}
<p>Total de points cumulés: {{ total_points }}</p>
```

#### Afficher uniquement les joueurs avec plus de 100 points

```twig
{% for subscriber in subscribers %}
    {% if subscriber.points > 100 %}
        <div>{{ subscriber.display_name }}: {{ subscriber.points }} pts</div>
    {% endif %}
{% endfor %}
```

## Utilisation en PHP

### Récupérer les données manuellement

```php
$subscribers = \App\Theme::getSubscribersWithInfos();
```

### Dans un controller custom

```php
$context = Timber::context();
$context['top_players'] = array_slice($context['subscribers'], 0, 3);
Timber::render('my-template.twig', $context);
```

## Mise à jour des données

Les données sont récupérées à chaque chargement de page. Si vous modifiez les points ou l'avatar d'un utilisateur depuis le back-office, les changements seront immédiatement visibles sur le front.

## Performance

- Les données sont récupérées via `get_users()` avec filtrage par rôle
- Les champs ACF sont chargés via `get_field()`
- Tri effectué en PHP avec `usort()`
- Pour de meilleures performances avec beaucoup d'utilisateurs, envisagez d'ajouter un système de cache

## Fichiers concernés

- **PHP**: `/wp-content/mu-plugins/cours_cms/src/App/Theme.php` (méthode `getSubscribersWithInfos()`)
- **Twig**: `/wp-content/themes/starter-theme/views/partials/subscribers-leaderboard.twig`
- **Exemple**: `/wp-content/themes/starter-theme/views/pages/front-page.twig`
- **ACF JSON**: `/wp-content/themes/starter-theme/acf-json/group_6995bafb2cb7b.json`
