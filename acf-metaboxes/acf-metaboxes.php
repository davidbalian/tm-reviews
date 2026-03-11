<?php

if (!function_exists('revus_get_theme_mod')) {
function revus_get_theme_mod($name = null, $use_acf = null, $postId = null, $acf_name = null)
{
    $value = null;

    // try get value from meta box
    if ($use_acf) {
        $value = tm_reviews_get_metabox($acf_name ? $acf_name : $name, $postId);
    }

    // get value from options
    if (($value === null || $value === 'default')) {
        if (class_exists('REVUS_Options')) {
            $value = REVUS_Options::get_option($name);
        }
    }

    $value = apply_filters('revus_filter_get_theme_mod', $value, $name);
    return $value;
}
}
// get metabox
if (!function_exists( 'tm_reviews_get_metabox' )):
    function tm_reviews_get_metabox($name = null, $postId = null)
    {
        $value = null;

        // try get value from meta box
        if (function_exists('get_field')) {
            if ($postId == null) {
                $postId = get_the_ID();
            }
            $value = get_field($name, $postId);
        }

        return $value;
    }
endif;


if (!function_exists('tmreviews_get_theme_mod')) {
    function tmreviews_get_theme_mod($name = null, $use_acf = null, $postId = null, $acf_name = null)
    {
        $value = null;

        // try get value from meta box
        if ($use_acf) {
            $value = gazek_get_metabox($acf_name ? $acf_name : $name, $postId);
        }



        $value = apply_filters('gazek_filter_get_theme_mod', $value, $name);
        return $value;
    }
}

