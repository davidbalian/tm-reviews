<?php
/**====================================================================
==  ICON VC
====================================================================*/
require 'icon/vc_icon.php';

/**====================================================================
==  Shortcode_extend
====================================================================*/

add_action('vc_before_init', 'tmreviews_shortcode_extend');

if(!function_exists('tmreviews_shortcode_extend')){
    function tmreviews_shortcode_extend()
    {
        if ( class_exists( 'WPBakeryShortCodesContainer' ) )
        {
            class WPBakeryShortCode_Vc_TMReviews__Portfolio_Info_Table                        extends WPBakeryShortCodesContainer {}

        }
    }
}

/**====================================================================
==  VC Plugin Scripts
====================================================================*/
add_action('wp_enqueue_scripts', 'tmreviews_short_code_scripts');
if ( !function_exists( 'tmreviews_short_code_scripts' ) )
{
    function tmreviews_short_code_scripts()
    {

        wp_enqueue_style('fl-font-vc', plugin_dir_url( __FILE__ ). '/icon/icon_assets/css/fl-icon.min.css', array(),'1.0');

        wp_enqueue_style('places-vc', plugin_dir_url( __FILE__ ). '/css/places_styles.css', array(),'1.0');

        wp_enqueue_script('fl-vc-custom',plugin_dir_url( __FILE__ ).'js/vc_custom.js', false, null , true);

        wp_register_script('fl-vc-google-maps',plugin_dir_url( __FILE__ ).'../assets/js/save_js/gmap3.min.js', false, null , true);

    }
}
/**====================================================================
==  Update Custom short code
====================================================================*/
//
add_action( 'vc_before_init', 'tmreviews_vc_before_init_actions' );
function tmreviews_vc_before_init_actions()
{
    if( function_exists('vc_set_shortcodes_templates_dir') )
    {
        vc_set_shortcodes_templates_dir( plugin_dir_path(__FILE__) .'custom_vc_shortcode');
    }
}








