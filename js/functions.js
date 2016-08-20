jQuery(document).ready(function(){

    jQuery( '.et_bloom_submit_subscription' ).each(function( index ) {
        jQuery( this ).parents('form').on('submit', function(event){
            return false;
        });
    });


    jQuery('.et_bloom_custom_html_form form').on('submit', function(event){
        event.preventDefault();

        // if form has action
        var thisForm = jQuery(this).find('form');

        if ( thisForm.attr('action').length && thisForm.attr('action').length != '' ){

            var redirectURL = '';
            var bloomRedirections = bloomRedirectionSettings['redirections'];
            if (optin_id in bloomRedirections){
                redirectURL = bloomRedirections[optin_id];
            }else {
                redirectURL = bloomRedirectionSettings['site_url'];
            }

            jQuery.ajax({
                type: 'POST',
                url: jQuery(this).attr('action'),
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    window.location.href = redirectURL;
                }
            });
            return false;
        }

    });


    jQuery('.et_bloom_submit_subscription').on('click', function(event){
        event.preventDefault();

        // if form has action
        var thisForm = jQuery(this).parents('form');
        var thisFormActionAttr = jQuery(thisForm).attr('action');

        // For some browsers, `attr` is undefined; for others, `attr` is false. Check for both.
        if (typeof thisFormActionAttr !== typeof undefined && thisFormActionAttr !== false) {
            var redirectURL = '';
            var bloomRedirections = bloomRedirectionSettings['redirections'];
            if (optin_id in bloomRedirections){
                redirectURL = bloomRedirections[optin_id];
            }else {
                redirectURL = bloomRedirectionSettings['site_url'];
            }

            jQuery.ajax({
                type: 'POST',
                url: thisFormActionAttr,
                data: form.serialize(),
                dataType: 'json',
                success: function(response) {
                    window.location.href = redirectURL;
                }
            });
            return false;
        }

        if ( !jQuery( this ).hasClass('et_bloom_submit_subscription_locked') ){
            aw_perform_subscription( jQuery( this ), '', '', '', '' );
        }else {
            var current_container = jQuery( this ).closest( '.et_bloom_locked_container' ),
                container_id = current_container.data( 'container_id' ),
                page_id = current_container.data( 'page_id' ),
                optin_id = current_container.data( 'optin_id' );
                aw_perform_subscription( jQuery( this ), current_container, container_id, page_id, optin_id );
        }
        return false;
    });

    function aw_perform_subscription( this_button, current_container, container_id, locked_page_id, locked_optin_id ) {
        var this_form = this_button.parent(),
            list_id = this_button.data( 'list_id' ),
            account_name = this_button.data( 'account' ),
            service = this_button.data( 'service' ),
            name = this_form.find( '.et_bloom_subscribe_name input' ).val(),
            last_name = undefined != this_form.find( '.et_bloom_subscribe_last input' ).val() ? this_form.find( '.et_bloom_subscribe_last input' ).val() : '',
            email = this_form.find( '.et_bloom_subscribe_email input' ).val(),
            page_id = this_button.data( 'page_id' ),
            optin_id = this_button.data( 'optin_id' ),
            disable_dbl_optin = this_button.data( 'disable_dbl_optin' );

        this_form.find( '.et_bloom_subscribe_email input' ).removeClass( 'et_bloom_warn_field' );

        if ( '' == email ) {
            this_form.find( '.et_bloom_subscribe_email input' ).addClass( 'et_bloom_warn_field' );
        } else {
            var redirectURL = '';
            var bloomRedirections = bloomRedirectionSettings['redirections'];
            if (optin_id in bloomRedirections){
                redirectURL = bloomRedirections[optin_id];
            }else {
                redirectURL = bloomRedirectionSettings['site_url'];
            }
            $subscribe_data = JSON.stringify({ 'list_id' : list_id, 'account_name' : account_name, 'service' : service, 'name' : name, 'email' : email, 'page_id' : page_id, 'optin_id' : optin_id, 'last_name' : last_name, 'dbl_optin' : disable_dbl_optin });
            jQuery.ajax({
                type: 'POST',
                dataType: 'json',
                url: bloomSettings.ajaxurl,
                data: {
                    action : 'bloom_subscribe',
                    subscribe_data_array : $subscribe_data,
                    subscribe_nonce : bloomSettings.subscribe_nonce
                },
                beforeSend: function( data ) {
                    this_button.addClass( 'et_bloom_button_text_loading' );
                    this_button.find( '.et_bloom_subscribe_loader' ).css( { 'display' : 'block' } );
                },
                success: function( data ) {
                    this_button.removeClass( 'et_bloom_button_text_loading' );
                    this_button.find( '.et_bloom_subscribe_loader' ).css( { 'display' : 'none' } );
                    if ( data ) {
                        if ( '' != current_container && ( data.success || 'Invalid email' != data.error ) ) {
                            unlock_content( current_container, container_id, locked_page_id, locked_optin_id );
                        } else {
                            if ( data.error ) {
                                this_form.find( '.et_bloom_error_message' ).remove();
                                this_form.prepend( '<h2 class="et_bloom_error_message">' + data.error + '</h2>' );
                                this_form.parent().parent().find( '.et_bloom_form_header' ).addClass( 'et_bloom_with_error' );
                            }
                            if ( data.success && '' == current_container ) {
                                window.location.href = redirectURL;
                                this_form.parent().find( '.et_bloom_success_message' ).addClass( 'et_bloom_animate_message' );
                                this_form.parent().find( '.et_bloom_success_container' ).addClass( 'et_bloom_animate_success' );
                                this_form.remove();
                            }
                        }
                    }
                }
            });
        }
    }
});

