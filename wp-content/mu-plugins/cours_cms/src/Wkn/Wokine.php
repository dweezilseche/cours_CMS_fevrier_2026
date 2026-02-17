<?php

namespace Wkn;

defined('ABSPATH') || exit;

/**
 * Point d'entrée du framework Wokine.
 * Initialise les classes listées en appelant leur méthode init().
 */
class Wokine
{
    /**
     * @param array<int, class-string> $classes Liste de FQCN avec méthode init()
     */
    public static function init(array $classes): void
    {
        foreach ($classes as $class) {
            if (is_string($class) && class_exists($class) && method_exists($class, 'init')) {
                $class::init();
            }
        }
    }
}
