<?php

class KB_WooCommerce_Braspress_Road extends KB_WooCommerce_Braspress_Shipping_Method implements
    KB_WooCommerce_Braspress_Shipping_Interface {

    /**
     * @var int
     */
    protected $type;

    /**
     * @var string
     */
    protected $mode;

    /**
     * KB_WooCommerce_Braspress_Road constructor.
     * @param int $instance_id
     */
    public function __construct($instance_id = 0)
    {
        $this->id = 'kb-braspress-road';
        $this->title = __('Braspress Road', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN);
        $this->method_title = __('Braspress Road', KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN);
        $this->mode = KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_MODAL_TYPE_ROAD;
        $this->type = KB_WOOCOMMERCE_BRASPRESS_SHIPPING_TYPE_CIF;

        parent::__construct($instance_id);
    }
}
