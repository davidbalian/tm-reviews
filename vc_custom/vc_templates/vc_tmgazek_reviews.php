<?php
/*
 * Shortcode reviews
 * */
if ( ! function_exists( 'vc_tmreviews_reviews_function' ) ) {
    function vc_tmreviews_reviews_function($atts, $content = null)
    {

        $css_classes []           = 'tmreviews_-reviews';

        global $tmreviews__helping_responsive_style;
        $atts = vc_map_get_attributes('vc_tmreviews_reviews', $atts);
        extract($atts);
        $result = $wrapper_attributes[] = $responsive_style = $css='';

        global $tmreviews_helping_responsive_style, $tmreviews_helping_css_style;


        $idf = uniqid('').'-'.rand(100,9999);

        $css_classes[] .= 'tmreviews_-reviews-'.$idf;

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
                $responsive_id = $idf = uniqid('tmreviews_-helping-alert-responsive-').'-'.rand(100,9999);
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

        $css_class = preg_replace( '/\s+/', ' ', implode( ' ', array_filter( array_unique( $css_classes ) ) ) );
        ob_start();
        $comments = get_comments(array('post_type' => tmreviews_get_post_type(), 'status' => 'approve', 'number' => $reviews_count));
        if(isset($reviews_style) && $reviews_style == 'review-style-one'){
            echo '<div class="fl-reviews-slider fl-reviews-style-one-' . $idf . '">';
            if(count($comments) < 3){
                $slidestoshow = count($comments);
            }else{
                $slidestoshow = 3;
            }
            if(isset($comments) && !empty($comments)){
                foreach ($comments as $c){
                    $post_id = $c->comment_post_ID;

                    //Comment
                    $title_comment = get_comment_meta($c->comment_ID, 'tmreviews_review_title', true);
                    $text_comment = $c->comment_content;
                    $comment_date = get_comment_date('F, j, Y', $c->comment_ID);

                    //Author
                    $author_id = get_post_field( 'post_author', $post_id );
                    $avatar = get_avatar($author_id);
                    $author_name = get_the_author_meta( 'display_name', $author_id );

                    //Category
                    $categories = get_the_terms( $post_id, tmreviews_get_post_type() . '-category' );
                    $categories_html = '';

                    if(isset($categories) && !empty($categories)){
                        foreach ($categories as $cat){
                            if (!next( $categories )){
                                $categories_html .= '<a href="' . esc_url(get_term_link($cat, 'places-taxonomy'), 'tmreviews') . '">' . $cat->name . '</a>';
                            } else {
                                $categories_html .= '<a href="' . esc_url(get_term_link($cat, 'places-taxonomy'), 'tmreviews') . '">' . $cat->name . ', </a>';
                            }
                        }
                    }


                    echo '<div class="fl-places-slide">';
                        echo '<div class="fl-places-slide-top">';
                            echo '<div class="fl-places-author-avatar">';
                                echo $avatar;
                            echo '</div>';
                        echo '<div class="fl-places-average-meta">';
                           // echo tmreviews_average_rating($post_id);
                            $rate =  get_comment_meta($c->comment_ID, 'rating', true);
                            $i = 1;
                            echo '<span class="fl-average-icons">';

                            while ($i <= 5){
                                if ($i <=intval($rate)){
                                    echo '<i class="fa fa-star" aria-hidden="true"></i>';

                                } else {
                                    echo '<i class="fa fa-star-o" aria-hidden="true"></i>';
                                }
                                $i++;
                            }
                            echo '</span>';
                            echo '<span class="fl-average-text">'. __('Rating ', 'tm-reviews') .$rate. '/5.0</span>';
                           // echo '<div class="fl-places-average-cat">';
                             //   echo $categories_html;
                            //echo '</div>';
                        echo '</div>';
                        echo '</div>';

                        echo '<div class="fl-places-slide-main">';
                            echo '<span class="fl-review-title">"' . $title_comment . '"</span>';
                            echo '<div class="fl-review-author-contain">';
                                echo '<span class="fl-review-author-name">' . $author_name . ': </span> ';
                                echo '<a href="'. esc_url(get_post_permalink($post_id)) . '">' . get_the_title($post_id) . '</a>';
                            echo '</div>';
                            echo '<span class="fl-review-text">' .  tmreviews_limit_excerpt_search(20, $text_comment) . '</span>';

                        echo '</div>';

                        echo '<div class="fl-places-slide-bottom">';
                            echo '<a class="fl-review-button" href="'. esc_url(get_post_permalink($post_id)) . '">' . __('Read review', 'tm-reviews') . '</a>';
                            echo '<span class="fl-review-date-contain"><span class="fl-review-date-text">' . __('Review Published: ', 'tm-reviews') . '</span><span class="fl-review-date">' . $comment_date . '</span>' . '</span>';
                        echo '</div>';
                    echo '</div>';

                }
            }
            echo '</div>';
            ?>
            <script>
                jQuery.noConflict()(function($) {



                    jQuery('.fl-reviews-style-one-<?php echo $idf;?>').slick({
                        dots: true,
                        speed: 300,
                        slidesToShow: <?php echo $slidestoshow?>,
                        slidesToScroll: 3,
                        centerMode: true,
                        arrows: false,
                        variableWidth: true,
                        responsive: [
                            {
                                breakpoint: 768,
                                settings: {
                                    slidesToShow: 1,
                                    slidesToScroll: 1
                                }
                            },
                        ]
                    });



                });
            </script>
            <?php

        } elseif (isset($reviews_style) && $reviews_style == 'review-style-two'){
            echo '<div class="fl-reviews-style-two">';
            $i = 1;
            $k = 1;
            $len = count($comments);
            foreach ($comments as $c){
                $post_id = $c->comment_post_ID;

                //Comment
                $title_comment = get_comment_meta($c->comment_ID, 'tmreviews_review_title', true);
                $text_comment = $c->comment_content;
                $comment_date = get_comment_date('F, j, Y', $c->comment_ID);

                //Author
                $author_id = get_post_field( 'post_author', $post_id );
                $avatar = get_avatar($author_id);
                $author_name = get_the_author_meta( 'display_name', $author_id );

                $page_id = get_option('tmreviews_user_reviews_page_id', true);
                if(isset($page_id) && !empty($page_id)){
                    $user_page_link = get_permalink($page_id);
                }



                //Category
                $categories = get_the_terms( $post_id, tmreviews_get_post_type() . '-category' );
                $categories_html = '';


                foreach ($categories as $cat){
                    if (!next( $categories )){
                        $categories_html .= '<a href="' . esc_url(get_term_link($cat, 'places-taxonomy'), 'tmreviews') . '">' . $cat->name . '</a>';
                    } else {
                        $categories_html .= '<a href="' . esc_url(get_term_link($cat, 'places-taxonomy'), 'tmreviews') . ', ">' . $cat->name . ', </a>';
                    }
                }
                if($i == 1){
                    echo '<div class="fl-row">';
                }
                    echo '<div class="fl-places-grid col-4">';
                        echo '<div class="fl-places-slide-top">';
                            echo '<div class="fl-places-author-avatar">';
                                echo $avatar;
                            echo '</div>';
                            echo '<div class="fl-places-average-meta">';
                                echo tmreviews_average_rating($post_id);
                               // echo '<div class="fl-places-average-cat">';
                               //     echo $categories_html;
                               // echo '</div>';
                            echo '</div>';
                        echo '</div>';

                echo '<div class="fl-places-slide-main">';
                    echo '<span class="fl-review-title">' . $title_comment . '</span>';
                        echo '<div class="fl-review-author-contain">';
                            echo '<span class="fl-review-author-name"><a href="' . esc_url($user_page_link) . '?author=' . $author_id . '">' . $author_name . '</a></span> ';
                            echo '<span class="fl-reviewed">' . __('reviewed ') . '</span>';
                            echo '<a href="'. esc_url(get_post_permalink($post_id)) . '">' . get_the_title($post_id) . '</a>';
                        echo '</div>';
                    echo '<span class="fl-review-text">' .  tmreviews_limit_excerpt_search(20, $text_comment) . '</span>';
                echo '</div>';

                echo '<div class="fl-places-slide-bottom">';
                    echo '<a class="fl-review-button" href="'. esc_url(get_post_permalink($post_id)) . '">' . __('Read review', 'tm-reviews') . '</a>';
                echo '<span class="fl-review-date-contain"><span class="fl-review-date">' . __('Review Published: ', 'tm-reviews') . '</span><span class="fl-review-date">' . $comment_date . '</span>' . '</span>';

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
add_shortcode('vc_tmreviews_reviews', 'vc_tmreviews_reviews_function');
