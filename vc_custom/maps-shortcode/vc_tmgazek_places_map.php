<?php

$array_categories = array();
$taxomony = get_terms(tmreviews_get_post_type() . '-category');
foreach ($taxomony as $t){
    $array_categories[$t->name] = $t->term_id;
}

if (function_exists('vc_map')) {
    vc_map(array(
        'name' => esc_html__('Places', 'tmreviews-themes-helper'),
        'base' => 'vc_tmreviews_places',
        'category' => esc_html__('TM Reviews', 'tmreviews-themes-helper'),
        'icon' => 'tmreviews-places-icon icon-tmreviews-places-vc-icon',
        'controls' => 'full',
        'weight' => 900,
        'params' => array_merge(
            array(
                array(
                    "type"        => "checkbox",
                    "param_name"  => "places_taxes",
                    'class'       => "fl-checkbox-style",
                    "value"       => $array_categories,
                    'std' => 'true',
                ),
                array(
                    'type'              => 'dropdown',
                    'heading'           => esc_html__('Places Style', 'tmreviews-theme-review'),
                    'param_name'        => 'places_style',
                    'value' => array(
                        esc_attr__("Style One", "tmreviews-theme-review")                 => "places-style-one",
                        esc_attr__("Style Two", "tmreviews-theme-review")                 => "places-style-two",
                    ),
                    'std'               => 'places-style-one'
                ),
                array(
                    'type'              => 'tmreviews_image_preview',
                    'param_name'        => 'fl_style_title_preview',
                    'value' => array(
                        esc_attr__("Style One", "tmreviews-theme-review")                 => "places-style-one",
                        esc_attr__("Style Two", "tmreviews-theme-review")                 => "places-style-two",
                    ),
                    'std'               => 'places-style-one'
                ),
            ),

            tmreviews_helping_get_animation_option(), tmreviews_helping_get_design_tab()
        ),
    ));
}

