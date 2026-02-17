<?php
/**
 * Template de bloc ACF : Slider
 */

use Timber\Timber;

$block_id = 'slider-' . $block['id'];
if (!empty($block['anchor'])) {
    $block_id = $block['anchor'];
}

$classes = 'block-slider';
if (!empty($block['className'])) {
    $classes .= ' ' . $block['className'];
}

$context = Timber::context();

$context['block'] = [
    'id' => $block_id,
    'classes' => $classes,
    'slides' => get_field('slider_slides'), // Gallery ACF
    'autoplay' => get_field('slider_autoplay'),
    'autoplay_speed' => get_field('slider_autoplay_speed'),
    'navigation' => get_field('slider_navigation'),
    'pagination' => get_field('slider_pagination'),
];

$context['is_preview'] = $is_preview ?? false;

Timber::render('blocks/slider.twig', $context);
