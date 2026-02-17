<?php

namespace App\Pages;

use Timber\Timber;

defined('ABSPATH') || exit;

class FrontPage extends Page
{
    /**
     * Charge automatiquement les champs ACF pour la page d'accueil
     */
    public function __construct($pid = null)
    {
        parent::__construct($pid);
        
        // Charger les champs ACF personnalisés
        if (function_exists('get_field')) {
            $this->hero = get_field('hero', $this->ID) ?: false;
        }
    }
}
