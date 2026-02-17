<?php
/**
 * Template de bloc ACF : CTA (Call to Action)
 */

use Timber\Timber;

$block_id = 'cta-' . $block['id'];
if (!empty($block['anchor'])) {
    $block_id = $block['anchor'];
}

$classes = 'block-cta';
if (!empty($block['className'])) {
    $classes .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $classes .= ' align' . $block['align'];
}

$context = Timber::context();

$context['block'] = [
    'id' => $block_id,
    'classes' => $classes,
    'title' => get_field('cta_title'),
    'content' => get_field('cta_content'),
    'buttons' => get_field('cta_buttons'), // Repeater ACF
    'background_color' => get_field('cta_background_color'),
    'text_align' => get_field('cta_text_align'), // left, center, right
];

$context['is_preview'] = $is_preview ?? false;

Timber::render('blocks/cta.twig', $context);
