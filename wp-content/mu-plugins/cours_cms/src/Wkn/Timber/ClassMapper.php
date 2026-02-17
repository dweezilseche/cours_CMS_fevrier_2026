<?php

namespace Wkn\Timber;

defined('ABSPATH') || exit;

/**
 * Classe de base pour le mapping Timber (posts, terms, comments, menu, etc.).
 * Les sous-classes surchargent posts(), terms(), etc. et retournent des tableaux
 * fusionnés avec les classmaps par défaut de Timber.
 */
abstract class ClassMapper
{
    public static function init(): void
    {
        $instance = new static();

        add_filter('timber/post/classmap', static function (array $classmap) use ($instance): array {
            return array_merge($classmap, $instance->posts());
        });

        add_filter('timber/term/classmap', static function (array $classmap) use ($instance): array {
            return array_merge($classmap, $instance->terms());
        });

        add_filter('timber/comment/classmap', static function (array $classmap) use ($instance): array {
            return array_merge($classmap, $instance->comments());
        });

        add_filter('timber/menu/classmap', static function (array $classmap) use ($instance): array {
            return array_merge($classmap, $instance->menu());
        });

        add_filter('timber/menuitem/classmap', static function (array $classmap) use ($instance): array {
            return array_merge($classmap, $instance->menuitem());
        });

        add_filter('timber/user/classmap', static function (array $classmap) use ($instance): array {
            return array_merge($classmap, $instance->user());
        });
    }

    /** @return array<string, string|callable> */
    abstract public function posts(): array;

    /** @return array<string, string> */
    abstract public function terms(): array;

    /** @return array<string, string> */
    abstract public function comments(): array;

    /** @return array<string, string> */
    abstract public function menu(): array;

    /** @return array<string, string> */
    abstract public function menuitem(): array;

    /** @return array<string, string> */
    abstract public function user(): array;
}
