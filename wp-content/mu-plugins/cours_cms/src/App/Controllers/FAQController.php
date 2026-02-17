<?php

namespace App\Controllers;

use App\PostsTypes\FAQPostType;
use App\Taxonomies\FAQTypeTaxonomy;

defined('ABSPATH') || exit;

class FAQController
{
    private static ?FAQPostType $_post_type = null;
    private static ?FAQTypeTaxonomy $_taxonomy = null;

    public static function init(): void
    {
        add_action('init', [self::class, '_registerTaxonomy'], 0);
        add_action('init', [self::class, '_registerPostType'], 0);
    }

    public static function _registerPostType(): void
    {
        self::$_post_type = new FAQPostType();
        self::$_post_type->register();
    }

    public static function _registerTaxonomy(): void
    {
        self::$_taxonomy = new FAQTypeTaxonomy();
        self::$_taxonomy->register();
    }

    public static function getPostType(): ?FAQPostType
    {
        return self::$_post_type;
    }

    public static function getTaxonomyName(): string
    {
        return self::$_taxonomy ? self::$_taxonomy->getName() : 'app_faq_taxonomy';
    }
}
