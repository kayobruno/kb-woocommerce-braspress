(function($) {
    'use strict';

    let mask = '00000-000';
    let elem_shipping_postcode = '#calc_shipping_postcode',
        options 	= {
            onKeyPress: function (identifier, e, field, options) {
                $(elem_shipping_postcode).mask(mask, options);
            }
        }
    ;

    $(elem_shipping_postcode).mask(mask, options);
})(jQuery);
