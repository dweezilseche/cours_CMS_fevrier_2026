<?php
/**
 * Plugin Name: Cours CMS MU Plugin
 * Description: Must-Use Plugin pour l'architecture MVC (Timber, ACF, CPT)
 * Version: 1.0.0
 * Author: Dweezil Sèche
 * Author URI: https://dweezilseche.fr
 * License: Proprietary
 */

defined('ABSPATH') || exit;

$cours_cms_bootstrap = __DIR__ . '/odyssee/bootstrap.php';
if (is_readable($cours_cms_bootstrap)) {
    require_once $cours_cms_bootstrap;
}
