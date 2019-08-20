/*
 * Post Bulk Edit Script
 * Hooks into the inline post editor functionality to extend it to our custom metadata
 */

jQuery(document).ready(function($){

    //Prepopulating our quick-edit post info
    var $inline_editor = inlineEditPost.edit;
    inlineEditPost.edit = function(id){

        //call old copy 
        $inline_editor.apply( this, arguments);

        //our custom functionality below
        var post_id = 0;
        if( typeof(id) == 'object'){
            post_id = parseInt(this.getId(id));
        }

        //if we have our post
        if(post_id != 0){

            //find our row
            var $row    = $('#edit-' + post_id);
            var $fields = $('.astra-bulk-edit-field-' + post_id);

            if ( $fields.length > 0 ) {

                $fields.each(function(i) {
                    
                    var field       = $(this);
                    var field_name  = field.attr('data-name');
                    var field_val   = field.text();
                    
                    var new_field       = $row.find( '#' + field_name );
                    var new_field_type  = new_field.attr('type');
                    var new_field_tag   = new_field.prop("tagName");

                    if ( 'SELECT' == new_field_tag ) {
                        new_field.val( field_val );

                        if ( '' == field_val && 'adv-header-id-meta' !=  field_name ) {
                            new_field.val( 'no-change' );
                        }
                    }else if ( 'checkbox' == new_field_type ) {

                        if ( 'disabled' == field_val || 'on' == field_val ) {
                            new_field.prop( "checked", true );
                        }
                    }
                });

                toggleStickyHeader();
            }
        }
    }

    jQuery( "#bulk_edit" ).on( "click", function(e) {

        // e.preventDefault();

        var bulk_row = jQuery( "#bulk-edit" );
        var post_ids = new Array();
        bulk_row.find( "#bulk-titles" ).children().each( function() {
            post_ids.push( jQuery( this ).attr( "id" ).replace( /^(ttle)/i, "" ) );
        });

        var form = bulk_row.closest('form');
        var post_data = form.serialize();

        post_data += '&action=astra_save_post_bulk_edit';

        jQuery.ajax({
            url: ajaxurl,
            type: "POST",
            async: false,
            cache: false,
            data: post_data,
            type: 'POST',
            dataType: 'json',
        })
        .done(function() {
            toggleStickyHeader();
            toggleStickyHeaderVisibility();
        })

    });

    jQuery( ".inline-edit select[name=stick-header-meta]" ).on( "change", function(e) {
        toggleStickyHeader();
        toggleStickyHeaderVisibility();
    });
    toggleStickyHeader();
    toggleStickyHeaderVisibility();

    var sticky_above_header = 'false';
    var sticky_main_header = 'false';
    var sticky_below_header = 'false';
    function toggleStickyHeader() {

        $( 'select[name=stick-header-meta]' ).each(function(index, el) {
            var value = $( el ).val() || '';
             if ( 'enabled' == value ) {
                $( el ).parents( '.inline-edit-col' ).find(".sticky-header-above-stick-meta").slideDown();
                $( el ).parents( '.inline-edit-col' ).find(".sticky-header-main-stick-meta").slideDown();
                $( el ).parents( '.inline-edit-col' ).find(".sticky-header-below-stick-meta").slideDown();
            } else {
                $( el ).parents( '.inline-edit-col' ).find(".sticky-header-above-stick-meta").slideUp();
                $( el ).parents( '.inline-edit-col' ).find(".sticky-header-main-stick-meta").slideUp();
                $( el ).parents( '.inline-edit-col' ).find(".sticky-header-below-stick-meta").slideUp();
            }
        });


        $('#ast-above-header-display').on("change", function ( e ) {
            var value = $( e.target ).val() || '';
            if ( 'disabled' == value ) {
                sticky_above_header = 'true';
                $(".sticky-header-above-stick-meta").slideUp();
            } else {
                sticky_above_header = 'false';
                $(".sticky-header-above-stick-meta").slideDown();
            }
            toggleStickyHeaderVisibility();
        });

        $('#ast-main-header-display').on("change", function ( e ) {
            var value = $( e.target ).val() || '';
            if ( 'disabled' == value ) {
                sticky_main_header = 'true';
                $(".sticky-header-main-stick-meta").slideUp();
            } else {
                sticky_main_header = 'false';
                $(".sticky-header-main-stick-meta").slideDown();
            }
            toggleStickyHeaderVisibility();
        });  

        $('#ast-below-header-display').on("change", function ( e ) {
            var value = $( e.target ).val() || '';
            if ( 'disabled' == value ) {
                sticky_below_header = 'true';
                $(".sticky-header-below-stick-meta").slideUp();
            } else {
                sticky_below_header = 'false';
                $(".sticky-header-below-stick-meta").slideDown();
            }
            toggleStickyHeaderVisibility();
        }); 
    }

    function toggleStickyHeaderVisibility() {
        if( 'true' == sticky_above_header && 'true' == sticky_main_header && 'true' == sticky_below_header ) {
            $(".stick-header-meta-visibility").slideUp();
        }
        else {
            $(".stick-header-meta-visibility").slideDown();
        }
    }
});