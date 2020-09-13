<?php

class KB_WooCommerce_Braspress_i18n {

    /**
     * It loads all the methods, files and functionalities needed
     * for the internationalization of KB WooCommerce Braspress.
     *
     * @since 1.0.0
     */
    public function load_text_domain()
    {
        load_plugin_textdomain(
            KB_WOOCOMMERCE_BRASPRESS_TEXT_DOMAIN,
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
