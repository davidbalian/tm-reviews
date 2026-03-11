<?php
/*
 * Shortcode testimonial Parent
 * */
if ( ! function_exists( 'vc_fl_broker_tables_function' ) ) {
    function vc_fl_broker_tables_function($atts, $content = null)
    {
        $css_classes[] = 'fl-broker-tables-wrapper text-center cf';

        global $fl_helping_responsive_style;

        $atts = vc_map_get_attributes('vc_fl_broker_tables', $atts);

        extract($atts);

        $idf = uniqid('').'-'.rand(100,9999);

        $css_classes[] = 'fl-broker-tables-'.$idf.'';
        $result=$wrapper_attributes[]=$responsive_style=$css=$mr_dots=$css=$content_wrapper_attributes[]='';

        $css_classes[] .= tmreviews_get_css_tab_class($atts);

        if(isset($id) && $id != '') {
            $wrapper_attributes[] .= 'id="'.tmreviews_sanitize_class($id).'"';
        }

        if(isset($class) && $class != '') {
            $css_classes[] .= tmreviews_sanitize_class($class);
        }

        // Responsive CSS Box
        if(isset($custom_responsive_option) && $custom_responsive_option !='off') {
            if( !empty( $responsive_css ) && $responsive_css != '' ) {
                $responsive_id = uniqid('fl-helping-broker-tables-responsive-').'-'.rand(100,9999);
                $column_selector = $responsive_id;
                $responsive_style = tmreviews_helping__addons_get_responsive_style($responsive_css, $column_selector);
                $css_classes[] = $responsive_id;
            }
        }

        // Arrow option
        if ( ! empty( $custom_arrows ) and ($custom_arrows !='off')) {
            $css_classes[] = 'fl-arrows';

            if ( ! empty( $arrow_side ) and ( $arrow_side !='')) {
                $css_classes[] = $arrow_side;
            }

            if ( ! empty( $arrow_position ) and ( $arrow_position !='')) {
                $css_classes[] = $arrow_position;
            }
        }

        // Animation option
        if ( ! empty( $animation ) and ($animation !='none')) {
            $css_classes[] = 'wow '.$animation;

            if ( ! empty( $custom_delay ) and ( $custom_delay !='off')) {
                if ( ! empty( $animation_delay ) and ($animation_delay !='')) {
                    $wrapper_attributes[] = 'data-wow-delay="'.$animation_delay.'ms"';
                }
            }

        }


        $css_class = preg_replace( '/\s+/', ' ', implode( ' ', array_filter( array_unique( $css_classes ) ) ) );

                $result .= '<div class="wrap-broker-table">';
                // Start
                $result .= '<table class="fl-broker-tables ' . esc_attr( trim( $css_class ) ) . '" '. implode( ' ', $wrapper_attributes ).'>';

                if(isset($list_fields) && !empty($list_fields) && function_exists('vc_param_group_parse_atts')) {

                    $list_fields = (array) vc_param_group_parse_atts($list_fields);

                    $result .= '<tr class="fl-broker-tables-head">';

                        $result .= '<td class="fl-broker-col">';
                            $result .= __('Broker', 'tm-reviews');
                        $result .= '</td>';

                        $result .= '<td class="fl-broker-col">';
                            $result .= __('Rating', 'tm-reviews');
                        $result .= '</td>';

                        $result .= '<td class="fl-broker-col">';
                            $result .= __('Regulated', 'tm-reviews');
                        $result .= '</td>';

                        $result .= '<td class="fl-broker-col">';
                            $result .= __('Bonus', 'tm-reviews');
                        $result .= '</td>';

                        $result .= '<td class="fl-broker-col">';
                            $result .= __('Min. Deposit', 'tm-reviews');
                        $result .= '</td>';

                        $result .= '<td class="fl-broker-col">';
                            $result .= __('Avg. Returns', 'tm-reviews');
                        $result .= '</td>';

                    $result .= '</tr>';

                    foreach($list_fields as $fields) {

                        $result .= '<tr class="fl-broker-tables">';

                            $result .= '<td class="fl-broker-tables-field">';
                                $result .= wp_get_attachment_image( $fields['image_id'], 'gazek_size_170x170_crop');
                            $result .= '</td>';

                            $result .= '<td class="fl-broker-tables">';
                                $average = intval($fields['rating']);
                                $i = 1;
                                while ($i <= intval($average)){
                                    $result .= '<i class="fa fa-star" aria-hidden="true"></i>';
                                    $i++;
                                }

                                if(intval($average) < 5){
                                    $asd = 5 - intval($average);
                                    $k = 1;
                                    while ($k <= $asd){
                                        $result .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                                        $k++;
                                    }
                                }

                            $result .= '</td>';

                            $result .= '<td class="fl-broker-tables">';
                                $result .= $fields['regulated'];
                            $result .= '</td>';

                            $result .= '<td class="fl-broker-tables">';
                                $result .= $fields['bonus'];
                            $result .= '</td>';

                            $result .= '<td class="fl-broker-tables">';
                                $result .= $fields['min_deposit'];
                            $result .= '</td>';

                            $result .= '<td class="fl-broker-tables">';
                                $result .= $fields['avg_returns'];
                            $result .= '</td>';


                            $result .= '<td class="fl-broker-tables">';
                                $result .= '<a class="btn-broker1" href="'. esc_url($fields['button_one_link'], 'tm-reviews') .'">' . $fields['button_one_text'] . '</a>';
                            $result .= '</td>';

                        $result .= '<td class="fl-broker-tables">';
                            $result .= '<a class="btn-broker2" href="'. esc_url($fields['button_two_link'], 'tm-reviews') .'">' . $fields['button_two_text'] . '</a>';
                        $result .= '</td>';

                        $result .= '</tr>';
                    }

                }

                $result .= '</table>';

                $result .= '</div>';






        if(isset($css) && $css !='') {
            $result .='<script>'
                . '(function($) {'
                . '$("head").append("<style>'.$css.'</style>");'
                . '})(jQuery);'
                . '</script>';
        }

        // Responsive CSS Box
        if(isset($custom_responsive_option) && $custom_responsive_option !='off') {
            $fl_helping_responsive_style .= $responsive_style;
        }


        ob_start();   ?>

<?php
        $result .= ob_get_clean();

        return $result;
    }
}
add_shortcode('vc_fl_broker_tables', 'vc_fl_broker_tables_function');