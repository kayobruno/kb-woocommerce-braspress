<?php

class KB_WooCommerce_Braspress_Road_FOB extends KB_WooCommerce_Braspress_Shipping_Method implements
    KB_WooCommerce_Braspress_Shipping_Interface {

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $mode;

    public function __construct($instance_id = 0)
    {
        $this->id = 'kb-braspress-road-fob';
        $this->title = $this->get_option('title', __('Braspress Road - FOB', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN));
        $this->method_title = __('Braspress Road - FOB', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN);
        $this->mode = KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_MODAL_TYPE_ROAD;
        $this->type = KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_FOB;

        parent::__construct($instance_id);
    }
}
