<?php
    if (function_exists('vc_map')) {
        vc_map(array(
            'name' => esc_html__('Broker Tables', 'tm-reviews'),
            'base' => 'vc_fl_broker_tables',
            'category' => esc_html__('TM Reviews', 'tm-reviews'),
            'icon' => 'tmreviews-places-icon icon-tmreviews-places-vc-icon',
            'controls' => 'full',
            'weight' => 900,
            'params' => array_merge(array(

                array(
                        'type'				=> 'param_group',
                        'heading'			=> esc_html__('Layers of Broker Tables', 'tm-reviews'),
                        'param_name'		=> 'list_fields',
                        'params'			=> array(
                            array(
                                'type'			=> 'attach_image',
                                'heading'		=> esc_html__('Upload Logo:', 'tm-reviews'),
                                'param_name'	=> 'image_id',
                            ),
                            array(
                                'type'          => 'fl_radio_advanced',
                                'heading'       => __('Rating', 'tm-reviews'),
                                'param_name'    => 'rating',
                                'description'   => '',
                                'options' => array(
                                    esc_attr__("1", "fl-themes-helper")                  => "1",
                                    esc_attr__("2", "fl-themes-helper")                  => "2",
                                    esc_attr__("3", "fl-themes-helper")                  => "3",
                                    esc_attr__("4", "fl-themes-helper")                  => "4",
                                    esc_attr__("5", "fl-themes-helper")                  => "5",
                                ),
                            ),
                            array(
                                'type'          => 'textfield',
                                'heading'       => esc_html__('Regulated', 'tm-reviews'),
                                'param_name'    => 'regulated',
                                'value'         => 'ASIC',
                                'description'   => '',
                            ),
                            array(
                                'type'          => 'textfield',
                                'heading'       => esc_html__('Bonus', 'tm-reviews'),
                                'param_name'    => 'bonus',
                                'value'         => 'up to 100%',
                                'description'   => '',
                            ),
                            array(
                                'type'          => 'textfield',
                                'heading'       => esc_html__('Min. Deposit', 'tm-reviews'),
                                'param_name'    => 'min_deposit',
                                'value'         => '$100',
                                'description'   => '',
                            ),
                            array(
                                'type'          => 'textfield',
                                'heading'       => esc_html__('Avg. Returns', 'tm-reviews'),
                                'param_name'    => 'avg_returns',
                                'value'         => '70% - 90%',
                                'description'   => '',
                            ),


                            //Buttons
                            array(
                                'type'          => 'textfield',
                                'heading'       => esc_html__('Button One Text', 'tm-reviews'),
                                'param_name'    => 'button_one_text',
                                'value'         => 'Review',
                                'description'   => '',
                            ),
                            array(
                                'type'          => 'textfield',
                                'heading'       => esc_html__('Button One Link', 'tm-reviews'),
                                'param_name'    => 'button_one_link',
                                'value'         => '#',
                                'description'   => '',
                            ),


                            array(
                                'type'          => 'textfield',
                                'heading'       => esc_html__('Button Two Text', 'tm-reviews'),
                                'param_name'    => 'button_two_text',
                                'value'         => 'Apply Now',
                                'description'   => '',
                            ),
                            array(
                                'type'          => 'textfield',
                                'heading'       => esc_html__('Button Two Link', 'tm-reviews'),
                                'param_name'    => 'button_two_link',
                                'value'         => '#',
                                'description'   => '',
                            ),
                        ),
                    ),


            ),  tmreviews_helping_get_animation_option(), tmreviews_helping_get_design_tab()),

        ));
    }
