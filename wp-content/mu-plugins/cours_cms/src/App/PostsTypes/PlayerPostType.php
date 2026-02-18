<?php

namespace App\PostsTypes;

use Wkn\PostType\PostTypeAbstract;

defined('ABSPATH') || exit;

class PlayerPostType extends PostTypeAbstract
{
    protected $_name = 'app_player';

    public function __construct()
    {
        parent::__construct();
        $this->setLabels([
            'name'               => __('Joueurs', 'app'),
            'singular_name'      => __('Joueur', 'app'),
            'menu_name'          => __('Joueurs', 'app'),
            'all_items'          => __('Tous les joueurs', 'app'),
            'add_new'            => __('Ajouter un joueur', 'app'),
            'add_new_item'       => __('Ajouter un nouveau joueur', 'app'),
            'edit_item'          => __('Modifier le joueur', 'app'),
            'new_item'           => __('Nouveau joueur', 'app'),
            'view_item'          => __('Voir le joueur', 'app'),
            'view_items'         => __('Voir les joueurs', 'app'),
            'search_items'       => __('Rechercher un joueur', 'app'),
            'not_found'          => __('Aucun joueur trouvé', 'app'),
            'not_found_in_trash' => __('Aucun joueur dans la corbeille', 'app'),
        ])->setArgs([
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'players', 'with_front' => false],
            
            // ✅ caps custom
            'capability_type' => ['app_player', 'app_players'],
            'map_meta_cap'    => true,
            'capabilities'    => [
                'read_post'              => 'read_app_player',
                'read_private_posts'     => 'read_private_app_players',
                'edit_post'              => 'edit_app_player',
                'edit_posts'             => 'edit_app_players',
                'edit_others_posts'      => 'edit_others_app_players',
                'edit_published_posts'   => 'edit_published_app_players',
                'publish_posts'          => 'publish_app_players',
                'delete_post'            => 'delete_app_player',
                'delete_posts'           => 'delete_app_players',
                'delete_others_posts'    => 'delete_others_app_players',
                'delete_published_posts' => 'delete_published_app_players',
                'create_posts'           => 'create_app_players',
            ],

            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-admin-users',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'can_export'         => true,
            'taxonomies'         => ['app_player_taxonomy'],
        ]);
    }
}
