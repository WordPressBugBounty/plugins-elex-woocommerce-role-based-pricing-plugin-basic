    jQuery(window).on('load', function () {
        // Ordering
        jQuery('.price_adjustment tbody').sortable({
            items: 'tr',
            cursor: 'move',
            axis: 'y',
            handle: '.sort',
            scrollSensitivity: 40,
            forcePlaceholderSize: true,
            helper: 'clone',
            opacity: 0.65,
            placeholder: 'wc-metabox-sortable-placeholder',
            start: function (event, ui) {
                ui.item.css('baclbsround-color', '#f6f6f6');
            },
            stop: function (event, ui) {
                ui.item.removeAttr('style');
                elex_rp_price_adjustment_row_indexes();
            }
        });

        elex_rp_hide_cart_placeholder_text('#eh_pricing_discount_cart_unregistered_user', '#eh_pricing_discount_cart_unregistered_user_text');
        elex_rp_hide_placeholder_text('#eh_pricing_discount_price_unregistered_user', '#eh_pricing_discount_price_unregistered_user_text');
        elex_rp_hide_user_placeholder_text('#eh_pricing_discount_price_user_role', '#eh_pricing_discount_price_user_role_text');
        elex_rp_hide_user_placeholder_text('#eh_pricing_discount_cart_user_role', '#eh_pricing_discount_cart_user_role_text');
        elex_rp_hide_cart_placeholder_text('#eh_pricing_discount_cart_catalog_mode', '#eh_pricing_discount_cart_catalog_mode_text');
        elex_rp_hide_user_replace_addtocart();
        elex_rp_hide_tax_options_table('#eh_pricing_discount_enable_tax_options', '#tax_options_table');
        elex_rp_replace_addtocart();
        elex_rp_replace_addtocart_catalog();
        elex_rp_price_suffix();


        jQuery('#eh_pricing_discount_cart_unregistered_user').change(function () {
            elex_rp_hide_cart_placeholder_text('#eh_pricing_discount_cart_unregistered_user', '#eh_pricing_discount_cart_unregistered_user_text');
        });
        jQuery('#eh_pricing_discount_cart_catalog_mode').change(function () {
            elex_rp_hide_cart_placeholder_text('#eh_pricing_discount_cart_catalog_mode', '#eh_pricing_discount_cart_catalog_mode_text');
        });

        jQuery('#eh_pricing_discount_price_unregistered_user').change(function () {
            elex_rp_hide_placeholder_text('#eh_pricing_discount_price_unregistered_user', '#eh_pricing_discount_price_unregistered_user_text');
        });
       
        jQuery('#eh_pricing_discount_cart_user_role').change(function () {
            elex_rp_hide_user_placeholder_text('#eh_pricing_discount_cart_user_role', '#eh_pricing_discount_cart_user_role_text');
        });

        jQuery('#eh_pricing_discount_price_user_role').change(function () {
            elex_rp_hide_user_placeholder_text('#eh_pricing_discount_price_user_role', '#eh_pricing_discount_price_user_role_text');
        });

        jQuery('#eh_pricing_discount_replace_cart_user_role').change(function () {
            elex_rp_hide_user_replace_addtocart();
        });

        jQuery('#eh_pricing_discount_enable_tax_options').change(function () {
            elex_rp_hide_tax_options_table('#eh_pricing_discount_enable_tax_options', '#tax_options_table');
        });

        jQuery('#eh_pricing_discount_replace_cart_unregistered_user').change(function () {
            elex_rp_replace_addtocart();
        });
        jQuery('#eh_pricing_discount_replace_cart_catalog_mode').change(function () {
            elex_rp_replace_addtocart_catalog();
        });

        jQuery('#eh_pricing_discount_enable_price_suffix').change(function () {
            elex_rp_price_suffix();
        });
        jQuery('label[for="eh_pricing_discount_replace_place_order_btn_text"],label[for="eh_pricing_discount_hide_place_order_btn_for_users"],label[for="eh_pricing_discount_unregistered_hide_place_order"],label[for="eh_pricing_discount_unregistered_replace_place_order"]').append('<span style="vertical-align: super;color:green;font-size:12px">[Premium]</span>');
        jQuery('label[for="eh_pricing_discount_hide_payment_gateways"]').append('<span style="vertical-align: super;color:green;font-size:12px">[Premium]</span>');
        jQuery('label[for="eh_product_choose_sale_regular"]').append('<span style="vertical-align: super;color:green;font-size:12px">[Premium]</span>');
        function elex_rp_price_adjustment_row_indexes() {
            jQuery('.price_adjustment tbody tr').each(function (index, el) {
                jQuery('input.order', el).val(parseInt(jQuery(el).index('.price_adjustment tr')));
            });
        }
        ;

        function elex_rp_hide_placeholder_text(check, hide_field) {
            if (jQuery(check).is(":checked")) {
                jQuery(hide_field).closest("tr").show();
            } else {
                jQuery(hide_field).closest("tr").hide();
            }
        }
        ;

        function elex_rp_hide_cart_placeholder_text(check, hide_field) {
            if (jQuery(check).is(":checked")) {
                jQuery(hide_field).closest("tr").show();

            } else {
                jQuery(hide_field).closest("tr").hide();

            }
        }
        ;

        function elex_rp_hide_user_placeholder_text(check, hide_field) {
            options = jQuery(check).val();
            if (options != null) {
                jQuery(hide_field).closest("tr").show();
            } else {
                jQuery(hide_field).closest("tr").hide();
            }
        }
        ;


        function elex_rp_hide_user_replace_addtocart() {
            options = jQuery('#eh_pricing_discount_replace_cart_user_role').val();
            if (options != null) {
                jQuery('#eh_pricing_discount_replace_cart_user_role_text_product').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_user_role_text_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_user_role_url_shop').closest("tr").show();
            } else {
                jQuery('#eh_pricing_discount_replace_cart_user_role_text_product').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_user_role_text_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_user_role_url_shop').closest("tr").hide();
            }
        }
        ;
        function elex_rp_hide_tax_options_table(check, hide_field) {
            if (jQuery(check).is(":checked")) {
                jQuery(hide_field).show();
            } else {
                jQuery(hide_field).hide();
            }
        }
        ;
        //---------------------------edited by nandana
        //To show/hide placeholder text and url for replace add to cart button for unregistered user
        function elex_rp_replace_addtocart() {
            if (jQuery('#eh_pricing_discount_replace_cart_unregistered_user').is(":checked")) {
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_text_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_url_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_text_product').closest("tr").show();
            } else {
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_text_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_url_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_unregistered_user_text_product').closest("tr").hide();
            }
        }
        ;
        //To show/hide placeholder text and url for replace add to cart button for Catalog mode
        function elex_rp_replace_addtocart_catalog() {
            if (jQuery('#eh_pricing_discount_replace_cart_catalog_mode').is(":checked")) {
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_text_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_url_shop').closest("tr").show();
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_text_product').closest("tr").show();
            } else {
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_text_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_url_shop').closest("tr").hide();
                jQuery('#eh_pricing_discount_replace_cart_catalog_mode_text_product').closest("tr").hide();
            }
        }
        ;
        //----------------------------
        function elex_rp_price_suffix() {
            options = jQuery('#eh_pricing_discount_enable_price_suffix').val();
            if (options === 'general') {
                jQuery('#eh_pricing_discount_price_general_price_suffix').closest("tr").show();
                jQuery('#price_suffix_table').hide();
            } else if (options === 'role_specific') {
                jQuery('#eh_pricing_discount_price_general_price_suffix').closest("tr").hide();
                jQuery('#price_suffix_table').show();
            } else {
                jQuery('#eh_pricing_discount_price_general_price_suffix').closest("tr").hide();
                jQuery('#price_suffix_table').hide();
            }
        }
        ;

    });


