(function($) {
    'use strict';
    let elem_identifier = 'input.identifier',
        elem_postcode = 'input.origin_postcode',
        options_identifier = {
            onKeyPress: function (identifier, e, field, options) {
                let masks = ['000.000.000-000', '00.000.000/0000-00'],
                    mask  = (identifier.length > 14) ? masks[1] : masks[0];
                $(elem_identifier).mask(mask, options);
            }
        },
        initial_identifier_mask = '000.000.000-000000';
    $(elem_identifier).mask(initial_identifier_mask, options_identifier);
    $(elem_postcode).mask('00000-000');
})(jQuery);
