<?php
/**
 * Plugin Name:  Kayo Bruno - Braspress for WooCommerce
 * Plugin URI: https://github.com/kayobruno/kb-woocommerce-braspress
 * Description: This is an unofficial Braspress plugin for shipping calculation
 * Author: Kayo Bruno <kayodw@gmail.com>
 * Author URI: https://github.com/kayobruno
 * Version: 1.2.0
 * License: GPLv3 or later
 * Text Domain: kb-woocommerce-braspress
 * Domain Path: /languages
 *
 * @package KB_WooCommerce_Braspress
 */

defined('ABSPATH') || exit;

define('KB_WOOCOMMERCE_BRASPRESS_VERSION', '1.0.0');
define('KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN', 'kb-woocommerce-braspress');

// Environments
define('KB_WOOCOMMERCE_BRASPRESS_ENVIRONMENT_STAGING', 'staging');
define('KB_WOOCOMMERCE_BRASPRESS_ENVIRONMENT_PRODUCTION', 'production');

// Shipping Types
define('KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_CIF', 1);
define('KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_FOB', 2);
define('KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_CONSIGNED', 3);
define('KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_MODAL_TYPE_AIR', 'A');
define('KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_MODAL_TYPE_ROAD', 'R');


/** get_environments
 * The core class of KB WooCommerce Braspress,
 * that is used to define many aspects about this plugin.
 */
require plugin_dir_path(__FILE__) . 'includes/class-kb-woocommerce-braspress.php';
require plugin_dir_path(__FILE__) . 'includes/class-kb-woocommerce-braspress-loader.php';

/**
 * This function runs during KB WooCommerce-Braspress activation.
 */
function activate_woocommerce_braspress()
{
    kb_woocommerce_braspress_require_class(
        plugin_dir_path(__FILE__) . 'includes/class-kb-woocommerce-braspress-activator.php',
        'KB_WooCommerce_Braspress_Activator'
    )->activate();
}

/**
 * This function runs during KB WooCommerce-Braspress deactivation.
 */
function deactivate_woocommerce_braspress()
{
    kb_woocommerce_braspress_require_class(
        plugin_dir_path(__FILE__) . 'includes/class-kb-woocommerce-braspress-deactivator.php',
        'KB_WooCommerce_Braspress_Deactivator'
    )->deactivate();
}

register_activation_hook(__FILE__, 'activate_kb_woocommerce_braspress');
register_deactivation_hook(__FILE__, 'deactivate_kb_woocommerce_braspress');

/**
 * This function require a class file an return
 * an instantiate of the class.
 *
 * @since 1.0.0
 * @param string $file_path path to the class file.
 * @param string $class_name class to be instantiated.
 * @return object|null The instance of the class.
 */
function kb_woocommerce_braspress_require_class(string $file_path, string $class_name)
{
    if (file_exists($file_path)) {
        require_once($file_path);
        return new $class_name;
    }

    return null;
}

/**
 * This function starts the plugin execution.
 *
 * @since 1.0.0
 */
function run_kb_woocommerce_braspress()
{
    if (KB_WooCommerce_Braspress::is_woocommerce_activated()) {
        $plugin = new KB_WooCommerce_Braspress(
            new KB_WooCommerce_Braspress_Loader()
        );
        $plugin->run();
    }
}

run_kb_woocommerce_braspress();
