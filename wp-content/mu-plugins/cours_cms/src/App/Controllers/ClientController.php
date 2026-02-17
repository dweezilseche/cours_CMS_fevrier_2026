<?php

namespace App\Controllers;

use Timber\Timber;

defined('ABSPATH') || exit;

class ClientController
{
    public static function init(): void
    {
        add_action('wp_ajax_get_clients', [self::class, 'getClients']);
        add_action('wp_ajax_nopriv_get_clients', [self::class, 'getClients']);
        add_shortcode('clients_list', [self::class, 'renderClientsList']);
    }

    public static function getClients(): void
    {
        if (!check_ajax_referer('clients_nonce', 'nonce', false)) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }
        $page = isset($_POST['page']) ? (int) $_POST['page'] : 1;
        $per_page = isset($_POST['per_page']) ? (int) $_POST['per_page'] : 10;
        $clients = Timber::get_posts([
            'post_type'      => 'app_client',
            'posts_per_page' => $per_page,
            'paged'          => $page,
        ]);
        $data = array_map(static function ($client) {
            return [
                'id'        => $client->ID,
                'title'     => $client->title(),
                'excerpt'   => $client->excerpt(),
                'link'      => $client->link(),
                'thumbnail' => $client->thumbnail() ? $client->thumbnail()->src() : '',
            ];
        }, $clients);
        wp_send_json_success([
            'clients' => $data,
            'total'   => wp_count_posts('app_client')->publish ?? 0,
        ]);
    }

    /**
     * @param array<string, string> $atts
     */
    public static function renderClientsList($atts): string
    {
        $atts = shortcode_atts(['count' => 6, 'category' => ''], $atts);
        $args = [
            'post_type'      => 'app_client',
            'posts_per_page' => (int) $atts['count'],
        ];
        if (!empty($atts['category'])) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'app_client_category',
                    'field'    => 'slug',
                    'terms'    => $atts['category'],
                ],
            ];
        }
        $clients = Timber::get_posts($args);
        return Timber::compile('components/clients-list.twig', ['clients' => $clients]);
    }
}
