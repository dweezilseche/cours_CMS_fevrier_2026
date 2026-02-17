<?php

namespace App\Posts;

use Timber\Post as TimberPost;
use Timber\Timber;

defined('ABSPATH') || exit;

class Post extends TimberPost
{
    public function getFormattedDate(string $format = 'd F Y'): string
    {
        return date_i18n($format, strtotime($this->post_date));
    }

    /**
     * @return \Timber\Post[]
     */
    public function getRelatedPosts(int $count = 3): array
    {
        $terms = wp_get_post_terms($this->ID, get_object_taxonomies($this->post_type));
        if (empty($terms) || is_wp_error($terms)) {
            return [];
        }
        $term_ids = array_map(static fn ($t) => $t->term_id, $terms);
        return Timber::get_posts([
            'post_type'      => $this->post_type,
            'posts_per_page' => $count,
            'post__not_in'   => [$this->ID],
            'tax_query'      => [
                [
                    'taxonomy' => $terms[0]->taxonomy,
                    'field'    => 'term_id',
                    'terms'    => $term_ids,
                ],
            ],
        ]);
    }

    public function getReadingTime(): int
    {
        $word_count = str_word_count(strip_tags($this->post_content ?? ''));
        return max(1, (int) ceil($word_count / 200));
    }

    public function isRecent(): bool
    {
        return strtotime($this->post_date) > strtotime('-7 days');
    }

    public function getShareUrl(string $network = 'facebook'): string
    {
        $url = urlencode($this->link());
        $title = urlencode($this->title());
        $share_urls = [
            'facebook' => "https://www.facebook.com/sharer/sharer.php?u={$url}",
            'twitter'  => "https://twitter.com/intent/tweet?url={$url}&text={$title}",
            'linkedin' => "https://www.linkedin.com/sharing/share-offsite/?url={$url}",
            'email'    => "mailto:?subject={$title}&body={$url}",
        ];
        return $share_urls[$network] ?? '#';
    }
}
