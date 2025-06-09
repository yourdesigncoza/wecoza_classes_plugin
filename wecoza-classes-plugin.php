<?php
/**
 * Plugin Name: WeCoza Classes Plugin
 * Plugin URI: https://yourdesign.co.za/wecoza-classes-plugin
 * Description: A comprehensive class management system for WeCoza training programs. Handles class creation, scheduling, learner management, and calendar integration with full MVC architecture.
 * Version: 1.0.0
 * Author: Your Design Co
 * Author URI: https://yourdesign.co.za
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wecoza-classes
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 *
 * @package WeCozaClasses
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
// Use datetime for development to prevent caching issues
define('WECOZA_CLASSES_VERSION', date('YmdHis')); // e.g., 20250609213656
define('WECOZA_CLASSES_PLUGIN_FILE', __FILE__);
define('WECOZA_CLASSES_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WECOZA_CLASSES_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WECOZA_CLASSES_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Define plugin paths
define('WECOZA_CLASSES_INCLUDES_DIR', WECOZA_CLASSES_PLUGIN_DIR . 'includes/');
define('WECOZA_CLASSES_APP_DIR', WECOZA_CLASSES_PLUGIN_DIR . 'app/');
define('WECOZA_CLASSES_ASSETS_DIR', WECOZA_CLASSES_PLUGIN_DIR . 'assets/');
define('WECOZA_CLASSES_CONFIG_DIR', WECOZA_CLASSES_PLUGIN_DIR . 'config/');

// Define plugin URLs
define('WECOZA_CLASSES_ASSETS_URL', WECOZA_CLASSES_PLUGIN_URL . 'assets/');
define('WECOZA_CLASSES_JS_URL', WECOZA_CLASSES_ASSETS_URL . 'js/');
define('WECOZA_CLASSES_CSS_URL', WECOZA_CLASSES_ASSETS_URL . 'css/');

/**
 * Plugin activation hook
 */
function activate_wecoza_classes_plugin() {
    require_once WECOZA_CLASSES_INCLUDES_DIR . 'class-activator.php';
    WeCoza_Classes_Activator::activate();
}
register_activation_hook(__FILE__, 'activate_wecoza_classes_plugin');

/**
 * Plugin deactivation hook
 */
function deactivate_wecoza_classes_plugin() {
    require_once WECOZA_CLASSES_INCLUDES_DIR . 'class-deactivator.php';
    WeCoza_Classes_Deactivator::deactivate();
}
register_deactivation_hook(__FILE__, 'deactivate_wecoza_classes_plugin');

/**
 * Plugin uninstall hook
 */
function uninstall_wecoza_classes_plugin() {
    require_once WECOZA_CLASSES_INCLUDES_DIR . 'class-uninstaller.php';
    WeCoza_Classes_Uninstaller::uninstall();
}
register_uninstall_hook(__FILE__, 'uninstall_wecoza_classes_plugin');

/**
 * Load plugin text domain for internationalization
 */
function wecoza_classes_load_textdomain() {
    load_plugin_textdomain(
        'wecoza-classes',
        false,
        dirname(WECOZA_CLASSES_PLUGIN_BASENAME) . '/languages/'
    );
}
add_action('plugins_loaded', 'wecoza_classes_load_textdomain');

/**
 * Initialize the plugin
 */
function run_wecoza_classes_plugin() {
    // Load the main plugin class
    require_once WECOZA_CLASSES_INCLUDES_DIR . 'class-wecoza-classes-plugin.php';
    
    // Initialize the plugin
    $plugin = new WeCoza_Classes_Plugin();
    $plugin->run();
}

/**
 * Check if WordPress and required dependencies are loaded
 */
function wecoza_classes_init() {
    // Check if WordPress is loaded
    if (!function_exists('add_action')) {
        return;
    }
    
    // Check for minimum WordPress version
    if (version_compare(get_bloginfo('version'), '5.0', '<')) {
        add_action('admin_notices', 'wecoza_classes_wordpress_version_notice');
        return;
    }
    
    // Check for minimum PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        add_action('admin_notices', 'wecoza_classes_php_version_notice');
        return;
    }
    
    // All checks passed, run the plugin
    run_wecoza_classes_plugin();
}

/**
 * WordPress version notice
 */
function wecoza_classes_wordpress_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('WeCoza Classes Plugin requires WordPress 5.0 or higher.', 'wecoza-classes');
    echo '</p></div>';
}

/**
 * PHP version notice
 */
function wecoza_classes_php_version_notice() {
    echo '<div class="notice notice-error"><p>';
    echo esc_html__('WeCoza Classes Plugin requires PHP 7.4 or higher.', 'wecoza-classes');
    echo '</p></div>';
}

// Initialize the plugin
wecoza_classes_init();
