<?php

namespace App\Controllers;

use App\PostsTypes\CharmRequestPostType;

defined('ABSPATH') || exit;

/**
 * Enregistre le CPT des demandes charms et traite le formulaire de la page Sur-mesure.
 *
 * Sécurité : nonce CSRF, honeypot, rate limiting, reCAPTCHA v3 (optionnel), validation stricte, uploads sécurisés.
 *
 * reCAPTCHA v3 (optionnel) : définir dans wp-config.php :
 *   CHARM_REQUEST_RECAPTCHA_SITE_KEY, CHARM_REQUEST_RECAPTCHA_SECRET_KEY
 *   et optionnellement CHARM_REQUEST_RECAPTCHA_THRESHOLD (défaut 0.5).
 */
class CharmRequestController
{
    private static ?CharmRequestPostType $_post_type = null;

    public const NONCE_ACTION = 'charm_request_submit';
    public const NONCE_FIELD = 'charm_request_nonce';

    /** Champ honeypot : doit rester vide (bots le remplissent). */
    public const HONEYPOT_FIELD = 'charm_request_website';

    /** Limite d'envois par IP : nombre max. */
    public const RATE_LIMIT_COUNT = 3;
    /** Limite d'envois par IP : fenêtre en secondes (1 heure). */
    public const RATE_LIMIT_WINDOW = 3600;

    /** Longueurs max pour validation. */
    public const MAX_NOM = 100;
    public const MAX_PRENOM = 100;
    public const MAX_MESSAGE = 5000;
    public const MAX_TELEPHONE = 30;
    /** Taille max pièce jointe en octets (5 Mo). */
    public const MAX_ATTACHMENT_BYTES = 5 * 1024 * 1024;

    public static function init(): void
    {
        add_action('init', [self::class, '_registerPostType'], 0);
        add_action('template_redirect', [self::class, '_maybeHandleFormSubmit']);
        add_action('add_meta_boxes', [self::class, '_addMetaBox']);
    }

    public static function _addMetaBox(): void
    {
        add_meta_box(
            'charm_request_details',
            __('Détails de la demande', 'app'),
            [self::class, '_renderMetaBox'],
            CharmRequestPostType::NAME,
            'normal'
        );
    }

    /**
     * @param \WP_Post $post
     */
    public static function _renderMetaBox($post): void
    {
        $nom = get_post_meta($post->ID, CharmRequestPostType::META_NOM, true);
        $prenom = get_post_meta($post->ID, CharmRequestPostType::META_PRENOM, true);
        $email = get_post_meta($post->ID, CharmRequestPostType::META_EMAIL, true);
        $telephone = get_post_meta($post->ID, CharmRequestPostType::META_TELEPHONE, true);
        $message = get_post_meta($post->ID, CharmRequestPostType::META_MESSAGE, true);
        $attachment_id = (int) get_post_meta($post->ID, CharmRequestPostType::META_ATTACHMENT_ID, true);
        ?>
        <table class="form-table">
            <tr><th><?php esc_html_e('Nom', 'app'); ?></th><td><?php echo esc_html($nom); ?></td></tr>
            <tr><th><?php esc_html_e('Prénom', 'app'); ?></th><td><?php echo esc_html($prenom); ?></td></tr>
            <tr><th><?php esc_html_e('Adresse mail', 'app'); ?></th><td><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></td></tr>
            <tr><th><?php esc_html_e('Téléphone', 'app'); ?></th><td><a href="tel:<?php echo esc_attr($telephone); ?>"><?php echo esc_html($telephone); ?></a></td></tr>
            <tr><th><?php esc_html_e('Message', 'app'); ?></th><td><?php echo nl2br(esc_html($message)); ?></td></tr>
            <?php if ($attachment_id) : ?>
                <?php $url = wp_get_attachment_url($attachment_id); ?>
                <tr><th><?php esc_html_e('Pièce jointe', 'app'); ?></th><td><a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener"><?php echo esc_html(basename(get_attached_file($attachment_id))); ?></a></td></tr>
            <?php endif; ?>
        </table>
        <?php
    }

