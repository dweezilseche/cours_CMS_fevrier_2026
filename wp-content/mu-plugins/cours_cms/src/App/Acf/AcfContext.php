<?php

namespace App\Acf;

defined('ABSPATH') || exit;

/**
 * Injection des champs ACF du post courant dans le contexte Timber.
 */
class AcfContext
{
    public static function init(): void
    {
        add_filter('timber/context', [self::class, 'injectPostFields'], 15);
    }

    public static function injectPostFields(array $context): array
    {
        if (!function_exists('get_fields')) {
            return $context;
        }
        $post_id = get_queried_object_id();
        if (!$post_id || !is_singular()) {
            return $context;
        }
        $fields = get_fields($post_id);
        if (!is_array($fields)) {
            return $context;
        }
        foreach ($fields as $key => $value) {
            if (!isset($context[$key])) {
                $context[$key] = $value;
            }
        }
        return $context;
    }
}
