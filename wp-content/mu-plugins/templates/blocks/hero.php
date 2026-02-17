<?php
/**
 * Template de bloc ACF : Hero
 * 
 * Ce fichier est appelé automatiquement par ACF pour rendre le bloc Hero.
 * Il prépare les données et utilise Timber pour le rendu.
 */

use Timber\Timber;

// Récupération de l'ID du bloc
$block_id = 'hero-' . $block['id'];
if (!empty($block['anchor'])) {
    $block_id = $block['anchor'];
}

// Récupération des classes
$classes = 'block-hero';
if (!empty($block['className'])) {
    $classes .= ' ' . $block['className'];
}
if (!empty($block['align'])) {
    $classes .= ' align' . $block['align'];
}

// Préparation du contexte pour Twig
$context = Timber::context();

$context['block'] = [
    'id' => $block_id,
    'classes' => $classes,
    'title' => get_field('hero_title'),
    'subtitle' => get_field('hero_subtitle'),
    'content' => get_field('hero_content'),
    'image' => get_field('hero_image'),
    'cta' => [
        'text' => get_field('hero_cta_text'),
        'link' => get_field('hero_cta_link'),
        'style' => get_field('hero_cta_style'), // primary, secondary, outline
    ],
    'height' => get_field('hero_height'), // small, medium, large, full
    'overlay' => get_field('hero_overlay'), // dark, light, none
];

// Mode Preview dans l'éditeur
$context['is_preview'] = $is_preview ?? false;

// Rendu du template Twig
Timber::render('blocks/hero.twig', $context);
