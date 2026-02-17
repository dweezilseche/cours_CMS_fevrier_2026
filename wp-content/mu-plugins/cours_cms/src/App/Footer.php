<?php

namespace App;

use Twig\TwigFunction;
use Wkn\Controller\ControllerAbstract;

defined('ABSPATH') || exit;

class Footer extends ControllerAbstract
{
    /** @var array|null Cache des champs footer */
    private static ?array $_fields = null;

    public static function init(): void
    {
        if (function_exists('acf_add_options_sub_page')) {
            acf_add_options_sub_page([
                'page_title' => __('Configuration Footer', 'app'),
                'menu_title' => __('Footer', 'app'),
                'parent_slug'=> 'mon-site-settings',
                'post_id'    => 'footer',
            ]);
        }
        add_filter('timber/twig', [self::class, 'add_to_twig']);
        add_filter('timber/context', [self::class, 'addToContext']);
    }

    public static function addToContext(array $context): array
    {
        $fields = self::getFields();
        $context['footer'] = is_array($fields['footer'] ?? null) ? $fields['footer'] : [];
        return $context;
    }

    public static function add_to_twig($twig): \Twig\Environment
    {
        $twig->addFunction(new TwigFunction('footer', [static::class, 'getField']));
        return $twig;
    }

    public static function getFields(): array
    {
        if (self::$_fields === null && function_exists('get_fields')) {
            self::$_fields = get_fields('footer') ?: [];
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
