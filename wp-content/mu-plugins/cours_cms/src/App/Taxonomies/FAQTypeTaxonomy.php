<?php

namespace App\Taxonomies;

use Wkn\Taxonomy\TaxonomyAbstract;

defined('ABSPATH') || exit;

class FAQTypeTaxonomy extends TaxonomyAbstract
{
    protected $_name = 'app_faq_taxonomy';

    public function __construct()
    {
        parent::__construct();
        $this->setLabels([
            'name'              => __('Catégories de questions', 'app'),
            'singular_name'     => __('Catégorie de question', 'app'),
            'menu_name'         => __('Catégories de questions', 'app'),
            'all_items'         => __('Toutes les catégories de questions', 'app'),
            'edit_item'         => __('Modifier la catégorie', 'app'),
            'view_item'         => __('Voir la catégorie de question', 'app'),
            'update_item'       => __('Mettre à jour la catégorie de question', 'app'),
            'add_new_item'      => __('Ajouter une nouvelle catégorie de question', 'app'),
            'new_item_name'     => __('Nom de la nouvelle catégorie de question', 'app'),
            'search_items'      => __('Rechercher une catégorie de question', 'app'),
            'popular_items'     => __('Catégories de questions populaires', 'app'),
            'back_to_items'     => __('Retour aux catégories de questions', 'app'),
        ])->setArgs([
            'public'            => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_in_menu'      => true,
            'rewrite'           => ['slug' => 'question-categorie', 'hierarchical' => true, 'with_front' => false],
            'sort'              => true,
        ])->setPostTypes(['app_faq']);
    }
}
