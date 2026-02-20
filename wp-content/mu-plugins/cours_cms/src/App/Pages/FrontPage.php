<?php

namespace App\Pages;

use Timber\Timber;

defined('ABSPATH') || exit;

class FrontPage extends Page
{
    /**
     * Récupère tous les subscribers avec leurs infos ACF (avatar + points).
     * Retourne un tableau d'utilisateurs triés par points décroissants.
     */
    public function getSubscribersWithInfos(): array
    {
        $subscribers = [];
        
        // Récupérer tous les utilisateurs avec le rôle subscriber ou customer
        $users = get_users([
            'role__in' => ['subscriber', 'customer'],
            'orderby' => 'registered',
            'order' => 'DESC',
        ]);
        
        if (!empty($users)) {
            foreach ($users as $user) {
                // Nettoyer le cache de cet utilisateur pour s'assurer d'avoir les données fraîches
                wp_cache_delete($user->ID, 'user_meta');
                
                // Récupérer les champs ACF (avec cache désactivé implicitement)
                $infos = get_field('infos', 'user_' . $user->ID);
                
                $subscriber_data = [
                    'id' => $user->ID,
                    'username' => $user->user_login,
                    'display_name' => $user->display_name,
                    'email' => $user->user_email,
                    'registered' => $user->user_registered,
                    'avatar' => null,
                    'points' => 0,
                ];
                
                // Récupérer les infos du groupe field
                if ($infos) {
                    if (isset($infos['avatar']) && !empty($infos['avatar'])) {
                        $subscriber_data['avatar'] = $infos['avatar'];
                    }
                    if (isset($infos['points']) && is_numeric($infos['points'])) {
                        $subscriber_data['points'] = (int) $infos['points'];
                    }
                }
                
                $subscribers[] = $subscriber_data;
            }
        }
        
        // Trier par points décroissants
        usort($subscribers, function($a, $b) {
            return $b['points'] - $a['points'];
        });
        
        return $subscribers;
    }
    
    /**
     * Expose les subscribers avec leurs infos directement dans le contexte Twig
     */
    public function subscribers(): array
    {
        return $this->getSubscribersWithInfos();
    }

    /**
     * Récupère les 3 prochains événements à venir
     */
    public function getUpcomingEvents(int $limit = 3): array
    {
        $args = [
            'post_type' => 'tribe_events',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'meta_value',
            'order' => 'ASC',
            'meta_key' => '_EventStartDate',
            'meta_query' => [
                [
                    'key' => '_EventStartDate',
                    'value' => current_time('Y-m-d H:i:s'),
                    'compare' => '>=',
                    'type' => 'DATETIME'
                ]
            ]
        ];
        
        $events = Timber::get_posts($args);
        return is_array($events) ? $events : (method_exists($events, 'to_array') ? $events->to_array() : iterator_to_array($events));
    }
    
    /**
     * Expose les événements à venir directement dans le contexte Twig
     */
    public function upcoming_events(): array
    {
        return $this->getUpcomingEvents(3);
    }

    /**
     * Récupère les derniers articles publiés
     */
    public function getLatestPosts(int $limit = 3): array
    {
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        $posts = Timber::get_posts($args);
        return is_array($posts) ? $posts : (method_exists($posts, 'to_array') ? $posts->to_array() : iterator_to_array($posts));
    }

    /**
     * Expose les derniers articles directement dans le contexte Twig
     */
    public function latest_posts(): array
    {
        return $this->getLatestPosts(3);
    }


    /**
     * Retourne le rang (1-based) de l'utilisateur connecté dans le classement, ou null.
     */
    public function current_user_rank(): ?int
    {
        if (!is_user_logged_in()) {
            return null;
        }
        $subscribers = $this->getSubscribersWithInfos();
        $current_id = get_current_user_id();
        foreach ($subscribers as $index => $subscriber) {
            if ((int) $subscriber['id'] === $current_id) {
                return $index + 1;
            }
        }
        return null;
    }

    /**
     * Retourne toutes les infos utiles de l'utilisateur connecté.
     */
    public function current_user(): ?array
    {
        if (!is_user_logged_in()) {
            return null;
        }

        $user = wp_get_current_user();

        if (!$user || empty($user->ID)) {
            return null;
        }

        $infos = get_field('infos', 'user_' . $user->ID);

        return [
            'id'           => $user->ID,
            'username'     => $user->user_login,
            'display_name' => $user->display_name,
            'email'        => $user->user_email,
            'roles'        => $user->roles,
            'registered'   => $user->user_registered,

            'points' => (is_array($infos) && isset($infos['points']) && is_numeric($infos['points']))
                ? (int) $infos['points']
                : 0,

            'avatar_acf' => (is_array($infos) && !empty($infos['avatar']))
                ? $infos['avatar']
                : null,

            // Fallback avatar WordPress (Gravatar)
            'avatar_wp' => get_avatar_url($user->ID),
        ];
    }
}
