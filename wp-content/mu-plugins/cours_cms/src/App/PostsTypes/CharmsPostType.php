<?php

namespace App\PostsTypes;

use Wkn\PostType\PostTypeAbstract;

defined('ABSPATH') || exit;

class CharmsPostType extends PostTypeAbstract
{
    protected $_name = 'app_charm';

    public function __construct()
    {
        parent::__construct();
        $this->setLabels([
            'name'               => __('Charms', 'app'),
            'singular_name'      => __('Charm', 'app'),
            'menu_name'          => __('Charms', 'app'),
            'all_items'          => __('Tous les charms', 'app'),
            'add_new'            => __('Ajouter un charm', 'app'),
            'add_new_item'       => __('Ajouter un nouveau charm', 'app'),
            'edit_item'          => __('Modifier le charm', 'app'),
            'new_item'           => __('Nouvel charm', 'app'),
            'view_item'          => __('Voir le charm', 'app'),
            'view_items'         => __('Voir les charms', 'app'),
            'search_items'       => __('Rechercher un charm', 'app'),
            'not_found'          => __('Aucun charm trouvé', 'app'),
            'not_found_in_trash' => __('Aucun charm dans la corbeille', 'app'),
        ])->setArgs([
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'charms', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-star-filled',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'can_export'         => true,
            'taxonomies'         => ['app_charm_taxonomy'],
        ]);
    }
}
