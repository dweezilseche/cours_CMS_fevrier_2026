<?php

namespace App\Posts;

defined('ABSPATH') || exit;

class Event extends Post
{
    /**
     * Helpers
     */
    protected function fnExists(string $fn): bool
    {
        return function_exists($fn);
    }

    protected function toString(mixed $value): string
    {
        return is_string($value) ? $value : '';
    }

    protected function toInt(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    protected function toBool(mixed $value): bool
    {
        return (bool) $value;
    }

    /**
     * Generic safe call to a function that should return string.
     */
    protected function callString(string $fn, array $args = []): string
    {
        if (!$this->fnExists($fn)) {
            return '';
        }
        $value = $fn(...$args);
        return $this->toString($value);
    }

    /**
     * Generic safe call to a function that should return bool.
     */
    protected function callBool(string $fn, array $args = []): bool
    {
        if (!$this->fnExists($fn)) {
            return false;
        }
        return $this->toBool($fn(...$args));
    }

    /**
     * ---------- Dates / Times ----------
     */
    /**
     * Compatible avec Timber\Post::date(). Affiche la date de début de l’événement.
     *
     * @param string|null $date_format Format PHP (d F Y par défaut).
     */
    public function date($date_format = null): string
    {
        $format = $date_format !== null ? $date_format : 'd F Y';
        return $this->getStartDate($format, false);
    }

    public function getStartDate(string $format = 'd F Y', bool $withTime = false): string
    {
        return $this->callString('tribe_get_start_date', [$this->ID, false, $format, $withTime]);
    }

    public function getEndDate(string $format = 'd F Y', bool $withTime = false): string
    {
        return $this->callString('tribe_get_end_date', [$this->ID, false, $format, $withTime]);
    }

    public function getStartTime(?string $format = null): string
    {
        return $this->callString('tribe_get_start_time', [$this->ID, $format]);
    }

    public function getEndTime(?string $format = null): string
    {
        return $this->callString('tribe_get_end_time', [$this->ID, $format]);
    }

    public function isAllDay(): bool
    {
        return $this->callBool('tribe_event_is_all_day', [$this->ID]);
    }

    public function isMultiday(): bool
    {
        return $this->callBool('tribe_event_is_multiday', [$this->ID]);
    }

    public function getTimezoneString(): string
    {
        if ($this->fnExists('tribe_get_event_timezone_string')) {
            return $this->callString('tribe_get_event_timezone_string', [$this->ID]);
        }
        return $this->toString(wp_timezone_string());
    }

    public function getStartDateTimeIso(): string
    {
        return $this->toString(get_post_meta($this->ID, '_EventStartDate', true));
    }

    public function getEndDateTimeIso(): string
    {
        return $this->toString(get_post_meta($this->ID, '_EventEndDate', true));
    }

    /**
     * ---------- Venue ----------
     */
    public function hasVenue(): bool
    {
        return $this->callBool('tribe_has_venue', [$this->ID]);
    }

    public function getVenueId(): int
    {
        if (!$this->fnExists('tribe_get_venue_id')) {
            return 0;
        }
        return $this->toInt(tribe_get_venue_id($this->ID));
    }

    public function localisation(): string
    {
        return $this->callString('tribe_get_venue', [$this->ID]);
    }

    public function address(): string
    {
        return $this->callString('tribe_get_address', [$this->ID]);
    }

    public function city(): string
    {
        return $this->callString('tribe_get_city', [$this->ID]);
    }

    public function zip(): string
    {
        return $this->callString('tribe_get_zip', [$this->ID]);
    }

    public function country(): string
    {
        return $this->callString('tribe_get_country', [$this->ID]);
    }

    public function province(): string
    {
        return $this->callString('tribe_get_stateprovince', [$this->ID]);
    }

    public function phone(): string
    {
        return $this->callString('tribe_get_phone', [$this->ID]);
    }

    public function website(): string
    {
        return $this->callString('tribe_get_venue_website_url', [$this->ID]);
    }

    public function maps_link(): string
    {
        return $this->callString('tribe_get_map_link', [$this->ID]);
    }

    /**
     * ---------- Organizer ----------
     */
    public function hasOrganizer(): bool
    {
        return $this->callBool('tribe_has_organizer', [$this->ID]);
    }

    public function getOrganizerId(): int
    {
        if (!$this->fnExists('tribe_get_organizer_id')) {
            return 0;
        }
        return $this->toInt(tribe_get_organizer_id($this->ID));
    }

    public function getOrganizerPhone(): string
    {
        return $this->callString('tribe_get_organizer_phone', [$this->ID]);
    }

    public function getOrganizerEmail(): string
    {
        return $this->callString('tribe_get_organizer_email', [$this->ID]);
    }

    public function getOrganizerWebsite(): string
    {
        return $this->callString('tribe_get_organizer_website_url', [$this->ID]);
    }

    /**
     * ---------- Cost ----------
     */
    public function getCost(): string
    {
        return $this->callString('tribe_get_cost', [$this->ID]);
    }

    public function formatted_cost(): string
    {
        return $this->callString('tribe_get_formatted_cost', [$this->ID]);
    }

    /**
     * ---------- Links ----------
     */
    public function getWebsiteUrl(): string
    {
        return $this->callString('tribe_get_event_website_url', [$this->ID]);
    }

    public function getIcalLink(): string
    {
        return $this->callString('tribe_get_ical_link');
    }

    public function getGcalLink(): string
    {
        return $this->callString('tribe_get_gcal_link');
    }

    public function getPrevEventLink(): string
    {
        if (!$this->fnExists('tribe_the_prev_event_link')) {
            return '';
        }
        ob_start();
        tribe_the_prev_event_link('%title%');
        return (string) ob_get_clean();
    }

    public function getNextEventLink(): string
    {
        if (!$this->fnExists('tribe_the_next_event_link')) {
            return '';
        }
        ob_start();
        tribe_the_next_event_link('%title%');
        return (string) ob_get_clean();
    }

    /**
     * ---------- Categories ----------
     */
    public function getCategories(): array
    {
        $terms = get_the_terms($this->ID, 'tribe_events_cat');
        if (!is_array($terms)) {
            return [];
        }

        return array_map(static function ($t) {
            return [
                'id'   => (int) $t->term_id,
                'name' => (string) $t->name,
                'slug' => (string) $t->slug,
                'url'  => (string) get_term_link($t),
            ];
        }, $terms);
    }

    /**
     * ---------- Tickets / Attendees ----------
     * IMPORTANT: tribe_tickets() returns a Ticket_Repository in your setup.
     */
    protected function ticketsRepo(): ?object
    {
        if (!$this->fnExists('tribe_tickets')) {
            return null;
        }
        $repo = tribe_tickets();
        return is_object($repo) ? $repo : null;
    }

    /**
     * Returns an array of tickets for the event using repository API.
     */
    protected function ticketsForEvent(): array
    {
        // Méthode principale: utiliser la classe Tribe__Tickets__Tickets
        if (class_exists('Tribe__Tickets__Tickets')) {
            $tickets = \Tribe__Tickets__Tickets::get_all_event_tickets($this->ID);
            if (is_array($tickets) && !empty($tickets)) {
                return $tickets;
            }
        }
        
        // Fallback: fonction helper si disponible
        if (function_exists('tribe_tickets_get_all_event_tickets')) {
            $tickets = tribe_tickets_get_all_event_tickets($this->ID);
            if (is_array($tickets) && !empty($tickets)) {
                return $tickets;
            }
        }
        
        // Fallback sur le repository
        $repo = $this->ticketsRepo();
        if (!$repo) {
            return [];
        }

        // Common repository pattern in TEC stack
        if (method_exists($repo, 'where') && method_exists($repo, 'get')) {
            $tickets = $repo->where('event', $this->ID)->get();
            return is_array($tickets) ? $tickets : [];
        }

        if (method_exists($repo, 'by') && method_exists($repo, 'get')) {
            $tickets = $repo->by('event', $this->ID)->get();
            return is_array($tickets) ? $tickets : [];
        }

        return [];
    }

    public function has_ticket(): bool
    {
        return count($this->ticketsForEvent()) > 0;
    }

    public function tickets_count(): int
    {
        return count($this->ticketsForEvent());
    }

    /**
     * Normalized tickets array for Twig
     */
    public function ticket(): array
    {
        $tickets = $this->ticketsForEvent();
        if (empty($tickets)) {
            return [];
        }

        return array_map(function ($ticket) {
            $id = 0;
            $name = '';
            $price = '';
            $stock = null;

            if (is_object($ticket)) {
                $id = isset($ticket->ID) ? (int) $ticket->ID : 0;

                if (isset($ticket->name)) {
                    $name = (string) $ticket->name;
                } elseif (isset($ticket->post_title)) {
                    $name = (string) $ticket->post_title;
                }

                if (isset($ticket->price)) {
                    $price = (string) $ticket->price;
                }

                if (isset($ticket->stock)) {
                    $stock = is_numeric($ticket->stock) ? (int) $ticket->stock : null;
                } elseif (method_exists($ticket, 'stock')) {
                    $maybe = $ticket->stock();
                    $stock = is_numeric($maybe) ? (int) $maybe : null;
                }
            }

            return [
                'id'    => $id,
                'name'  => $name,
                'price' => $price,
                'stock' => $stock, // null = unknown/illimité
            ];
        }, $tickets);
    }

    /**
     * Attendees / participants count (best effort).
     * Returns 0 if not available.
     */
    public function participants_count(): int
    {
        if (function_exists('tribe_tickets_get_event_attendees_count')) {
            return $this->toInt(tribe_tickets_get_event_attendees_count($this->ID));
        }

        $repo = $this->ticketsRepo();
        if ($repo && method_exists($repo, 'get_attendees_count')) {
            return $this->toInt($repo->get_attendees_count($this->ID));
        }

        return 0;
    }

    /**
     * Total capacity based on tickets stock.
     * Returns 0 when unknown/illimité.
     */
    public function getTotalCapacity(): int
    {
        $tickets = $this->ticketsForEvent();
        if (empty($tickets)) {
            return 0;
        }

        $total = 0;

        foreach ($tickets as $ticket) {
            $stock = null;

            if (is_object($ticket) && isset($ticket->stock)) {
                $stock = $ticket->stock;
            } elseif (is_object($ticket) && method_exists($ticket, 'stock')) {
                $stock = $ticket->stock();
            }

            // -1 / null = illimité / inconnu → capacité non calculable
            if ($stock === -1 || $stock === null) {
                return 0;
            }

            $total += $this->toInt($stock);
        }

        return $total;
    }

    /**
     * Remaining spots if capacity is known, else 0.
     */
    public function remaining_spots(): int
    {
        $capacity = $this->getTotalCapacity();
        if ($capacity <= 0) {
            return 0;
        }

        return max(0, $capacity - $this->participants_count());
    }

    /**
     * Days until booking ends.
     * Fallback: booking ends at event start datetime.
     * -1 = unknown, 0 = ended, >0 = days left
     */
    public function booking_end(): int
    {
        $startIso = $this->getStartDateTimeIso();
        if (!$startIso) {
            return -1;
        }

        $end = strtotime($startIso);
        if (!is_int($end) || $end <= 0) {
            return -1;
        }

        $diff = $end - time();

        if ($diff <= 0) {
            return 0;
        }

        return (int) ceil($diff / DAY_IN_SECONDS);
    }

    /**
     * Registration button/module HTML (Event Tickets)
     */
    public function getRegistrationHtml(): string
    {
        // Vérifier si Event Tickets est actif
        if (!class_exists('Tribe__Tickets__Tickets')) {
            return '';
        }

        // Capturer le rendu des tickets/RSVP
        ob_start();
        
        // Méthode 1: Utiliser le shortcode standard (le plus fiable)
        if (shortcode_exists('tribe_tickets')) {
            echo do_shortcode('[tribe_tickets post_id="' . $this->ID . '"]');
        }
        // Méthode 2: Appeler directement la fonction d'affichage
        elseif (function_exists('tribe_tickets_attendees_show_view')) {
            tribe_tickets_attendees_show_view('', $this->ID);
        }
        
        $output = ob_get_clean();
        
        // Si toujours vide, forcer l'affichage du template
        if (empty($output) && class_exists('Tribe__Tickets__Tickets_View')) {
            ob_start();
            $view = \Tribe__Tickets__Tickets_View::instance();
            $view->inject_link_template();
            $output = ob_get_clean();
        }
        
        return $output ? (string) $output : '';
    }

    /**
     * Human schedule string
     */
    public function getHumanSchedule(): string
    {
        $dateStart = $this->getStartDate('d F Y');
        $dateEnd   = $this->getEndDate('d F Y');

        if ($this->isMultiday() && $dateStart && $dateEnd) {
            if ($this->isAllDay()) {
                return sprintf('%s → %s (toute la journée)', $dateStart, $dateEnd);
            }

            $startTime = $this->getStartTime();
            $endTime   = $this->getEndTime();

            if ($startTime || $endTime) {
                return trim(sprintf('%s %s → %s %s', $dateStart, $startTime, $dateEnd, $endTime));
            }

            return sprintf('%s → %s', $dateStart, $dateEnd);
        }

        if ($this->isAllDay()) {
            return $dateStart ? sprintf('%s (toute la journée)', $dateStart) : '';
        }

        $timeStart = $this->getStartTime();
        $timeEnd   = $this->getEndTime();

        if ($dateStart && ($timeStart || $timeEnd)) {
            if ($timeStart && $timeEnd) {
                return sprintf('%s — %s → %s', $dateStart, $timeStart, $timeEnd);
            }
            if ($timeStart) {
                return sprintf('%s — %s', $dateStart, $timeStart);
            }
            return sprintf('%s — %s', $dateStart, $timeEnd);
        }

        return $dateStart;
    }

    public function calendar(): array
    {
        return [
            'start' => $this->getStartDateTimeIso(),
            'end'   => $this->getEndDateTimeIso(),
            'schedule' => $this->getHumanSchedule(),
        ];
    }
}
