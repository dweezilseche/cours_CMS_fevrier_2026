<?php

namespace App\PostsTypes;

use Wkn\PostType\PostTypeAbstract;

defined('ABSPATH') || exit;

/**
 * Charm Request Post Type
 * Stocke les demandes du formulaire « Sur-mesure » (Nom, Prénom, Email, Téléphone, Message, Pièce jointe).
 *
 * @package App\PostsTypes
 * @since 1.0.0
 */
class CharmRequestPostType extends PostTypeAbstract
{
    public const NAME = 'app_charm_request';

    /** Meta keys pour les champs du formulaire */
    public const META_NOM = '_charm_request_nom';
    public const META_PRENOM = '_charm_request_prenom';
    public const META_EMAIL = '_charm_request_email';
    public const META_TELEPHONE = '_charm_request_telephone';
    public const META_MESSAGE = '_charm_request_message';
    public const META_ATTACHMENT_ID = '_charm_request_attachment_id';

    protected $_name = self::NAME;

    public function __construct()
    {
        parent::__construct();
        $this->setLabels([
            'name'               => __('Demandes Charms', 'app'),
            'singular_name'      => __('Demande Charm', 'app'),
            'menu_name'          => __('Demandes Charms', 'app'),
            'all_items'          => __('Toutes les demandes', 'app'),
            'add_new'            => __('Ajouter', 'app'),
            'add_new_item'       => __('Nouvelle demande', 'app'),
            'edit_item'          => __('Modifier la demande', 'app'),
            'view_item'          => __('Voir la demande', 'app'),
            'search_items'       => __('Rechercher une demande', 'app'),
            'not_found'          => __('Aucune demande trouvée', 'app'),
            'not_found_in_trash' => __('Aucune demande dans la corbeille', 'app'),
        ])->setArgs([
            'public'       => false,
            'show_ui'      => true,
            'show_in_menu' => true,
            'menu_icon'    => 'dashicons-email-alt',
            'supports'     => ['title', 'custom-fields'],
            'capability_type' => 'post',
        ]);
    }
}
