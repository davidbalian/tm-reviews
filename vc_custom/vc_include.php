<?php


/**====================================================================
 * ==  Custom VC Params
 * ==  all php files integration
 * ====================================================================*/
foreach(glob(TMREVIEWS_THEME_HELPER_PLUGIN_PATH .'vc_custom/params/*.php') as $custom_params)
{
    require($custom_params);
}

/**====================================================================
==  Include all
==  Shortcodes VC
====================================================================*/
//shortcode_map
foreach(glob(TMREVIEWS_THEME_HELPER_PLUGIN_PATH .'vc_custom/maps-shortcode/*.php') as $shortcodes_map)
{
    require($shortcodes_map);
}

//shortcode
foreach(glob(TMREVIEWS_THEME_HELPER_PLUGIN_PATH .'vc_custom/vc_templates/*.php') as $shortcodes)
{
    require($shortcodes);
}
/*
if ( function_exists( 'vc_shortcodes_theme_templates_dir' ) || function_exists( 'vc_set_template_dir' ) ) {
    $templates_path = TMREVIEWS_THEME_HELPER_PLUGIN_PATH . 'vc_custom/vc_templates/';
    vc_set_shortcodes_templates_dir( $templates_path );
}*/