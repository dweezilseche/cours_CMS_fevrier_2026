<?php
/**
 * Starter Theme - Functions
 * 
 * Point d'entrée du thème.
 * La logique métier (CPT, Taxonomies, Config, etc.) est gérée par le MU-Plugin.
 * Ce fichier gère UNIQUEMENT le chargement des assets (Vite).
 * 
 * @package StarterTheme
 * @version 1.0.0
 */

// Sécurité : Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale du thème
 * 
 * Responsabilités :
 * - Chargement des assets via Vite (dev ou prod)
 * - Gestion du type="module" pour les scripts ES6
 * 
 * ATTENTION : 
 * - NE PAS gérer les menus, CPT, taxonomies, ACF ici (c'est le rôle du mu-plugin)
 * - NE PAS initialiser Timber ici (déjà fait dans le mu-plugin)
 * - NE PAS ajouter de logique métier ici
 */
class StarterTheme {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Actions WordPress
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Ajouter type="module" aux scripts Vite
        add_filter('script_loader_tag', [$this, 'add_module_to_scripts'], 10, 2);
    }
    
    /**
     * Chargement des assets avec Vite
     * 
     * En mode dev : charge depuis le serveur Vite (HMR activé)
     * En mode prod : charge depuis dist/ (fichiers compilés)
     */
    public function enqueue_assets() {
        $theme_dir = get_template_directory();
        $theme_uri = get_template_directory_uri();
        
        // Mode développement (Vite dev server sur port 5173)
        if ($this->is_vite_dev_server_running()) {
            // Vite client (HMR)
            wp_enqueue_script(
                'vite-client',
                'http://localhost:5173/@vite/client',
                [],
                null,
                false
            );
            
            // Point d'entrée JavaScript principal
            wp_enqueue_script(
                'theme-app',
                'http://localhost:5173/src/js/app.js',
                [],
                null,
                true
            );
        } 
        // Mode production (fichiers compilés dans dist/)
        else {
            // Vite 5 place le manifest dans dist/.vite/manifest.json
            $manifest_path = file_exists($theme_dir . '/dist/.vite/manifest.json')
                ? $theme_dir . '/dist/.vite/manifest.json'
                : $theme_dir . '/dist/manifest.json';

            if (file_exists($manifest_path)) {
                $manifest = json_decode(file_get_contents($manifest_path), true);
                $entry = $manifest['src/js/app.js'] ?? null;

                if ($entry) {
                    // CSS (émis par Vite depuis l'import dans app.js)
                    if (!empty($entry['css'])) {
                        foreach ($entry['css'] as $css_file) {
                            wp_enqueue_style(
                                'theme-styles',
                                $theme_uri . '/dist/' . $css_file,
                                [],
                                null
                            );
                        }
                    }

                    // JavaScript
                    wp_enqueue_script(
                        'theme-scripts',
                        $theme_uri . '/dist/' . $entry['file'],
                        [],
                        null,
                        true
                    );
                }
            }
        }
        
        // Données globales pour JavaScript
        wp_localize_script(
            $this->is_vite_dev_server_running() ? 'theme-app' : 'theme-scripts',
            'wpData',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_rest'),
                'siteUrl' => get_site_url(),
                'themeUrl' => $theme_uri,
                'currentLang' => function_exists('pll_current_language') ? pll_current_language() : 'fr',
            ]
        );
    }
    
    /**
     * Ajouter type="module" aux scripts Vite
     * 
     * Nécessaire pour les modules ES6
     */
    public function add_module_to_scripts($tag, $handle) {
        $module_handles = ['vite-client', 'theme-app', 'theme-scripts'];
        if (in_array($handle, $module_handles, true)) {
            return str_replace(' src', ' type="module" src', $tag);
        }
        return $tag;
    }
    
    /**
     * Vérifie si le serveur de dev Vite est actif
     * 
     * @return bool True si Vite dev server est actif
     */
    private function is_vite_dev_server_running() {
        // Force le mode dev si environnement local
        if (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'local') {
            return true;
        }
        
        // Vérifie si le serveur Vite répond
        $context = stream_context_create([
            'http' => [
                'timeout' => 0.5,
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents('http://localhost:5173/@vite/client', false, $context);
        
        return $response !== false;
    }
}

// Instancier le thème
new StarterTheme();

/**
 * Détection de connexion réussie dans l'iframe de la modal
 * Envoie un message postMessage à la fenêtre parente
 */
add_action('login_footer', function() {
    ?>
    <script>
    (function() {
        // Vérifier si on est dans une iframe
        if (window.self !== window.top) {
            // Surveiller les redirections après connexion
            const urlParams = new URLSearchParams(window.location.search);
            
            // Si on détecte une redirection ou un message de succès
            if (urlParams.get('loggedout') === 'true' || 
                urlParams.get('registration') === 'complete' ||
                document.querySelector('.message.success')) {
                
                // Ne pas notifier sur la déconnexion
                if (urlParams.get('loggedout') !== 'true') {
                    window.parent.postMessage({ type: 'wp-login-success' }, '*');
                }
            }
            
            // Surveiller les soumissions de formulaire
            const loginForm = document.getElementById('loginform');
            if (loginForm) {
                loginForm.addEventListener('submit', function() {
                    // Après soumission, surveiller si on est redirigé
                    setTimeout(function checkLogin() {
                        // Si la page a changé et qu'il n'y a pas d'erreur
                        if (!document.querySelector('.login-error') && 
                            !document.querySelector('#login_error')) {
                            
                            // Si on n'est plus sur wp-login.php, la connexion a réussi
                            if (!window.location.href.includes('wp-login.php')) {
                                window.parent.postMessage({ type: 'wp-login-success' }, '*');
                            }
                        }
                    }, 500);
                });
            }
        }
    })();
    </script>
    <?php
});
