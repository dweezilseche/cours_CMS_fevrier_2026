<?php

namespace App;

use Twig\TwigFunction;
use Wkn\Controller\ControllerAbstract;

defined('ABSPATH') || exit;

class Configuration extends ControllerAbstract
{
    /** @var array|null Cache des champs options */
    private static ?array $_fields = null;

    public static function init(): void
    {
        if (function_exists('acf_add_options_page')) {
            acf_add_options_page([
                'page_title' => __('Configuration du site', 'app'),
                'menu_title' => __('Configuration', 'app'),
                'menu_slug'  => 'mon-site-settings',
                'capability' => 'edit_posts',
                'icon_url'   => 'dashicons-admin-settings',
                'position'   => 2,
                'redirect'   => false,
            ]);
        }
        add_filter('timber/twig', [self::class, 'add_to_twig']);
    }

    public static function add_to_twig($twig): \Twig\Environment
    {
        $twig->addFunction(new TwigFunction('config', [static::class, 'getField']));
        return $twig;
    }

    public static function getFields(): array
    {
        if (self::$_fields === null && function_exists('get_fields')) {
            self::$_fields = get_fields('options') ?: [];
        }
        return self::$_fields ?? [];
    }

    /**
     * @param string $field Clé du champ ACF
     * @return mixed Retourne '' si absent pour éviter trim(null) (PHP 8.1+)
     */
    public static function getField(string $field)
    {
        $fields = self::getFields();
        $value = $fields[$field] ?? null;
        return $value === null ? '' : $value;
    }
}
