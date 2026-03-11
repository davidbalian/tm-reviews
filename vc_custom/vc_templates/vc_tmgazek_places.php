<?php
/*
 * Shortcode reviews
 * */
if ( ! function_exists( 'vc_tmreviews_places_function' ) ) {
    function vc_tmreviews_places_function($atts, $content = null)
    {

        $css_classes []           = 'tmreviews-places';

        global $tmreviews_helping_responsive_style, $tmreviews_helping_css_style;
        $atts = vc_map_get_attributes('vc_tmreviews_places', $atts);
        extract($atts);
        $result = $wrapper_attributes[] = $responsive_style = $css='';

        $idf = uniqid('').'-'.rand(100,9999);

        $css_classes[] .= 'tmreviews-places-'.$idf;

        $css_classes[] .= tmreviews_get_css_tab_class($atts);

        if(isset($id) && $id != '') {
            $wrapper_attributes[] = 'id="'.tmreviews_sanitize_class($id).'"';
        }

        if(isset($class) && $class != '') {
            $css_classes[] = tmreviews_sanitize_class($class);
        }


        // Responsive CSS Box
        if(isset($custom_responsive_option) && $custom_responsive_option !='off') {
            if( !empty( $responsive_css ) && $responsive_css != '' ) {
                $responsive_id = $idf = uniqid('tmreviews-helping-alert-responsive-').'-'.rand(100,9999);
                $column_selector = $responsive_id;
                $responsive_style = tmreviews_helping__addons_get_responsive_style($responsive_css, $column_selector);
                $css_classes[] = $responsive_id;
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

        if ( ! empty( $animation_text ) and ($animation_text !='')) {
            $wrapper_attributes[] = 'data-text="'.$animation_text.'"';
        }

        // Custom Typography
        if(isset($text_font_options) && $text_font_options != ''
            || isset($custom_text_google_fonts) && $custom_text_google_fonts != 'off'
            || isset($text_custom_fonts) && $text_custom_fonts != '' ) {
            $text_options = _tmreviews_parse_text_params($text_font_options,$custom_text_google_fonts, $text_custom_fonts);
        }

        if(!isset($places_taxes)){
        }

        $places_taxes_arr = explode(",", $places_taxes);

        $css_class = preg_replace( '/\s+/', ' ', implode( ' ', array_filter( array_unique( $css_classes ) ) ) );

        $terms = get_terms( array(
                'taxonomy' => tmreviews_get_post_type() . '-category',
                'hide_empty' => false,
                'term_taxonomy_id' => $places_taxes_arr
        ) );

        ob_start();
        if (isset($places_style) && $places_style == 'places-style-two'){
            echo '<div class="fl-places-cat-contain fl-places-style-two">';
            $i = 1;
            $k = 1;
            $len = count($terms);
            foreach ($terms as $term){
                $cat_bg_id = get_field('cat_image', $term);
                $cat_bg = wp_get_attachment_image_url($cat_bg_id, 'gazek_size_570x400_crop');
                $results = $term->count . __(' Results', 'tm-reviews');
                $cat_review_count = 0;
                $args = array(
                    'post_type' => tmreviews_get_post_type(),
                    'tax_query' => array(
                        array(
                            'taxonomy' => tmreviews_get_post_type() . '-category',
                            'field' => 'term_id',
                            'terms' => $term->term_id
                        )
                    )
                );

                $services = get_posts($args);
                foreach ($services as $s){
                    $cat_review_count += $s->comment_count;
                }
                if($i == 1){
                    echo '<div class="fl-row">';
                }
                echo '<div class="fl-places-cat" >';

                if (isset($cat_bg) && $cat_bg !=''){
                    $cat_bg_style = 'background-image: url('.$cat_bg.'); background-size: cover;';
                    echo '<a class = "fl-places-cat-contain" style="'.$cat_bg_style.'" href="' . get_term_link( $term, $taxonomy = 'places-taxonomy' ) . '"></a>';
                } else {
                    $cat_bg_style = 'background-image: url('.get_template_directory_uri().'/assets/css/images/cat045.jpg); background-size: cover;';
                    echo '<a class = "fl-places-cat-contain" style="'.$cat_bg_style.'" href="' . get_term_link( $term, $taxonomy = 'places-taxonomy' ) . '"></a>';
                }

                echo '<div class="fl-places-meta">';
                echo '<div class="fl-service-bottom">';
                echo '<span class="fl-places-results">' . $results . '</span>';
                echo '<span class="fl-places-reviews-count"><i class="fa fa-comment" aria-hidden="true"></i>' . $cat_review_count . __(' Reviews', 'tm-reviews')  .'</span>';
                echo '</div>';
                echo '<a class="fl-places-title fl-text-title-style" href="' . get_term_link( $term, $taxonomy = 'places-taxonomy' ) . '">' . $term->name . '</a>';

                echo '</div>';

                echo '</div>';

                $i++;
                if($i == 4 || $k == $len){
                    echo '</div>';
                    $i = 1;
                }
                $k++;


            }
            echo '</div>';
        } elseif (isset($places_style) && $places_style == 'places-style-one'){
            echo '<div class="fl-places-cat-contain fl-places-style-one">';
            $i = 1;
            $k = 1;
            $len = count($terms);
            foreach ($terms as $term){
                $cat_bg_id = get_field('cat_image', $term);
                $cat_bg = wp_get_attachment_image_url($cat_bg_id, 'gazek_size_570x400_crop');
                $results = $term->count . __(' Results', 'tm-reviews');
                $cat_review_count = 0;
                $args = array(
                    'post_type' => tmreviews_get_post_type(),
                    'tax_query' => array(
                        array(
                            'taxonomy' => tmreviews_get_post_type() . '-category',
                            'field' => 'term_id',
                            'terms' => $term->term_id
                        )
                    )
                );

                $services = get_posts($args);
                foreach ($services as $s){
                    $cat_review_count += $s->comment_count;
                }
                if($i == 1){
                    echo '<div class="fl-row">';
                }
                echo '<div class="fl-places-cat" >';

                if (isset($cat_bg) && $cat_bg !=''){
                    $cat_bg_style = 'background-image: url('.$cat_bg.'); background-size: cover;';
                    echo '<a class = "fl-places-cat-contain" style="'.$cat_bg_style.'" href="' . get_term_link( $term, $taxonomy = 'places-taxonomy' ) . '"></a>';
                }

                echo '<div class="fl-places-meta">';
                echo '<span class="fl-places-results">' . $results . '</span>';
                echo '<div class="fl-service-bottom">';
                echo '<a class="fl-places-title" href="' . get_term_link( $term, $taxonomy = 'places-taxonomy' ) . '">' . $term->name . '</a>';
                echo '<span class="fl-places-reviews-count"><i class="fa fa-comment" aria-hidden="true"></i>' . $cat_review_count . esc_attr(' Reviews')  .'</span>';
                echo '</div>';
                echo '</div>';

                echo '</div>';

                $i++;
                if($i == 4 || $k == $len){
                    echo '</div>';
                    $i = 1;
                }
                $k++;


            }

            echo '</div>';
        }



        if(isset($css) && $css !='') {
            $result .='<script>'
                . '(function($) {'
                . '$("head").append("<style>'.$css.'</style>");'
                . '})(jQuery);'
                . '</script>';
        }

        // Responsive CSS Box
        if(isset($custom_responsive_option) && $custom_responsive_option !='off') {
            $tmreviews_helping_responsive_style .= $responsive_style;
        }

       ?>

        <script>
            jQuery.noConflict()(function($) {
                var anim_text = $(".tmreviews_-animation-typing-<?php echo esc_js($idf); ?>").data('text');
                $(".tmreviews_-animation-typing-<?php echo esc_js($idf); ?>").one('inview', function() {
                    var typed = new Typed('.tmreviews_-animation-typing-<?php echo esc_js($idf); ?> .tmreviews_-text-wrapper', {
                        strings: [anim_text],
                        typeSpeed: <?php
                        if(isset($typing_speed) && $typing_speed !='') {
                            echo esc_js($typing_speed);
                        } else {
                            echo 30;
                        }; ?>,
                        loop: false,
                        contentType: 'html'
                    });
                });
            });
        </script>
<?php
        $result .= ob_get_clean();

        return $result;

    }
}
add_shortcode('vc_tmreviews_places', 'vc_tmreviews_places_function');
