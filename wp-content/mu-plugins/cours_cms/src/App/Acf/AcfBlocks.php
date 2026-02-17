<?php

namespace App\Acf;

use Timber\Timber;
use Wkn\Controller\ControllerAbstract;

defined('ABSPATH') || exit;

class AcfBlocks extends ControllerAbstract
{
    public static function init(): void
    {
        add_action('acf/init', [self::class, 'register_acf_blocks']);
        add_action('wp_ajax_save_post', [self::class, 'log_save_errors'], 1);
        add_action('wp_ajax_nopriv_save_post', [self::class, 'log_save_errors'], 1);
    }

    public static function register_acf_blocks(): void
    {
        $blocks_dir = get_template_directory() . '/views/blocks';
        if (!is_dir($blocks_dir)) {
            return;
        }
        foreach (new \DirectoryIterator($blocks_dir) as $entry) {
            if ($entry->isDot() || !$entry->isDir()) {
                continue;
            }
            $path = $entry->getPathname();
            $block_json = $path . '/block.json';
            if (!is_file($block_json)) {
                continue;
            }
            register_block_type($path);
        }
    }

    public static function log_save_errors(): void
    {
        set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline): bool {
            error_log('[Cours CMS AcfBlocks save_post] ' . $errstr . ' in ' . $errfile . ':' . $errline);
            return false;
        });
    }

    /**
     * Rendu d'un bloc ACF via Timber (utilisé si block.json pointe render sur cette méthode ou callback PHP).
     */
    public static function render_block(array $attributes, string $content = '', bool $is_preview = false, int $post_id = 0, $wp_block = null): string
    {
        $slug = isset($attributes['name']) ? str_replace('acf/', '', $attributes['name']) : 'unknown';
        try {
            $context = Timber::context();
            $context['attributes'] = $attributes;
            $context['fields'] = function_exists('get_fields') ? (get_fields() ?: []) : [];
            $context['is_preview'] = $is_preview;

            $template = 'blocks/' . $slug . '/' . $slug . '.twig';
            ob_start();
            Timber::render($template, $context);
            return (string) ob_get_clean();
        } catch (\Throwable $e) {
            error_log('[Cours CMS AcfBlocks] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            if ($is_preview) {
                return '<div class="acf-block-error" style="padding:1em;border:1px solid #c00;color:#c00;">Erreur bloc : ' . esc_html($e->getMessage()) . '</div>';
            }
            return '';
        }
    }
}
