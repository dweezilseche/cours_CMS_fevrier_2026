<?php

namespace App\Pages;

use Timber\Post as TimberPost;

defined('ABSPATH') || exit;

class Page extends TimberPost
{
    public function getBlocks(): array
    {
        return parse_blocks($this->post_content ?? '');
    }

    public function hasTemplate(string $template): bool
    {
        return $this->meta('_wp_page_template') === $template;
    }

    public function getSeoTitle(): string
    {
        $yoast_title = $this->meta('_yoast_wpseo_title');
        return $yoast_title ?: $this->title();
    }

    public function getSeoDescription(): string
    {
        $yoast_desc = $this->meta('_yoast_wpseo_metadesc');
        return $yoast_desc ?: $this->excerpt(['words' => 30]);
    }
}
