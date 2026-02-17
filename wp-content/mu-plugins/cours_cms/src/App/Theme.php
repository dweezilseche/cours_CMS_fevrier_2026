<?php

namespace App;

use App\Acf\GlobalFields;
use Timber\Timber;
use Wkn\Theme as WknTheme;

defined('ABSPATH') || exit;

class Theme extends WknTheme
{
    public static function init(): void
    {
        parent::init();

        Timber::$dirname = ['views'];
        $environment = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production';
        if (defined('WP_ENV')) {
            $environment = WP_ENV;
        }
        $isProduction = ($environment === 'production' && (!defined('WP_DEBUG') || !WP_DEBUG));
        Timber::$cache = $isProduction;
        Timber::$autoescape = false;

        add_theme_support('title-tag');
        add_theme_support('post-thumbnails');
        add_theme_support('post-formats', ['aside', 'gallery', 'link', 'image', 'quote', 'video', 'audio']);
        add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'script', 'style']);
        add_theme_support('custom-logo', [
            'height'      => 100,
            'width'       => 400,
            'flex-height' => true,
            'flex-width'  => true,
        ]);
        add_theme_support('align-wide');
        add_theme_support('responsive-embeds');
        add_theme_support('editor-styles');

        register_nav_menus([
            'header_main'      => __('Menu principal header', 'app'),
            'header_secondary' => __('Menu secondaire header', 'app'),
            'footer_main'      => __('Menu principal footer', 'app'),
            'footer_secondary'=> __('Menu secondaire footer', 'app'),
            'footer_legal'     => __('Menu légal footer', 'app'),
        ]);

        register_sidebar([
            'name'          => __('Sidebar principale', 'app'),
            'id'            => 'sidebar-main',
            'description'   => __('Sidebar principale du site', 'app'),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3 class="widget__title">',
            'after_title'   => '</h3>',
        ]);

        add_image_size('card', 600, 400, true);
        add_image_size('hero', 1920, 1080, true);
        add_image_size('thumbnail-large', 400, 400, true);
        add_image_size('gallery', 800, 600, true);

        add_action('wp_dashboard_setup', [self::class, 'wpDashboardSetup']);
        add_action('wp_before_admin_bar_render', [self::class, 'wpBeforeAdminBarRender']);
        add_action('admin_menu', [self::class, 'adminRemoveMenus']);
        add_action('login_enqueue_scripts', [self::class, 'wpm_login_style']);
        add_action('admin_head', [self::class, 'admin_custom_styles']);
        add_filter('timber/context', [self::class, 'addToContext']);

        if (!defined('USE_GUTENBERG') || !constant('USE_GUTENBERG')) {
            add_filter('use_block_editor_for_post', [self::class, 'enable_only_for_landings'], 10, 2);
        }
        add_filter('allowed_block_types_all', [self::class, 'allowed_block_types_all'], 10, 2);
        add_filter('upload_mimes', [self::class, 'allow_webp_upload']);
        add_filter('file_is_displayable_image', [self::class, 'webp_is_displayable_image'], 10, 2);

        Pagination::init();
    }

    /**
     * Autorise l'upload de fichiers WebP dans la médiathèque.
     *
     * @param array<string, string> $mimes
     * @return array<string, string>
     */
    public static function allow_webp_upload(array $mimes): array
    {
        $mimes['webp'] = 'image/webp';
        return $mimes;
    }

    /**
     * Considère le WebP comme image affichable (miniatures, éditeur, etc.).
     */
    public static function webp_is_displayable_image(bool $result, string $path): bool
    {
        if ($result) {
            return true;
        }
        return strtolower((string) pathinfo($path, PATHINFO_EXTENSION)) === 'webp';
    }

    /**
     * Gutenberg : activer uniquement pour certains templates (ex: landings).
     */
    public static function enable_only_for_landings(bool $can_edit, \WP_Post $post): bool
    {
        $allowed_templates = ['template-landing.php'];
        $template = get_page_template_slug($post);
        return in_array($template, $allowed_templates, true);
    }

    /**
     * Restreindre les blocs autorisés (ACF custom + certains core).
     */
    public static function allowed_block_types_all($block_types, $editor_context)
    {
        if (empty($editor_context->post)) {
            return $block_types;
        }
        $acf_blocks = [];
        $blocks_dir = get_template_directory() . '/views/blocks';
        if (is_dir($blocks_dir)) {
            foreach (new \DirectoryIterator($blocks_dir) as $entry) {
                if ($entry->isDir() && !$entry->isDot() && is_file($entry->getPathname() . '/block.json')) {
                    $acf_blocks[] = 'acf/' . $entry->getFilename();
                }
            }
        }
        $core = ['core/paragraph', 'core/heading', 'core/image', 'core/list', 'core/quote', 'core/buttons', 'core/separator'];
        return array_merge($core, $acf_blocks);
    }

    public static function addToContext(array $context): array
    {
        $context['theme'] = [
            'name'    => wp_get_theme()->get('Name'),
            'version' => wp_get_theme()->get('Version'),
            'uri'     => get_template_directory_uri(),
            'path'    => get_template_directory(),
        ];
        $context['menu_header_main']       = Timber::get_menu('header_main');
        $context['menu_header_secondary']  = Timber::get_menu('header_secondary');
        $context['menu_footer_main']        = Timber::get_menu('footer_main');
        $context['menu_footer_secondary']   = Timber::get_menu('footer_secondary');
        $context['menu_footer_legal']       = Timber::get_menu('footer_legal');
        $custom_logo_id = get_theme_mod('custom_logo');

        if ($custom_logo_id) {
            $context['logo'] = Timber::get_post($custom_logo_id);
        }

        if (class_exists(GlobalFields::class)) {
            $context['fields'] = GlobalFields::get();
        }

        if (taxonomy_exists('product_tag')) {
            $context['woocommerce_product_tags'] = Timber::get_terms([
                'taxonomy' => 'product_tag', 'hide_empty' => true, 'orderby' => 'count', 'order' => 'DESC', 'number' => 5,
            ]);
        } else {
            $context['woocommerce_product_tags'] = [];
        }

        if (taxonomy_exists('product_cat')) {
            $uncategorized = get_term_by('slug', 'non-classe', 'product_cat');
            $exclude_ids = $uncategorized ? [$uncategorized->term_id] : [];
            $context['woocommerce_product_categories'] = Timber::get_terms([
                'taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC', 'exclude' => $exclude_ids,
            ]);
        }
        
        if (function_exists('wc_get_page_id')) {
            $context['shop_page_id'] = wc_get_page_id('shop');
        }
        if (function_exists('WC') && WC()->cart) {
            $context['cart_count'] = (int) WC()->cart->get_cart_contents_count();
        } else {
            $context['cart_count'] = 0;
        }
        return $context;
    }

    public static function wpDashboardSetup(): void
    {
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_action('welcome_panel', 'wp_welcome_panel');
    }

    public static function wpBeforeAdminBarRender(): void
    {
        global $wp_admin_bar;
        if (!$wp_admin_bar) {
            return;
        }
        $wp_admin_bar->remove_node('wp-logo');
        $wp_admin_bar->remove_node('updates');
        $wp_admin_bar->remove_node('comments');
    }

    /**
     * Masque Outils, Réglages, Extensions, ACF et Utilisateurs pour les utilisateurs
     * qui n'ont pas la capacité manage_options (seuls les administrateurs les voient).
     */
    public static function adminRemoveMenus(): void
    {
        if (current_user_can('manage_options')) {
            return;
        }
        $pages = [
            'tools.php',
            'options-general.php',
            'plugins.php',
            'edit.php?post_type=acf-field-group',
            'users.php',
        ];
        foreach ($pages as $page) {
            remove_menu_page($page);
        }
    }

    public static function wpm_login_style(): void
    {
        ?>
        <style type="text/css">
            body.login { background: #141414; background: url(<?php echo esc_url(get_stylesheet_directory_uri()); ?>/dist/imgs/background-admin.png) center center no-repeat; background-size: cover; }
            #login { padding-top: 10% !important; }
            #loginform { border-radius: 0.625em; border-color: #e1e1e1; }
            #loginform input { border-color: #e1e1e1; }
            #login h1 a, .login h1 a { background-image: url(<?php echo esc_url(get_stylesheet_directory_uri()); ?>/dist/imgs/logo_odyssey.png); background-size: contain; width: 152.84px; height: 45px; }
            #wp-submit { color: #fff; background: #000; border: none; }
            #language-switcher { display: none; }
            .dashicons.dashicons-admin-users { color: #000 !important; }
            .login #backtoblog a, .login #nav a { color: #858585 !important; }
            .login .message, .login .notice, .login .success { border-left: 4px solid #000 !important; }
            .dashicons.dashicons-visibility { color: #000; }
            .privacy-policy-link { color: #fff; }
        </style>
        <?php
    }

    public static function admin_custom_styles(): void
    {
        echo '<style>.block-editor-block-list__block { max-width: none; }</style>';
    }

    /**
     * Déqueue des CSS WordPress inutiles (front).
     */
    public static function remove_wordpress_css(): void
    {
        add_action('wp_enqueue_scripts', function (): void {
            wp_dequeue_style('wp-block-library');
            wp_dequeue_style('wp-block-library-theme');
            wp_dequeue_style('classic-theme-styles');
        }, 100);
    }

    /**
     * Chemin de stockage privé (uploads).
     */
    private static function getPrivateMediaPath(): string
    {
        $dir = wp_get_upload_dir();
        $path = $dir['basedir'] . '/private';
        if (!is_dir($path)) {
            wp_mkdir_p($path);
        }
        return $path;
    }

    /**
     * Copie un fichier dans le stockage privé et retourne le nom du fichier.
     */
    public static function addAttachment(string $img_url, string $filename, string $reponame = 'private'): string
    {
        $base = self::getPrivateMediaPath();
        $sub = $reponame ? $base . '/' . $reponame : $base;
        if (!is_dir($sub)) {
            wp_mkdir_p($sub);
        }
        $path = $sub . '/' . $filename;
        if (filter_var($img_url, FILTER_VALIDATE_URL)) {
            $content = @file_get_contents($img_url);
            if ($content !== false) {
                file_put_contents($path, $content);
            }
        }
        return $filename;
    }
}
