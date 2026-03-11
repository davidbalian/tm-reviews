<?php
        if (function_exists('vc_map')) {
            vc_map(array(
                'name' => esc_html__('Reviews', 'tmreviews-themes-helper'),
                'base' => 'vc_tmreviews_reviews',
                'category' => esc_html__('TM Reviews', 'tmreviews-themes-helper'),
                'icon' => 'tmreviews-icon icon-tmreviews-vc-icon',
                'controls' => 'full',
                'weight' => 900,
                'params' => array_merge(array(
                    array(
                        'type'              => 'dropdown',
                        'heading'           => esc_html__('Reviews Style', 'tmreviews-theme-review'),
                        'param_name'        => 'reviews_style',
                        'value' => array(
                            esc_attr__("Style One", "tmreviews-theme-review")                 => "review-style-one",
                            esc_attr__("Style Two", "tmreviews-theme-review")                 => "review-style-two",
                            esc_attr__("Style Three", "tmreviews-theme-review")                 => "review-style-three",
                        ),
                        'std'               => 'review-style-one'
                    ),
                    array(
                        'type'              => 'tmreviews_image_preview',
                        'param_name'        => 'fl_style_title_preview',
                        'value' => array(
                            esc_attr__("Style One", "tmreviews-theme-review")                 => "review-style-one",
                            esc_attr__("Style Two", "tmreviews-theme-review")                 => "review-style-two",
                            esc_attr__("Style Three", "tmreviews-theme-review")                 => "review-style-three",
                        ),
                        'std'               => 'review-style-one'
                    ),
                    array(
                        'type'          => 'fl_number',
                        'heading'       => esc_html__('Reviews Count', 'tm-reviews'),
                        'param_name'    => 'reviews_count',
                        'value'         => 6,
                        'min'           => 1,
                        'max'           => 999999,
                        'step'          => 1,
                    ),
                    ), tmreviews_helping_get_animation_option(), tmreviews_helping_get_design_tab()),

            ));
        }

