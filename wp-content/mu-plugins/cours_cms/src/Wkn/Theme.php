<?php

namespace Wkn;

use Timber\Timber;

defined('ABSPATH') || exit;

/**
 * Classe de base du thème (support, menus, contexte Timber).
 */
class Theme
{
    /**
     * Initialisation : theme_support, hooks admin, Timber, etc.
     */
    public static function init(): void
    {
        Timber::init();
    }
}
