<?php

abstract class KB_WooCommerce_Braspress_Shipping_Method extends WC_Shipping_Method {

    use KB_WooCommerce_Braspress_Functions;

    /**
     * @var string
     */
    private $origin_postcode;

    /**
     * @var string
     */
    private $destination_identifier;

    /**
     * @var bool
     */
    private $show_deadline_days;

    /**
     * KB_WooCommerce_Braspress_Shipping_Method constructor.
     * @param int $instance_id
     */
    public function __construct($instance_id = 0)
    {
        $this->instance_id = absint($instance_id);
        $description = "It's a shipping method that allow to the customer to ship your product using %s.";
        $this->method_description = sprintf(
            __($description, KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
            $this->method_title
        );

        $this->supports  = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal'
        );

        $this->enabled  = 'yes';
        $this->fob_message = $this->get_option('fob_message');
        $this->shipping_class_id = (int) $this->get_option('shipping_class_id', '-1');
        $this->origin_postcode = $this->get_option('origin_postcode');
        $this->origin_identifier = $this->get_option('origin_identifier');
        $this->destination_identifier = $this->get_destination_identifier();
        $this->show_deadline_days = $this->get_option('show_deadline_days');
        $this->additional_days = $this->get_option('additional_days');

        $this->init_form_fields();
        $this->init_settings();

        add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));

        parent::__construct($instance_id);
    }


    /**
     * Add admin options fields.
     */
    public function init_form_fields()
    {
        $this->instance_form_fields = array(
            'enabled' => array(
                'title' => __('Enable/Disable', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'checkbox',
                'label' => __('Enable this shipping method', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'default' => __('yes'),
            ),
            'title' => array(
                'title' => __('Title', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __(
                    'This controls the title which the user sees during checkout.',
                    KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN
                ),
                'desc_tip' => true,
                'default' => $this->method_title,
            ),
            'environment' => array(
                'title' => __('Environment', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'select',
                'description' => __(
                    'Each environment has a different username and password',
                    KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN
                ),
                'desc_tip' => true,
                'default' => '',
                'class' => 'wc-enhanced-select',
                'options' => $this->get_environments(),
            ),
            'user' => array(
                'title' => __('User', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __(
                    'Contact braspress to request your user and password',
                    KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN
                ),
                'desc_tip' => true,
                'default' => '',
            ),
            'password' => array(
                'title' => __('Password', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __(
                    'Contact braspress to request your user and password',
                    KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN
                ),
                'desc_tip' => true,
                'default' => '',
            ),
            'behavior_options' => array(
                'title' => __('Behavior Options', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'title',
                'default' => '',
            ),
            'show_deadline_days' => array(
                'title' => __('Show/Hide', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'checkbox',
                'label' => __('Show estimated delivery', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'default' => 'yes',
            ),
            'additional_days' => array(
                'title' => __('Additional days', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('Additional days to the estimated delivery.', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'desc_tip' => true,
                'default' => '0',
                'placeholder' => '0',
            ),
            'shipping_class_id' => array(
                'title' => __('Shipping Class', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'select',
                'description' => __(
                    'If necessary, select a shipping class to apply this method.',
                    KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN
                ),
                'desc_tip' => true,
                'default' => '',
                'class' => 'wc-enhanced-select',
                'options' => $this->get_shipping_classes_options(),
            ),
            'origin_postcode' => array(
                'title' => __('Origin Postcode', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __(
                    'The postcode of the location your packages are delivered from.',
                    KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN
                ),
                'class' => 'origin_postcode',
                'desc_tip' => true,
                'placeholder' => '00000-000',
                'default' => '',
            ),
            'origin_identifier' => array(
                'title' => __('Origin Identifier', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'type' => 'text',
                'description' => __('The identifier of your company', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                'placeholder' => '00.000.000/0000-00',
                'class' => 'identifier',
                'desc_tip' => true,
            ),
        );

        if ($this->is_fob()) {
            $this->instance_form_fields = array_merge(
                $this->instance_form_fields,
                array(
                    'optional_services' => array(
                        'title' => __( 'Optional Services', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN ),
                        'type' => 'title',
                        'default' => '',
                    ),
                    'fob_message' => array(
                        'title' => __('Message FOB (Free on Board)', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                        'type' => 'text',
                        'label' => __('Enable', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                        'description' => __(
                            'Message that will be displayed when FOB(Free on Board) mode is selected.',
                            KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN
                        ),
                        'desc_tip' => true,
                        'default' => __('Remove on conveyor', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                    ),
                )
            );
        }
    }

    /**
     * get all environments
     *
     * @return array
     */
    public function get_environments()
    {
        return array(
            KB_WOOCOMMERCE_BRASPRESS_ENVIRONMENT_STAGING => __('Staging', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
            KB_WOOCOMMERCE_BRASPRESS_ENVIRONMENT_PRODUCTION => __('Production', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
        );
    }

    /**
     * Get shipping classes options.
     *
     * @return array
     */
    protected function get_shipping_classes_options()
    {
        $shipping_classes = WC()->shipping->get_shipping_classes();
        $options = array(
            '-1' => __('Any Shipping Class', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
            '0' => __('No Shipping Class', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
        );

        if (!empty($shipping_classes)) {
            $options += wp_list_pluck($shipping_classes, 'name', 'term_id');
        }

        return $options;
    }

    /**
     * Return if the method is fob.
     *
     * @return bool
     */
    protected function is_fob()
    {
        return ($this->get_type() == KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_FOB);
    }

    /**
     * This method calculate the shipping price.
     *
     * @param $package
     * @return void
     */
    public function calculate_shipping($package = array())
    {
        if ('' === $package['destination']['postcode'] || 'BR' !== $package['destination']['country'] ) {
            return;
        }

        if (!$this->has_only_selected_shipping_class($package)) {
            return;
        }

        $shipping = null;
        try {
            $shipping = $this->get_rate($package);
        } catch (\Exception $e) {
           if (is_cart()) {
               $notice = sprintf(
                   '%s: %s',
                   $this->title,
                   __($e->getMessage(), KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN)
               );
               wc_add_notice($notice, 'notice');
           }
            return;
        }

        if (null === $shipping) {
            return;
        }

        if (!isset($shipping['total_shipping']) && is_cart()) {
            $notice = sprintf(
                '%s: %s',
                $this->title,
                __('Unable to calculate shipping', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN)
            );

            wc_add_notice($notice, 'notice');
            return;
        }

        $method_label = $this->get_shipping_label((int) $shipping['deadline']);
        $cost = $this->normalize_money(esc_attr((string) $shipping['total_shipping']));

        if (0 === intval($cost)) {
            return;
        }

        $rate = array(
            'id' => $this->id,
            'label' => $method_label,
            'cost' => $shipping['total_shipping'],
            'calc_tax' => 'per_item',
        );

        $this->add_rate($rate);
    }

    /**
     * Get shipping rate.
     *
     * @param $package
     * @return array
     * @throws Exception
     */
    protected function get_rate($package)
    {
        $api = new KB_WooCommerce_Braspress_API($this->id, $this->instance_id);
        $api->set_environment($this->get_option('environment'));
        $api->set_user($this->get_option('user'));
        $api->set_password($this->get_option('password'));
        $api->set_type($this->get_type());
        $api->set_mode($this->get_mode());
        $api->set_type($this->get_type());
        $api->set_origin_postcode($this->get_option('origin_postcode'));
        $api->set_destination_postcode($package['destination']['postcode']);
        $api->set_origin_identifier($this->get_origin_identifier());
        $api->set_destination_identifier($this->get_destination_identifier());
        $api->set_total_price($this->get_total_price($package));
        $api->set_total_weight($this->get_total_weight($package));
        $api->set_total_package($this->get_total_package($package));

        return $api->get_shipping($package);
    }

    /**
     * @param $package
     * @return mixed
     */
    protected function get_total_price($package)
    {
        return $package['contents_cost'];
    }

    /**
     * @param $package
     * @return mixed
     */
    protected function get_total_weight($package )
    {
        $measured_package = $this->measures_extract($package);
        return $measured_package['weight'];
    }

    /**
     * @param $package
     * @return array
     */
    protected function measures_extract($package): array
    {
        $count= 0;
        $height = $width = $length = $weight = array();

        foreach ($package['contents'] as $item_id => $values) {
            $product = $values['data'];
            $qty = $values['quantity'];

            if ($qty > 0 && $product->needs_shipping()) {
                if (version_compare(WOOCOMMERCE_VERSION, '2.1', '>=')) {
                    $_height = wc_get_dimension($this->fix_format($product->height), 'cm');
                    $_width  = wc_get_dimension($this->fix_format($product->width), 'cm');
                    $_length = wc_get_dimension($this->fix_format($product->length), 'cm');
                    $_weight = wc_get_weight($this->fix_format($product->weight), 'kg');
                } else {
                    $_height = woocommerce_get_dimension($this->fix_format($product->height), 'cm');
                    $_width  = woocommerce_get_dimension($this->fix_format($product->width), 'cm');
                    $_length = woocommerce_get_dimension($this->fix_format($product->length), 'cm');
                    $_weight = woocommerce_get_weight($this->fix_format($product->weight), 'kg');
                }

                $height[$count] = $_height;
                $width[$count] = $_width;
                $length[$count] = $_length;
                $weight[$count] = $_weight;

                if ($qty > 1) {
                    $n = $count;
                    for ($i = 0; $i < $qty; $i++) {
                        $height[$n] = $_height;
                        $width[$n] = $_width;
                        $length[$n] = $_length;
                        $weight[$n] = $_weight;
                        $n++;
                    }
                    $count = $n;
                }
                $count++;
            }
        }

        return array(
            'height' => array_values($height),
            'length' => array_values($length),
            'width' => array_values($width),
            'weight' => array_sum($weight),
        );
    }

    /**
     * @param $package
     * @return int
     */
    protected function get_total_package($package)
    {
        $package_quantity = 0;

        array_walk($package['contents'], function($value) use (&$package_quantity){
            $package_quantity += (int) $value['quantity'];
        });

        return $package_quantity;
    }


    /**
     * Check if package uses only the selected shipping class.
     *
     * @param $package
     * @return bool
     */
    protected function has_only_selected_shipping_class($package)
    {
        $only_selected = true;
        if (-1 === $this->shipping_class_id) {
            return $only_selected;
        }

        foreach ($package['contents'] as $item_id => $values) {
            $product = $values['data'];
            $qty = $values['quantity'];

            if ($qty > 0 && $product->needs_shipping()) {
                if ($this->shipping_class_id !== $product->get_shipping_class_id()) {
                    $only_selected = false;
                    break;
                }
            }
        }

        return $only_selected;
    }

    private function get_mode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    private function get_origin_identifier(): string
    {
        return $this->remove_chars($this->get_option('origin_identifier'));
    }

    /**
     * @param $deadline_days
     * @return mixed
     */
    private function get_shipping_label($deadline_days)
    {
        if ('yes' === $this->get_option('show_deadline_days')) {
            return $this->get_estimating_delivery($this->title, $deadline_days, $this->get_additional_days());
        }

        return $this->title;
    }

    function get_estimating_delivery( $title, $deadline_days, $additional_days = 0)
    {
        $total_additional_days = intval($deadline_days) + intval($additional_days);

        if ( $total_additional_days > 0 ) {
			$title .= ' - ' . sprintf(
			    __('Delivery in %d working day(s)', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN),
                $total_additional_days
            );
        }

        return $title;
    }

    /**
     * @return mixed
     */
    private function get_additional_days()
    {
        return $this->get_option('additional_days');
    }

    /**
     * Get setting form fields for instances of this shipping method.
     *
     * @return array
     */
    public function get_instance_form_fields()
    {
        if (is_admin()) {
            wc_enqueue_js("
				jQuery(function($) {
					function kbWooCommerceBraspressShowHideAdditionalDays(el) {						
				        var b = jQuery('input[id$=\"_additional_days\"]').closest(\"tr\");
				        $(el).is(\":checked\") ? b.show() : b.hide()
					}

					$( document.body ).on('change', 'input[id$=\"_show_deadline_days\"]', function() {
						kbWooCommerceBraspressShowHideAdditionalDays(this);
					});
				});
			");
        }

        return parent::get_instance_form_fields();
    }

    /**
     * Return the shipment type.
     *
     * @return String
     */
    protected function get_type()
    {
        return (string) $this->type;
    }

    public function get_destination_identifier(): string
    {
        $data = isset($_POST['post_data']) ? sanitize_text_field($_POST['post_data']) : '';
        $shipping_identifier = isset($_POST['shipping_identifier'])
            ? sanitize_text_field($_POST['shipping_identifier'])
            : ''
        ;

        if (!empty($data)) {

            $post_data = array();

            parse_str($data, $post_data);
            if (isset($post_data['billing_cpf_cnpj']) && $post_data['billing_cpf_cnpj'] != '') {
                $this->set_destination_identifier($post_data['billing_cpf_cnpj']);
            }
        } elseif (isset($shipping_identifier) && $shipping_identifier != '') {
            $this->set_destination_identifier($shipping_identifier);
        }

        if ( null !== $this->destination_identifier) {
            WC()->session->set('_session_identifier', $this->destination_identifier);
        } else {
            $session_identifier = '';
            if (isset(WC()->session)) {
                $session_identifier = WC()->session->get('_session_identifier');
            }
            $this->destination_identifier = $session_identifier;
        }

        return $this->remove_chars($this->destination_identifier);
    }

    /**
     * @param string $identifier
     * @return $this
     */
    private function set_destination_identifier(string $identifier)
    {
        $this->destination_identifier = $identifier;
        return $this;
    }

    /**
     * @return string
     */
    public function get_origin_postcode(): string
    {
        return $this->origin_postcode;
    }

    /**
     * @param string $origin_postcode
     * @return $this
     */
    public function set_origin_postcode(string $origin_postcode)
    {
        $this->origin_postcode = $origin_postcode;
        return $this;
    }
}
