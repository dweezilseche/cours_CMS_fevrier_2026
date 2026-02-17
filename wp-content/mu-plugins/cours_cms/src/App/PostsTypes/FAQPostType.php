<?php

namespace App\PostsTypes;

use Wkn\PostType\PostTypeAbstract;

defined('ABSPATH') || exit;

class FAQPostType extends PostTypeAbstract
{
    protected $_name = 'app_faq';

    public function __construct()
    {
        parent::__construct();
        $this->setLabels([
            'name'               => __('FAQ', 'app'),
            'singular_name'      => __('FAQ', 'app'),
            'menu_name'          => __('FAQ', 'app'),
            'all_items'          => __('Toutes les questions', 'app'),
            'add_new'            => __('Ajouter une question', 'app'),
            'add_new_item'       => __('Ajouter une nouvelle question', 'app'),
            'edit_item'          => __('Modifier la question', 'app'),
            'new_item'           => __('Nouvelle question', 'app'),
            'view_item'          => __('Voir la question', 'app'),
            'view_items'         => __('Voir les questions', 'app'),
            'search_items'       => __('Rechercher une question', 'app'),
            'not_found'          => __('Aucune question trouvée', 'app'),
            'not_found_in_trash' => __('Aucune question dans la corbeille', 'app'),
        ])->setArgs([
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'rewrite'            => ['slug' => 'faq', 'with_front' => false],
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 5,
            'menu_icon'          => 'dashicons-editor-help',
            'supports'           => ['title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'],
            'can_export'         => true,
            'taxonomies'         => ['app_faq_taxonomy'],
        ]);
    }
}
