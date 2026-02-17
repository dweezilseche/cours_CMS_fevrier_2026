<?php
defined('ABSPATH') || exit;

$context = Timber\Timber::context();

Timber\Timber::render('pages/checkout.twig', $context);
