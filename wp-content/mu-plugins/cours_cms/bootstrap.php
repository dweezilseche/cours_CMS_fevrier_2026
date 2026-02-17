    <?php

    defined('ABSPATH') || exit('403');

    if (!defined('APP_PATH')) {
        define('APP_PATH', __DIR__);
    }
    if (!defined('APP_URL')) {
        define('APP_URL', WP_CONTENT_URL . '/mu-plugins/cours_cms');
    }

    $autoload = APP_PATH . '/vendor/autoload.php';
    if (is_readable($autoload)) {
        require_once $autoload;
    }

    spl_autoload_register(function (string $class): void {
        $prefix = 'App\\';
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        $relative = substr($class, $len);
        $path = APP_PATH . '/src/App/' . str_replace('\\', '/', $relative) . '.php';
        if (is_file($path)) {
            require_once $path;
        }
    });

    add_action('after_setup_theme', function (): void {
        \Wkn\Wokine::init([
            \App\Configuration::class,
            \App\Theme::class,
            \App\Header::class,
            \App\Footer::class,
            \App\Reinssurance::class,
            \App\Socials::class,
            \App\Pagination::class,
            \App\ClassMapper::class,
            \App\Acf\AcfBlocks::class,
            \App\Acf\AcfContext::class,
            \App\Controllers\CharmsController::class,
            \App\Controllers\CharmRequestController::class,
            \App\Controllers\ClientController::class,
            \App\Controllers\FAQController::class,
            \App\WooCommerce::class,
        ]);
    }, 10);
