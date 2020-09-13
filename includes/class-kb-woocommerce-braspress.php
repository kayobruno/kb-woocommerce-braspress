<?php

class KB_WooCommerce_Braspress {

    /**
     * @var string
     */
    protected $version;

    /**
     * @var KB_WooCommerce_Braspress_Loader
     */
    protected $loader;

    /**
     * @var string
     */
    protected $name;

    public function __construct(KB_WooCommerce_Braspress_Loader $loader)
    {
        $this->name = 'kb-woocommerce-braspress';
        $this->version = KB_WOOCOMMERCE_BRASPRESS_VERSION;
        $this->loader = $loader;

        $this->load_required_classes();
        $this->set_locale();
        $this->define_hooks();
    }

    /**
     * Validate if Woocommerce is activated.
     *
     * This static method validate if the Woocommerce
     * plugin is activated in WordPress.
     *
     * @since 1.0.0
     */
    public static function is_woocommerce_activated()
    {
        $activated_plugin = (is_multisite()) ?
            array_merge(
                get_option('active_plugins'),
                get_option('active_sitewide_plugins')
            ) : get_option('active_plugins');

        $activated_plugin = array_merge(
            $activated_plugin,
            array_keys(
                $activated_plugin
            )
        );

        return in_array('woocommerce/woocommerce.php', $activated_plugin);
    }

    private function load_required_classes()
    {
        $required_classes = array(
            "includes/{interfaces,traits}/*.php",
            "includes/class-kb-woocommerce-braspress-loader.php",
            "includes/class-kb-woocommerce-braspress-i18n.php",
            "includes/class-kb-woocommerce-braspress-api.php",
            "admin/class-kb-woocommerce-braspress-admin.php",
            "public/class-kb-woocommerce-braspress-public.php",
        );

        array_walk($required_classes, array($this, 'load_file'));
    }

    /**
     * This method load the class file.
     *
     * @param string $file_path Path of the file.
     */
    private function load_file(string $file_path)
    {
        $glob = glob($this->get_path() . $file_path, GLOB_BRACE);
        array_walk(
            $glob,
            function($file) {
                file_exists($file) ? require_once($file) : die();
            }
        );
    }

    /**
     * This method return the path to this file.
     *
     * @return String
     */
    private function get_path()
    {
        return (string) plugin_dir_path(dirname(__FILE__));
    }

    /**
     * Define internationalization locale for Woocommerce Braspress.
     *
     * Set the class KB_WooCommerce_Braspress_i18n in order to register
     * the hook with WordPress.
     *
     * @since 1.0.0
     * @access private
     */
    private function set_locale()
    {
        $braspress_i18n = new KB_WooCommerce_Braspress_i18n();
        $this->loader->add_action(
            'plugins_loaded',
            $braspress_i18n,
            'load_text_domain'
        );
    }

    /**
     * Register all hooks related of the admin area.
     *
     * @since 1.0.0
     * @access private
     */
    private function define_admin_hooks()
    {
        $actions = array(
            array(
                'hook' => 'admin_enqueue_scripts',
                'component' => new KB_WooCommerce_Braspress_Admin(
                    $this->get_name(),
                    $this->get_version()
                ),
                'callback'  => 'enqueue_assets',
            ),
            array(
                'hook' => 'woocommerce_after_shipping_calculator',
                'component' => new KB_WooCommerce_Braspress_Public(
                    $this->get_name(),
                    $this->get_version()
                ),
                'callback' => 'enqueue_assets',
            ),
        );

        foreach ($actions as $action){
            $this->loader->add_action(
                $action['hook'],
                $action['component'],
                $action['callback']
            );
        }
    }

    /**
     * Register all of the actions related to Woocommerce Braspress.
     *
     * @since 1.0.0
     * @access private
     */
    private function define_wc_action()
    {
        $actions = array(
            'woocommerce_checkout_fields' => array(
                'component' => $this,
                'callback' => 'add_identifier_field',
                'priority' => 10,
                'args' => 1,
            ),
            'woocommerce_checkout_process' => array(
                'component' => $this,
                'callback' => 'customize_checkout_field_process',
                'priority' => 10,
                'args' => 1,
            ),
            'woocommerce_after_shipping_calculator' => array(
                'component' => $this,
                'callback' => 'add_hidden_identifier_field',
                'priority' => 10,
                'args' => 1,
            ),
        );

        array_walk(
            $actions,
            function ($component, $hook) {
                if (!isset($component['component'])) {
                    $component['component'] = null;
                }

                $this->loader->add_action(
                    $hook,
                    $component['component'],
                    $component['callback']
                );
            }
        );
    }

    function customize_checkout_field_process()
    {
        // TODO: ADD AN IDENTIFIER VALIDATION
    }

