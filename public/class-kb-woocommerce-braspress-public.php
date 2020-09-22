<?php

/**
 * The public functionality of the plugin
 *
 * @since 1.0.0
 * @package KB_WooCommerce_Braspress
 * @subpackage admin
 */
class KB_WooCommerce_Braspress_Public {

    /**
     * The ID of this plugin.
     *
     * @since 1.0.0
     * @access private
     * @var string $KB_WooCommerce_Braspress The ID of this plugin.
     */
    private $name;

    /**
     * The version of this plugin.
     *
     * @since 1.0.0
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
     * Register the stylesheets for the public side of the plugin
     *
     * @since 1.0.0
     */
    private function enqueue_styles()
    {
        /**
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
            plugin_dir_url(__FILE__) . 'css/kb-woocommerce-braspress-public.css', array(),
            $this->version,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public side of the KB Woocommerce Braspress.
     *
     * @since 1.0.0
     */
    private function enqueue_scripts()
    {
        /**
         * An instance of this class should be passed to the run() function
         * defined in KB_WooCommerce_Braspress_Loader as all of the hooks are defined
         * in that particular class.
         *
         * The KB_WooCommerce_Braspress_Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        $scripts = array(
            array(
                'handle' => 'input_mask',
                'src' => plugin_dir_url(__FILE__) . '../assets/js/jquery.mask.js',
                'dependencies' => array('jquery'),
                'version' => $this->version,
                'in_footer' => true
            ),
            array(
                'handle' => 'default_js',
                'src' => plugin_dir_url(__FILE__) . 'js/kb-woocommerce-braspress-public.js',
                'dependencies' => array('jquery'),
                'version' => $this->version,
                'in_footer' => true,
            ),
            array(
                'handle' => 'validate_identifier',
                'src' => plugin_dir_url(__FILE__) . 'js/kb-woocommerce-braspress-identifier-validate.js',
                'dependencies' => array('jquery'),
                'version' => $this->version,
                'in_footer' => true,
            ),
        );

        array_walk($scripts,
            function( $script ) {
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
     * Register all CSS and JavaScript assets.
     *
     * @since 1.0.0
     */
    public function enqueue_assets()
    {
        $this->enqueue_styles();
        $this->enqueue_scripts();
    }
}
