(function($){
    function enablecustomiconpickerFor($el) {
        $el.find('.acf-iconpicker').each(function(){
            if ( !$(this).parents('.acf-clone').length ){
                // Let's iconpick!!!
                $(this).customiconpicker();
            }
        });
    }
    if( typeof acf.add_action !== 'undefined' ) {
        // ACF5
        acf.add_action('ready append', function( $el ){
            enablecustomiconpickerFor($el);
        });
    } else {
        // ACF4
        $(document).on('acf/include_fields acf/setup_fields', function(e, postbox){
            enablecustomiconpickerFor($(postbox));
        });
    }
})(jQuery);
