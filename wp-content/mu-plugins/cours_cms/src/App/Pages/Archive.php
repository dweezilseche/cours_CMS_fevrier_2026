<?php

namespace App\Pages;

use Timber\Timber;

defined('ABSPATH') || exit;

class Archive extends Page
{
    protected ?string $post_type = null;
    /** @var array */
    protected $posts = [];

    public function __construct($pid = null)
    {
        parent::__construct($pid);
        $this->post_type = get_post_type();
        $this->posts = $this->getPosts();
    }

    public function getPosts(): array
    {
        global $wp_query;
        return Timber::get_posts($wp_query);
    }

    public function getPagination(): array
    {
        global $wp_query;
        return [
            'current' => max(1, (int) get_query_var('paged')),
            'total'   => $wp_query->max_num_pages ?? 0,
            'prev'    => get_previous_posts_page_link(),
            'next'    => get_next_posts_page_link(),
        ];
    }

    public function getArchiveTitle(): string
    {
        return post_type_archive_title('', false);
    }

    public function getArchiveDescription(): string
    {
        return get_the_archive_description();
    }

    public function getFilters(): array
    {
        $post_type = $this->post_type ?: get_post_type();
        $taxonomies = get_object_taxonomies($post_type, 'objects');
        $filters = [];
        foreach ($taxonomies as $taxonomy) {
            $terms = get_terms(['taxonomy' => $taxonomy->name, 'hide_empty' => true]);
            if (!empty($terms) && !is_wp_error($terms)) {
                $filters[$taxonomy->name] = ['label' => $taxonomy->label, 'terms' => $terms];
            }
        }
        return $filters;
    }
}
