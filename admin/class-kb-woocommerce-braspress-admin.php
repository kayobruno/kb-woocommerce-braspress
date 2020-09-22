<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since 1.0.0
 *
 * @package KB_WooCommerce_Braspress
 * @subpackage admin
 */

class KB_WooCommerce_Braspress_Admin {

    /**
     * The ID of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var string $KB_WooCommerce_Braspress The ID of this plugin.
     */
    private $name;

    /**
     * The version of this plugin.
     *
     * @since  1.0.0
     * @access private
     * @var string $version The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $name The name of this plugin.
     * @param string $version The version of this plugin.
     * @since 1.0.0
     */
    public function __construct(string $name, string $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    private function enqueue_styles()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in KB_WooCommerce_Braspress_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The KB_WooCommerce_Braspress_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        wp_enqueue_style(
            $this->name,
            plugin_dir_url(__FILE__) . 'css/kb-woocommerce-braspress-admin.css',
            array(),
            $this->version, 'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since 1.0.0
     */
    private function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Woocommerce_Braspress_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Woocommerce_Braspress_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $scripts = array(
            array(
                'handle' => 'input_mask',
                'src' => plugin_dir_url(__FILE__) . '../assets/js/jquery.mask.js',
                'dependencies' => array('jquery'),
                'version' => $this->version,
                'in_footer' => true,
            ),
            array(
                'handle' => 'default_js',
                'src' => plugin_dir_url(__FILE__) . 'js/kb-woocommerce-braspress-admin.js',
                'dependencies' => array('jquery'),
                'version' => $this->version,
                'in_footer' => true,
            ),
        );

        array_walk($scripts,
            function($script) {
                wp_enqueue_script(
                    $script['handle'],
                    $script['src'],
                    $script['dependencies'],
                    $script['version'],
                    $script['in_footer']
                );
            }
        );
    }

    /**
     * Register all assets of KB Woocommerce Braspress.
     *
     * @since   1.0.0
     */
    public function enqueue_assets()
    {
        $this->enqueue_styles();
        $this->enqueue_scripts();
    }
}
