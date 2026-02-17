<?php

namespace Wkn\Controller;

defined('ABSPATH') || exit;

/**
 * Classe de base pour les contrôleurs (options ACF, réglages, etc.)
 */
abstract class ControllerAbstract
{
    /**
     * Initialisation : enregistrement des hooks.
     */
    abstract public static function init(): void;
}
