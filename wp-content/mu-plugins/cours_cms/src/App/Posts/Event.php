<?php

namespace App\Posts;

defined('ABSPATH') || exit;

class Event extends Post
{
    public function getEventDate(): string
    {
        $date = get_field('event_date', $this->ID);
        return $date ? date_i18n('d F Y', strtotime($date)) : '';
    }

    public function getEventTime(): string
    {
        return (string) get_field('event_time', $this->ID);
    }

    /**
     * @return array{address?: string, city?: string, zip?: string, country?: string}
     */
    public function getEventLocation(): array
    {
        return [
            'address' => get_field('event_address', $this->ID),
            'city'    => get_field('event_city', $this->ID),
            'zip'     => get_field('event_zip', $this->ID),
            'country' => get_field('event_country', $this->ID),
        ];
    }

    public function isPast(): bool
    {
        $event_date = get_field('event_date', $this->ID);
        return $event_date ? strtotime($event_date) < time() : false;
    }

    public function isUpcoming(): bool
    {
        return !$this->isPast();
    }

    public function getRegistrationLink(): ?string
    {
        return get_field('event_registration_link', $this->ID);
    }

    public function isRegistrationOpen(): bool
    {
        $is_open = get_field('event_registration_open', $this->ID);
        return $is_open && $this->isUpcoming();
    }
}
