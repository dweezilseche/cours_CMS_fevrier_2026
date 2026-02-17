<?php

namespace App\Taxonomies;

use Wkn\Taxonomy\TaxonomyAbstract;

defined('ABSPATH') || exit;

class CharmsTypeTaxonomy extends TaxonomyAbstract
{
    protected $_name = 'app_charm_taxonomy';

    public function __construct()
    {
        parent::__construct();
        $this->setLabels([
            'name'              => __('Catégories de charms', 'app'),
            'singular_name'     => __('Catégorie de charm', 'app'),
            'menu_name'         => __('Catégories de charms', 'app'),
            'all_items'         => __('Toutes les catégories de charms', 'app'),
            'edit_item'         => __('Modifier la catégorie', 'app'),
            'view_item'         => __('Voir la catégorie de charm', 'app'),
            'update_item'       => __('Mettre à jour la catégorie de charm', 'app'),
            'add_new_item'      => __('Ajouter une nouvelle catégorie de charm', 'app'),
            'new_item_name'     => __('Nom de la nouvelle catégorie de charm', 'app'),
            'search_items'      => __('Rechercher une catégorie de charm', 'app'),
            'popular_items'     => __('Catégories de charms populaires', 'app'),
            'back_to_items'     => __('Retour aux catégories de charms', 'app'),
        ])->setArgs([
            'public'            => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_in_menu'      => true,
            'rewrite'           => ['slug' => 'charms-categorie', 'hierarchical' => true, 'with_front' => false],
            'sort'              => true,
        ])->setPostTypes(['app_charm']);
    }
}
