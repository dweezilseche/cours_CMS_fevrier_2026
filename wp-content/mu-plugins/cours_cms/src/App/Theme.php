<?php

namespace App;

use App\Acf\AcfBlocks;
use App\Acf\AcfContext;
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
        
        // Désactiver le cache Twig (forcer à false)
        Timber::$cache = false;
        Timber::$twig_cache = false;
        
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

        // Activer les inscriptions utilisateurs
        update_option('users_can_register', 1);
        update_option('default_role', 'customer'); // Rôle par défaut : client WooCommerce
        
        add_action('wp_dashboard_setup', [self::class, 'wpDashboardSetup']);
        add_action('wp_before_admin_bar_render', [self::class, 'wpBeforeAdminBarRender']);
        add_action('admin_menu', [self::class, 'adminRemoveMenus']);
        add_action('admin_menu', [self::class, 'addMyEventsMenuForSubscribers']);
        add_action('admin_init', [self::class, 'handleEventUnregister']);
        add_action('login_enqueue_scripts', [self::class, 'wpm_login_style']);
        add_action('admin_head', [self::class, 'admin_custom_styles']);
        add_action('admin_head', [self::class, 'hideProfileSectionsForSubscribers']);
        
        add_filter('timber/context', [self::class, 'addToContext']);

        // Donner les capacités de gestion des joueurs aux abonnés (pour qu'ils puissent voir les joueurs dans l'admin)
        add_action('init', [self::class, 'grantPlayerCaps']);

        if (!defined('USE_GUTENBERG') || !constant('USE_GUTENBERG')) {
            add_filter('use_block_editor_for_post', [self::class, 'enable_only_for_landings'], 10, 2);
        }
        add_filter('allowed_block_types_all', [self::class, 'allowed_block_types_all'], 10, 2);
        add_filter('upload_mimes', [self::class, 'allow_webp_upload']);
        add_filter('file_is_displayable_image', [self::class, 'webp_is_displayable_image'], 10, 2);
        add_filter('upload_size_limit', [self::class, 'increase_upload_limit']);
        add_filter('wp_max_upload_size', [self::class, 'increase_upload_limit']);
        
        // Désactiver les wrappers HTML de The Events Calendar pour utiliser Timber
        add_filter('tribe_events_before_html', '__return_false');
        add_filter('tribe_events_after_html', '__return_false');
        add_filter('tribe_template_pre_html', [self::class, 'disableTecTemplateWrapper'], 10, 4);

        
    }

    /**
     * Augmente la limite de téléversement à 30Mo.
     */
    public static function increase_upload_limit(int $size): int
    {
        return 30 * 1024 * 1024; // 30Mo en octets
    }

    /**
     * Désactive le wrapper HTML de The Events Calendar pour permettre à Timber
     * de gérer complètement la structure HTML via base.twig.
     *
     * @param mixed $html Le HTML à afficher (peut être null)
     * @param string $file Le fichier template
     * @param string|array $name Le nom du template (peut être un tableau)
     * @param object $template L'objet template
     * @return false|string|null False pour désactiver le wrapper, ou le HTML
     */
    public static function disableTecTemplateWrapper($html, $file, $name, $template)
    {
        // Gérer le cas où $name est un tableau
        if (is_array($name)) {
            // Ne rien faire, laisser TEC gérer
            return $html;
        }
        
        // Désactiver le wrapper pour le template default-template/single-event
        if (is_string($name) && (strpos($name, 'default-template') !== false || strpos($name, 'single-event') !== false)) {
            return false;
        }
        
        return $html;
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

        // ACF fields are automatically available on post objects (e.g., post.hero, post.our_concept)
        // via Timber's built-in ACF integration
        
        if (function_exists('wc_get_page_id')) {
            $context['shop_page_id'] = wc_get_page_id('shop');
        }
        if (function_exists('WC') && WC()->cart) {
            $context['cart_count'] = (int) WC()->cart->get_cart_contents_count();
        } else {
            $context['cart_count'] = 0;
        }
        
        // Ajouter les subscribers avec leurs infos
        $context['subscribers'] = self::getSubscribersWithInfos();
        
        // Ajouter les derniers articles
        $context['latest_posts'] = self::getLatestPosts(5);
        
        return $context;
    }

    /**
     * Récupère tous les subscribers avec leurs infos ACF (avatar + points).
     * Retourne un tableau d'utilisateurs triés par points décroissants.
     */
    public static function getSubscribersWithInfos(): array
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
                // Récupérer les champs ACF
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
     * Récupère les derniers articles publiés.
     * 
     * @param int $count Nombre d'articles à récupérer
     * @return \Timber\PostCollectionInterface|array
     */
    public static function getLatestPosts()
    {
        $args = [
            'post_type' => 'post',
            'post_status' => 'publish',
            'posts_per_page' => '-1',
            'orderby' => 'date',
            'order' => 'DESC',
        ];
        
        $posts = Timber::get_posts($args);
        return $posts ?: [];
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
        
        // Ajouter un lien "Mes évènements" pour les subscribers et customers
        $user = wp_get_current_user();
        if (in_array('subscriber', $user->roles) || in_array('customer', $user->roles)) {
            $wp_admin_bar->add_node([
                'id'    => 'my-events',
                'title' => '<span class="ab-icon dashicons-calendar-alt"></span>' . __('Mes évènements', 'app'),
                'href'  => admin_url('admin.php?page=my-events'),
            ]);
        }
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

    /**
     * Ajoute un menu "Mes évènements" pour les subscribers et customers.
     */
    public static function addMyEventsMenuForSubscribers(): void
    {
        $user = wp_get_current_user();
        
        // Ajouter le menu seulement pour subscribers et customers
        if (in_array('subscriber', $user->roles) || in_array('customer', $user->roles)) {
            add_menu_page(
                __('Mes évènements', 'app'),
                __('Mes évènements', 'app'),
                'read',
                'my-events',
                [self::class, 'renderMyEventsPage'],
                'dashicons-calendar-alt',
                25
            );
        }
    }

    /**
     * Gère la désinscription d'un événement.
     */
    public static function handleEventUnregister(): void
    {
        if (!isset($_GET['action']) || $_GET['action'] !== 'unregister_event') {
            return;
        }
        
        if (!isset($_GET['attendee_id']) || !isset($_GET['_wpnonce'])) {
            return;
        }
        
        $attendee_id = intval($_GET['attendee_id']);
        $nonce = sanitize_text_field($_GET['_wpnonce']);
        
        // Vérifier le nonce
        if (!wp_verify_nonce($nonce, 'unregister_event_' . $attendee_id)) {
            wp_die(__('Action non autorisée.', 'app'));
        }
        
        // Vérifier que l'utilisateur est connecté
        if (!is_user_logged_in()) {
            wp_die(__('Vous devez être connecté.', 'app'));
        }
        
        $current_user_id = get_current_user_id();
        $user_email = wp_get_current_user()->user_email;
        
        // Vérifier via l'API Event Tickets que cet attendee appartient à l'utilisateur
        $is_user_attendee = false;
        $found_event_id = null;
        
        if (function_exists('tribe_tickets_get_attendees')) {
            // Récupérer tous les événements avec tickets
            $events_query = new \WP_Query([
                'post_type' => 'tribe_events',
                'posts_per_page' => -1,
                'post_status' => 'publish',
            ]);
            
            if ($events_query->have_posts()) {
                while ($events_query->have_posts()) {
                    $events_query->the_post();
                    $event_id = get_the_ID();
                    
                    // Récupérer les attendees pour cet événement
                    $attendees = tribe_tickets_get_attendees($event_id);
                    
                    if (!empty($attendees)) {
                        foreach ($attendees as $attendee) {
                            // Vérifier si cet attendee correspond à celui qu'on veut supprimer
                            if (isset($attendee['attendee_id']) && (int) $attendee['attendee_id'] === $attendee_id) {
                                // Vérifier si cet attendee appartient à l'utilisateur courant
                                $attendee_user_id = isset($attendee['user_id']) ? (int) $attendee['user_id'] : 0;
                                $attendee_email = isset($attendee['purchaser_email']) ? $attendee['purchaser_email'] : '';
                                
                                if ($attendee_user_id === $current_user_id || $attendee_email === $user_email) {
                                    $is_user_attendee = true;
                                    $found_event_id = $event_id;
                                    break 2; // Sortir des deux boucles
                                }
                            }
                        }
                    }
                }
                wp_reset_postdata();
            }
        }
        
        if ($is_user_attendee) {
            // Supprimer l'attendee
            $deleted = wp_delete_post($attendee_id, true);

            $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : '';
            $same_site   = $redirect_to && (strpos($redirect_to, home_url()) === 0);
            $redirect_url = ($redirect_to && $same_site)
                ? add_query_arg(['message' => 'unregistered', 'nocache' => time()], $redirect_to)
                : add_query_arg(['page' => 'my-events', 'message' => 'unregistered', 'nocache' => time()], admin_url('admin.php'));

            if ($deleted && $found_event_id) {
                wp_cache_delete($found_event_id, 'tribe_attendees');
                wp_cache_delete('attendees_' . $found_event_id, 'tribe_events');
                delete_transient('tribe_attendees_' . $found_event_id);
                do_action('event_tickets_after_delete_ticket', $attendee_id, $found_event_id);
                wp_redirect($redirect_url);
                exit;
            }
            if ($deleted) {
                wp_redirect($redirect_url);
                exit;
            }
            wp_redirect(($redirect_to && $same_site)
                ? add_query_arg(['message' => 'error'], $redirect_to)
                : add_query_arg(['page' => 'my-events', 'message' => 'error'], admin_url('admin.php')));
            exit;
        }
        wp_die(__('Vous ne pouvez pas vous désinscrire de cet événement. ID: ' . $attendee_id, 'app'));
    }

    /**
     * Récupère les événements auxquels l'utilisateur connecté est inscrit (RSVP / billet).
     * Utilisé par la page admin "Mes évènements" et par le template front page-my-events.
     *
     * @return array Liste d'entrées [ 'event_id' => int, 'event_title' => string, 'event_url' => string, 'ticket_name' => string, 'order_status' => string, 'attendee_id' => string|int ]
     */
    public static function get_current_user_events_data(): array
    {
        $current_user_id = get_current_user_id();
        if ( ! $current_user_id) {
            return [];
        }
        $user_email = wp_get_current_user()->user_email;
        $user_events = [];

        if ( ! function_exists('tribe_tickets_get_attendees')) {
            return $user_events;
        }

        $events_query = new \WP_Query([
            'post_type'      => 'tribe_events',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        ]);

        if ( ! $events_query->have_posts()) {
            return $user_events;
        }

        while ($events_query->have_posts()) {
            $events_query->the_post();
            $event_id = get_the_ID();
            $attendees = tribe_tickets_get_attendees($event_id);

            if (empty($attendees)) {
                continue;
            }

            foreach ($attendees as $attendee) {
                $attendee_user_id = isset($attendee['user_id']) ? (int) $attendee['user_id'] : 0;
                $attendee_email   = isset($attendee['purchaser_email']) ? $attendee['purchaser_email'] : '';

                if ($attendee_user_id === $current_user_id || $attendee_email === $user_email) {
                    $user_events[] = [
                        'event_id'     => $event_id,
                        'event_title'  => get_the_title($event_id),
                        'event_url'    => get_permalink($event_id),
                        'ticket_name'  => isset($attendee['product_name']) ? $attendee['product_name'] : '',
                        'order_status' => isset($attendee['order_status']) ? $attendee['order_status'] : '',
                        'attendee_id'  => isset($attendee['attendee_id']) ? $attendee['attendee_id'] : '',
                    ];
                    break;
                }
            }
        }
        wp_reset_postdata();

        return $user_events;
    }

    /**
     * Affiche la page "Mes évènements" avec la liste des événements auxquels l'utilisateur est inscrit.
     */
    public static function renderMyEventsPage(): void
    {
        $user_events = self::get_current_user_events_data();

        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('Mes évènements', 'app'); ?></h1>
            
            <?php
            // Afficher les messages
            if (isset($_GET['message'])) {
                $message = sanitize_text_field($_GET['message']);
                if ($message === 'unregistered') {
                    echo '<div class="notice notice-success is-dismissible"><p>';
                    echo esc_html__('Vous avez été désinscrit de l\'événement avec succès.', 'app');
                    echo '</p></div>';
                } elseif ($message === 'error') {
                    echo '<div class="notice notice-error is-dismissible"><p>';
                    echo esc_html__('Une erreur s\'est produite lors de la désinscription.', 'app');
                    echo '</p></div>';
                }
            }
            ?>
            
            <?php if (empty($user_events)) : ?>
                <p><?php echo esc_html__('Vous n\'êtes inscrit à aucun événement pour le moment.', 'app'); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php echo esc_html__('Événement', 'app'); ?></th>
                            <th><?php echo esc_html__('Type de ticket', 'app'); ?></th>
                            <th><?php echo esc_html__('Statut', 'app'); ?></th>
                            <th><?php echo esc_html__('Action', 'app'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_events as $event) : ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?php echo esc_url($event['event_url']); ?>">
                                            <?php echo esc_html($event['event_title']); ?>
                                        </a>
                                    </strong>
                                </td>
                                <td><?php echo esc_html($event['ticket_name']); ?></td>
                                <td>
                                    <?php 
                                    $status = $event['order_status'];
                                    $status_label = $status ? ucfirst($status) : __('Confirmé', 'app');
                                    echo esc_html($status_label);
                                    ?>
                                </td>
                                <td>
                                    <a href="<?php echo esc_url($event['event_url']); ?>" class="button button-secondary">
                                        <?php echo esc_html__('Voir l\'événement', 'app'); ?>
                                    </a>
                                    <?php if ($event['attendee_id']) : 
                                        $unregister_url = wp_nonce_url(
                                            add_query_arg([
                                                'action' => 'unregister_event',
                                                'attendee_id' => $event['attendee_id']
                                            ], admin_url('admin.php')),
                                            'unregister_event_' . $event['attendee_id']
                                        );
                                    ?>
                                    <a href="<?php echo esc_url($unregister_url); ?>" 
                                       class="button button-link-delete" 
                                       onclick="return confirm('<?php echo esc_js(__('Êtes-vous sûr de vouloir vous désinscrire de cet événement ?', 'app')); ?>');">
                                        <?php echo esc_html__('Se désinscrire', 'app'); ?>
                                    </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    public static function wpm_login_style(): void
    {
        $theme_uri = esc_url(get_stylesheet_directory_uri());
        $background = $theme_uri . '/dist/imgs/bg_login.webp';
        $logo = $theme_uri . '/dist/imgs/logo-blanc.svg';
        ?>
        <style type="text/css">
            /* Background */
            body.login {
                background-image: url('<?php echo $background; ?>');
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                min-height: 100vh;
            }

            /* Login container */
            #login {   
                padding-top: 10%!important;
            }

            /* Form */
            #loginform {
                border-radius: 10px;
                border: 1px solid #e1e1e1;
            }

            #loginform input {
                border-color: #e1e1e1;
            }

            /* Logo */
            .login h1 a {
                background-image: url('<?php echo $logo; ?>') !important;
                background-size: contain !important;
                background-repeat: no-repeat !important;
                background-position: center !important;
                width: 250px !important;
                height: 100px !important;
            }

            /* Submit button */
            #wp-submit {
                color: #fff;
                background: #DD531F;
                border: none;
                box-shadow: none;
                text-shadow: none;
            }

            /* Links */
            .login #backtoblog a,
            .login #nav a {
                color: #fff !important;
            }

            /* Notices */
            .login .message,
            .login .notice,
            .login .success {
                border-left: 4px solid #000 !important;
            }

            /* Icons */
            .dashicons.dashicons-admin-users,
            .dashicons.dashicons-visibility {
                color: #000 !important;
            }

            /* Misc */
            #language-switcher {
                display: none;
            }

            .privacy-policy-link {
                color: #fff;
            }
        </style>
        <?php
    }


    public static function admin_custom_styles(): void
    {
        echo '<style>.block-editor-block-list__block { max-width: none; }</style>';
    }

    /**
     * Cache les sections inutiles de la page de profil pour les abonnés.
     */
    public static function hideProfileSectionsForSubscribers(): void
    {
        // Vérifier si on est sur la page de profil
        $screen = get_current_screen();
        if (!$screen || ($screen->id !== 'profile' && $screen->id !== 'user-edit')) {
            return;
        }

        // Vérifier si l'utilisateur est un abonné (subscriber ou customer)
        $user = wp_get_current_user();
        if (!in_array('subscriber', $user->roles) && !in_array('customer', $user->roles)) {
            return;
        }

        // Masquer les sections pour les abonnés
        ?>
        <style type="text/css">
            /* Masquer le jeu de couleurs de l'administration */
            .user-admin-color-wrap,
            
            /* Masquer la barre d'outils */
            .show-admin-bar,
            
            /* Masquer l'illustration du profil (avatar/gravatar) */
            .user-profile-picture,
            .user-description-wrap {
                display: none !important;
            }

            .application-passwords-section{
                display: none !important;;
            }
        </style>
        <?php
    }

    /**
     * Accorde les capacités de gestion des joueurs aux différents rôles.
     * - Administrateurs et éditeurs : toutes les capacités
     * - Abonnés et clients : lecture seule
     */
    public static function grantPlayerCaps(): void
    {
        // Toutes les capacités pour les administrateurs et éditeurs
        $all_capabilities = [
            'read_app_player',
            'read_private_app_players',
            'edit_app_player',
            'edit_app_players',
            'edit_others_app_players',
            'edit_published_app_players',
            'publish_app_players',
            'create_app_players',
            'delete_app_player',
            'delete_app_players',
            'delete_others_app_players',
            'delete_published_app_players',
        ];
        
        // Capacités de lecture seule pour les abonnés
        $read_only_capabilities = [
            'read_app_player',
            'read_private_app_players',
            'read', // Requis pour accéder à l'admin
        ];
        
        // Administrateurs - Toutes les capacités
        $admin_role = get_role('administrator');
        if ($admin_role) {
            foreach ($all_capabilities as $cap) {
                $admin_role->add_cap($cap);
            }
        }
        
        // Éditeurs - Toutes les capacités
        $editor_role = get_role('editor');
        if ($editor_role) {
            foreach ($all_capabilities as $cap) {
                $editor_role->add_cap($cap);
            }
        }
        
        // Abonnés - Lecture seule
        $subscriber_role = get_role('subscriber');
        if ($subscriber_role) {
            foreach ($read_only_capabilities as $cap) {
                $subscriber_role->add_cap($cap);
            }
        }
        
        // Clients WooCommerce - Lecture seule
        $customer_role = get_role('customer');
        if ($customer_role) {
            foreach ($read_only_capabilities as $cap) {
                $customer_role->add_cap($cap);
            }
        }
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
