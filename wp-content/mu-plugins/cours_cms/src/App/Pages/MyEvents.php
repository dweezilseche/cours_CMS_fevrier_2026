<?php

namespace App\Pages;

defined('ABSPATH') || exit;

class MyEvents extends Page
{
    /**
     * Indique si l'utilisateur est connecté.
     */
    public function isUserLoggedIn(): bool
    {
        return is_user_logged_in();
    }

    /**
     * Récupère les événements auxquels l'utilisateur connecté est inscrit.
     * Retourne un tableau d'événements Timber.
     */
    public function getUserEvents(): array
    {
        if (!is_user_logged_in()) {
            return [];
        }

        $user_events_data = class_exists(\App\Theme::class)
            ? \App\Theme::get_current_user_events_data()
            : [];

        if (empty($user_events_data)) {
            return [];
        }

        $event_ids = array_unique(array_column($user_events_data, 'event_id'));

        $events_query = [
            'post_type'      => 'tribe_events',
            'post__in'       => $event_ids,
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
            'meta_key'       => '_EventStartDate',
        ];

        return \Timber\Timber::get_posts($events_query);
    }

    /**
     * Récupère les données brutes des événements de l'utilisateur.
     * Utilisé pour avoir accès aux attendee_id.
     */
    public function getUserEventsData(): array
    {
        if (!is_user_logged_in()) {
            return [];
        }

        return class_exists(\App\Theme::class)
            ? \App\Theme::get_current_user_events_data()
            : [];
    }

    /**
     * Crée un tableau associatif event_id => attendee_id
     */
    public function getAttendeeByEvent(): array
    {
        $user_events_data = $this->getUserEventsData();
        $attendee_by_event = [];

        foreach ($user_events_data as $row) {
            if (!empty($row['attendee_id'])) {
                $attendee_by_event[(int) $row['event_id']] = (int) $row['attendee_id'];
            }
        }

        return $attendee_by_event;
    }

    /**
     * Génère les URLs de désinscription pour chaque événement
     */
    public function getUnregisterUrls(): array
    {
        $attendee_by_event = $this->getAttendeeByEvent();
        $my_events_url = get_permalink();
        $unregister_urls = [];

        foreach ($attendee_by_event as $event_id => $attendee_id) {
            $unregister_urls[$event_id] = wp_nonce_url(
                add_query_arg([
                    'action'       => 'unregister_event',
                    'attendee_id'  => $attendee_id,
                    'redirect_to'  => $my_events_url,
                ], admin_url('admin.php')),
                'unregister_event_' . $attendee_id
            );
        }

        return $unregister_urls;
    }

    /**
     * Récupère le message à afficher (succès, erreur, etc.)
     */
    public function getMessage(): string
    {
        return isset($_GET['message']) ? sanitize_text_field($_GET['message']) : '';
    }

    /**
     * Vérifie si un message doit être affiché
     */
    public function hasMessage(): bool
    {
        return !empty($this->getMessage());
    }

    /**
     * Retourne le type de message (success, error, info)
     */
    public function getMessageType(): string
    {
        $message = $this->getMessage();
        
        if ($message === 'unregistered') {
            return 'success';
        } elseif ($message === 'error') {
            return 'error';
        }
        
        return 'info';
    }

    /**
     * Retourne le texte du message formaté
     */
    public function getMessageText(): string
    {
        $message = $this->getMessage();
        
        if ($message === 'unregistered') {
            return __('Vous avez été désinscrit de l\'événement avec succès.', 'app');
        } elseif ($message === 'error') {
            return __('Une erreur s\'est produite lors de la désinscription.', 'app');
        }
        
        return '';
    }

    /**
     * Compte le nombre d'événements auxquels l'utilisateur est inscrit
     */
    public function getEventsCount(): int
    {
        return count($this->getUserEvents());
    }

    /**
     * Vérifie si l'utilisateur est inscrit à au moins un événement
     */
    public function hasEvents(): bool
    {
        return $this->getEventsCount() > 0;
    }
}