    /**
     * This method, add a identifier custom field to checkout fields.
     *
     * @param $fields array with all checkout fields.
     *
     * @return array
     */
    public function add_identifier_field(array $fields)
    {
        $fields['billing']['billing_cpf_cnpj'] = array(
            'ui-mask' => '999.999.999-99',
            'label' => __('Identifier', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
            'priority' => 1,
            'maxlength' => '18',
            'required' => true,
            'placeholder' => __('Identifier', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
            'class' => array('address-field', 'update_totals_on_change', 'cpf_cnpj'),
            'default' => WC()->session->get('_session_cpf_cnpj'),
        );

        return $fields;
    }

    public function add_hidden_identifier_field()
    {
        $destination_identifier = null;
        if (isset($_POST['post_data'])) {
            $post_data = array();
            parse_str($_POST['post_data'], $post_data);

            if (isset($post_data['billing_cpf_cnpj']) && $post_data['billing_cpf_cnpj'] != '') {
                $destination_identifier = $post_data['billing_cpf_cnpj'];
            }
        } elseif (
            isset($_POST['calc_shipping_cpf']) &&
            preg_replace('/[^0-9]/', '', $_POST['calc_shipping_cpf']) != ''
        ) {
            $destination_identifier = preg_replace('/[^0-9]/', '', $_POST['calc_shipping_cpf']);
        }

        $cpf_cnpj_session = WC()->session->get('_session_cpf_cnpj');
        if (null !== $destination_identifier) {
            WC()->session->set('_session_cpf_cnpj', $destination_identifier);
        } elseif (is_null($destination_identifier)) {
            $destination_identifier = $cpf_cnpj_session;
        }

        echo sprintf(
            '<input id="identifier_post" value="%s" type="hidden" />',
            $destination_identifier
        );
    }

    /**
     * Register all of the hooks related to Woocommerce functionality
     * of the KB WooCommerce Braspress.
     *
     * @since 1.0.0
     * @access private
     */
    private function define_wc_filters()
    {
        /**
         * **woocommerce_shipping_calculator_enable_city**:
         *  Remove the city field on cart shipping calculator.
         *
         * **woocommerce_shipping_methods**:
         *  Add shipping methods to the Woocommerce.
         *
         */
        $filters = array(
            'woocommerce_shipping_methods' => array(
                "component" => $this,
                "callback"  => 'include_methods'
            ),
        );

        array_walk(
            $filters,
            function($component, $hook) {
                if (!isset($component['component'])) {
                    $component['component'] = null;
                }

                $this->loader->add_filter(
                    $hook,
                    $component['component'],
                    $component['callback']
                );
            }
        );
    }

    /**
     * Include KB WooCommerce Braspress shipping methods to WooCommerce.
     *
     * @param array $methods Default shipping methods.
     * @return array
     */
    public function include_methods(array $methods)
    {
        $this->load_file('includes/abstracts/abstract-kb-woocommerce-braspress-shipping-method.php');

        $shipping_methods = array(
            'kb-braspress-aeropress' => array(
                'class' => 'KB_WooCommerce_Braspress_Air',
                'file_path' => 'includes/shipping/class-kb-woocommerce-braspress-air.php'
            ),
            'kb-braspress-rodoviario' => array(
                'class' => 'KB_WooCommerce_Braspress_Road',
                'file_path' => 'includes/shipping/class-kb-woocommerce-braspress-road.php'
            ),
            'braspress-aeropress-fob' => array(
                'class' => 'KB_WooCommerce_Braspress_Air_FOB',
                'file_path' => 'includes/shipping/class-kb-woocommerce-braspress-air-fob.php'
            ),
            'braspress-rodoviario-fob' => array(
                'class' => 'KB_WooCommerce_Braspress_Road_FOB',
                'file_path' => 'includes/shipping/class-kb-woocommerce-braspress-road-fob.php'
            ),
        );

        if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.6.0', '>=')) {
            array_walk(
                $shipping_methods,
                function ($class, $method) use (&$methods) {
                    require_once($this->get_path() . $class['file_path']);
                    if (in_array('KB_WooCommerce_Braspress_Shipping_Interface', class_implements($class['class']))) {
                        $methods[$method] = $class["class"];
                    }
                }
            );
        }

        return $methods;
    }

    /**
     * Execute all of the hooks loaded in the $loader.
     *
     * @since 1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * Name of the plugin used in WordPress to define the internationalization.
     *
     * @since 1.0.0
     * @return string The name of Woocommerce Braspress plugin.
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * This method return the loader, that contains all the
     * filters and hooks of KB WooCommerce Braspress.
     *
     * @since 1.0.0
     * @return KB_WooCommerce_Braspress_Loader Class that manage the hooks and filter of KB WooCommerce Braspress.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Return the version number of KB WooCommerce Braspress.
     *
     * @since 1.0.0
     * @return string The version number of KB WooCommerce Braspress.
     */
    public function get_version()
    {
        return $this->version;
    }

    /**
     * Register all KB WooCommerce Braspress hooks.
     *
     * @since 1.0.0
     */
    private function define_hooks()
    {
        $this->define_admin_hooks();
        $this->define_wc_filters();
        $this->define_wc_action();
    }
}
