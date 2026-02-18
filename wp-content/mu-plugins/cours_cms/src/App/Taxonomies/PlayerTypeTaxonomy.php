<?php

namespace App\Taxonomies;

use Wkn\Taxonomy\TaxonomyAbstract;

defined('ABSPATH') || exit;

class PlayerTypeTaxonomy extends TaxonomyAbstract
{
    protected $_name = 'app_player_taxonomy';

    public function __construct()
    {
        parent::__construct();
        $this->setLabels([
            'name'              => __('Catégories de joueurs', 'app'),
            'singular_name'     => __('Catégorie de joueur', 'app'),
            'menu_name'         => __('Catégories de joueurs', 'app'),
            'all_items'         => __('Toutes les catégories de joueurs', 'app'),
            'edit_item'         => __('Modifier la catégorie', 'app'),
            'view_item'         => __('Voir la catégorie de joueur', 'app'),
            'update_item'       => __('Mettre à jour la catégorie de joueur', 'app'),
            'add_new_item'      => __('Ajouter une nouvelle catégorie de joueur', 'app'),
            'new_item_name'     => __('Nom de la nouvelle catégorie de joueur', 'app'),
            'search_items'      => __('Rechercher une catégorie de joueur', 'app'),
            'popular_items'     => __('Catégories de joueurs populaires', 'app'),
            'back_to_items'     => __('Retour aux catégories de joueurs', 'app'),
        ])->setArgs([
            'public'            => true,
            'show_ui'           => true,
            'show_in_rest'      => true,
            'hierarchical'      => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_in_menu'      => true,
            'rewrite'           => ['slug' => 'players-categorie', 'hierarchical' => true, 'with_front' => false],
            'sort'              => true,
        ])->setPostTypes(['app_player']);
    }
}
