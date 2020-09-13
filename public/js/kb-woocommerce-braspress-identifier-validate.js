(function($) {
    'use strict';

    let elem_identifier = '.cpf_cnpj>input',
        options 	= {
            onKeyPress: function (identifier, e, field, options) {
                let masks = ['000.000.000-000', '00.000.000/0000-00'],
                    mask = (identifier.length > 14) ? masks[1] : masks[0];
                $(elem_identifier).mask(mask, options);
            }
        },
        initial_mask = '000.000.000-000000';

    $('.cart-collaterals').on('click',
        function () {
            let is_kb_braspress = true,
                identifier_value = '',
                selected_method  = $(this).find('.shipping_method[checked=checked]').attr('id');

            $.each(
                [
                    'kb-braspress-rodoviario',
                    'kb-braspress-aeropress',
                    'kb-braspress-rodoviario-fob',
                    'kb-braspress-aeropress-fob'
                ],
                function( index, value ) {
                    is_kb_braspress = (typeof selected_method !== 'undefined' && selected_method.indexOf(value) > 0)
                        ? true
                        : is_kb_braspress
                    ;
                }
            );

            if (!$('#calc_shipping_cpf').length && is_kb_braspress) {
                if ( $('#cpf_cnpj_post').length ) {
                    identifier_value = $('#cpf_cnpj_post').val();
                }

                $('form.woocommerce-shipping-calculator p:last').before(
                    '<p class="form-row form-row-wide cpf_cnpj" id="calc_shipping_cpf_field">' +
                    '<input type="text" class="input-text" value="' + identifier_value + '" placeholder="CPF / CNPJ" name="calc_shipping_cpf" id="calc_shipping_cpf">' +
                    '</p>'
                );
            }

            if ($(elem_identifier).length && $(elem_identifier).val() == '') {
                $(elem_identifier).mask(initial_mask, options);
            }
        }
    );

    $(elem_identifier).mask(initial_mask, options);
})( jQuery );
