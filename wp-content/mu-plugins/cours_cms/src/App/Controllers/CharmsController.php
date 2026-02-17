<?php

namespace App\Controllers;

use App\PostsTypes\CharmsPostType;
use App\Taxonomies\CharmsTypeTaxonomy;

defined('ABSPATH') || exit;

class CharmsController
{
    private static ?CharmsPostType $_post_type = null;
    private static ?CharmsTypeTaxonomy $_taxonomy = null;

    public static function init(): void
    {
        add_action('init', [self::class, '_registerTaxonomy'], 0);
        add_action('init', [self::class, '_registerPostType'], 0);
    }

    public static function _registerPostType(): void
    {
        self::$_post_type = new CharmsPostType();
        self::$_post_type->register();
    }

    public static function _registerTaxonomy(): void
    {
        self::$_taxonomy = new CharmsTypeTaxonomy();
        self::$_taxonomy->register();
    }

    public static function getPostType(): ?CharmsPostType
    {
        return self::$_post_type;
    }

    public static function getTaxonomyName(): string
    {
        return self::$_taxonomy ? self::$_taxonomy->getName() : 'app_charm_taxonomy';
    }
}
