<?php

namespace App\Acf;

defined('ABSPATH') || exit;

/**
 * Agrégateur des champs ACF globaux (options) pour le contexte Timber.
 */
class GlobalFields
{
    private static ?array $cache = null;

    public static function get(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }
        if (!function_exists('get_field')) {
            self::$cache = self::defaultStructure();
            return self::$cache;
        }
        self::$cache = [
            'header' => self::getOptionGroup('header'),
            'footer' => self::getOptionGroup('footer'),
            'seo'    => self::getOptionGroup('seo'),
            'social' => self::getOptionGroup('social'),
        ];
        return self::$cache;
    }

    private static function getOptionGroup(string $groupName): array
    {
        $value = get_field($groupName, 'option');
        return is_array($value) ? $value : [];
    }

    private static function defaultStructure(): array
    {
        return ['header' => [], 'footer' => [], 'seo' => [], 'social' => []];
    }

    public static function resetCache(): void
    {
        self::$cache = null;
    }
}
