<?php

namespace App\Controllers;

use App\PostsTypes\PlayerPostType;
use App\Taxonomies\PlayerTypeTaxonomy;

defined('ABSPATH') || exit;

class PlayerController
{
    private static ?PlayerPostType $_post_type = null;
    private static ?PlayerTypeTaxonomy $_taxonomy = null;

    public static function init(): void
    {
        add_action('init', [self::class, '_registerTaxonomy'], 0);
        add_action('init', [self::class, '_registerPostType'], 0);
    }

    public static function _registerPostType(): void
    {
        self::$_post_type = new PlayerPostType();
        self::$_post_type->register();
    }

    public static function _registerTaxonomy(): void
    {
        self::$_taxonomy = new PlayerTypeTaxonomy();
        self::$_taxonomy->register();
    }

    public static function getPostType(): ?PlayerPostType
    {
        return self::$_post_type;
    }

    public static function getTaxonomyName(): string
    {
        return self::$_taxonomy ? self::$_taxonomy->getName() : 'app_player_taxonomy';
    }
}