    public static function _registerPostType(): void
    {
        self::$_post_type = new CharmRequestPostType();
        self::$_post_type->register();
    }

    public static function _maybeHandleFormSubmit(): void
    {
        if (! isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST' || ! isset($_POST[self::NONCE_FIELD])) {
            return;
        }

        $page_id = (int) ($_POST['charm_request_page_id'] ?? 0);
        if (! $page_id || get_post_type($page_id) !== 'page') {
            return;
        }

        $permalink = get_permalink($page_id);
        $redirect_error = function () use ($permalink): void {
            wp_safe_redirect(add_query_arg('charm_request_error', '1', $permalink));
            exit;
        };

        // 1. Honeypot : si rempli = bot
        $honeypot = isset($_POST[self::HONEYPOT_FIELD]) ? trim(sanitize_text_field(wp_unslash($_POST[self::HONEYPOT_FIELD]))) : '';
        if ($honeypot !== '') {
            $redirect_error();
        }

        // 2. Nonce CSRF
        if (! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST[self::NONCE_FIELD])), self::NONCE_ACTION)) {
            $redirect_error();
        }

        $template = get_page_template_slug($page_id);
        if ($template !== 'page-custom-made.php') {
            return;
        }

        // 3. Rate limiting par IP
        $ip = self::_getClientIp();
        if (self::_isRateLimited($ip)) {
            wp_safe_redirect(add_query_arg(['charm_request_error' => '1', 'charm_request_rate_limit' => '1'], $permalink));
            exit;
        }

        // 4. reCAPTCHA v3 (si clés définies)
        if (self::_isRecaptchaEnabled() && ! self::_verifyRecaptcha()) {
            $redirect_error();
        }

        $nom = isset($_POST['charm_request_nom']) ? sanitize_text_field(wp_unslash($_POST['charm_request_nom'])) : '';
        $prenom = isset($_POST['charm_request_prenom']) ? sanitize_text_field(wp_unslash($_POST['charm_request_prenom'])) : '';
        $email = isset($_POST['charm_request_email']) ? sanitize_email(wp_unslash($_POST['charm_request_email'])) : '';
        $telephone = isset($_POST['charm_request_telephone']) ? sanitize_text_field(wp_unslash($_POST['charm_request_telephone'])) : '';
        $message = isset($_POST['charm_request_message']) ? sanitize_textarea_field(wp_unslash($_POST['charm_request_message'])) : '';

        if ($nom === '' || $prenom === '' || $email === '' || $telephone === '' || $message === '') {
            $redirect_error();
        }

        // 5. Validation des longueurs et format email
        if (
            mb_strlen($nom) > self::MAX_NOM
            || mb_strlen($prenom) > self::MAX_PRENOM
            || mb_strlen($message) > self::MAX_MESSAGE
            || mb_strlen($telephone) > self::MAX_TELEPHONE
            || ! is_email($email)
        ) {
            $redirect_error();
        }

        self::_incrementRateLimit($ip);

        $title = sprintf(
            /* translators: 1: prénom, 2: nom */
            __('Demande - %1$s %2$s', 'app'),
            $prenom,
            $nom
        );

        $post_id = wp_insert_post([
            'post_type'   => CharmRequestPostType::NAME,
            'post_title'  => $title,
            'post_status' => 'publish',
            'post_author' => 1,
        ], true);

        if (is_wp_error($post_id)) {
            wp_safe_redirect(add_query_arg('charm_request_error', '1', get_permalink($page_id)));
            exit;
        }

        update_post_meta($post_id, CharmRequestPostType::META_NOM, $nom);
        update_post_meta($post_id, CharmRequestPostType::META_PRENOM, $prenom);
        update_post_meta($post_id, CharmRequestPostType::META_EMAIL, $email);
        update_post_meta($post_id, CharmRequestPostType::META_TELEPHONE, $telephone);
        update_post_meta($post_id, CharmRequestPostType::META_MESSAGE, $message);

        $attachment_id = self::_handleAttachment($post_id);
        if ($attachment_id) {
            update_post_meta($post_id, CharmRequestPostType::META_ATTACHMENT_ID, $attachment_id);
        }

        wp_safe_redirect(add_query_arg('charm_request_sent', '1', get_permalink($page_id)));
        exit;
    }

    private static function _getClientIp(): string
    {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            if (! empty($_SERVER[$key])) {
                $ip = is_string($_SERVER[$key]) ? trim($_SERVER[$key]) : '';
                if (strpos($ip, ',') !== false) {
                    $ip = trim((string) preg_replace('/\s*,\s*/', ',', $ip));
                    $parts = explode(',', $ip);
                    $ip = trim($parts[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    private static function _getRateLimitKey(string $ip): string
    {
        return 'charm_request_rate_' . md5($ip);
    }

    private static function _isRateLimited(string $ip): bool
    {
        $key = self::_getRateLimitKey($ip);
        $count = (int) get_transient($key);
        return $count >= self::RATE_LIMIT_COUNT;
    }

    private static function _incrementRateLimit(string $ip): void
    {
        $key = self::_getRateLimitKey($ip);
        $count = (int) get_transient($key);
        set_transient($key, $count + 1, self::RATE_LIMIT_WINDOW);
    }

    private static function _isRecaptchaEnabled(): bool
    {
        return defined('CHARM_REQUEST_RECAPTCHA_SECRET_KEY') && CHARM_REQUEST_RECAPTCHA_SECRET_KEY !== '';
    }

    private static function _verifyRecaptcha(): bool
    {
        $token = isset($_POST['charm_request_recaptcha_token']) ? sanitize_text_field(wp_unslash($_POST['charm_request_recaptcha_token'])) : '';
        if ($token === '') {
            return false;
        }

        $secret = CHARM_REQUEST_RECAPTCHA_SECRET_KEY;
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'timeout' => 10,
            'body'    => [
                'secret'   => $secret,
                'response' => $token,
                'remoteip' => self::_getClientIp(),
            ],
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        if ($code !== 200 || $body === '') {
            return false;
        }

        $data = json_decode($body, true);
        if (! is_array($data) || empty($data['success'])) {
            return false;
        }

        $score = isset($data['score']) ? (float) $data['score'] : 0.0;
        $threshold = defined('CHARM_REQUEST_RECAPTCHA_THRESHOLD') ? (float) CHARM_REQUEST_RECAPTCHA_THRESHOLD : 0.5;
        return $score >= $threshold;
    }

    /**
     * Gère l'upload de la pièce jointe optionnelle.
     * Vérifie extension, MIME type et taille.
     *
     * @return int Attachment ID ou 0 en cas d'échec / pas de fichier.
     */
    private static function _handleAttachment(int $post_id): int
    {
        if (empty($_FILES['charm_request_attachment']['name']) || $_FILES['charm_request_attachment']['error'] !== UPLOAD_ERR_OK) {
            return 0;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $file = $_FILES['charm_request_attachment'];

        if (! is_uploaded_file($file['tmp_name']) || $file['size'] > self::MAX_ATTACHMENT_BYTES) {
            return 0;
        }

        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'webp'];
        $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (! in_array($ext, $allowed_ext, true)) {
            return 0;
        }

        $detected = '';
        if (function_exists('finfo_open') && is_readable($file['tmp_name'])) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if ($finfo) {
                $detected = (string) finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
            }
        }
        if ($detected !== '' && ! in_array($detected, $allowed_mimes, true)) {
            return 0;
        }

        $upload = wp_handle_upload($file, ['test_form' => false]);
        if (isset($upload['error'])) {
            return 0;
        }

        $attachment = [
            'post_mime_type' => $upload['type'],
            'post_title'     => sanitize_file_name(pathinfo($file['name'], PATHINFO_FILENAME)),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ];
        $attachment_id = wp_insert_attachment($attachment, $upload['file'], $post_id);
        if (is_wp_error($attachment_id)) {
            return 0;
        }

        wp_generate_attachment_metadata($attachment_id, $upload['file']);
        return (int) $attachment_id;
    }

    public static function getPostType(): ?CharmRequestPostType
    {
        return self::$_post_type;
    }
}
