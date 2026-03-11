<?php
use Elementor\Control_Media;
use Elementor\Core\Base\Document;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use ElementorPro\Modules\QueryControl\Module as QueryControlModule;
use ElementorPro\Plugin;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TMReviews_Reviews extends Widget_Base {

    public function get_name() {
        return 'tmreviews-reviews';
    }

    public function get_title() {
        return esc_html__( 'Reviews', 'tm-reviews' );
    }

    public function get_icon() {
        return 'fa fa-location-arrow tm-reviews-icon';
    }

    public function get_categories() {
        return array('tm-reviews-helper-core-elements');
    }

    public function tmreviews_get_taxonomy($type = null){

        $terms = get_terms( array(
            'taxonomy' => tmreviews_get_post_type() . '-category',
            'hide_empty' => true,
        ) );

        $options[0] = esc_html__( 'Select a Category', 'tm-reviews' );

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $cat ) {
                $options[$cat->slug] = $cat->name;
            }
        } else {
            $options[0] = esc_html__( 'Create a Category First', 'tm-reviews' );
        }

        return $options;
    }

    public function tmreviews_get_places($type = null){

        $places = get_posts( array(
            'post_type' => tmreviews_get_post_type(),
            'status' => 'publish',
        ) );

        $options[0] = esc_html__( 'Select a featured Places', 'tm-reviews' );

        if ( ! empty( $places ) && ! is_wp_error( $places ) ) {
            foreach ( $places as $place ) {
                $options[$place->ID] = $place->post_title;
            }
        } else {
            $options[0] = esc_html__( 'Create a Place First', 'tm-reviews' );
        }

        return $options;
    }


    public function tmreviews_get_city($type = null){

        $terms = get_terms( array(
            'taxonomy' => tmreviews_get_post_type() . '-city',
            'hide_empty' => true,
        ) );

        $options[0] = esc_html__( 'Select a City', 'tm-reviews' );

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $cat ) {
                $options[$cat->slug] = $cat->name;
            }
        } else {
            $options[0] = esc_html__( 'Create a City First', 'tm-reviews' );
        }

        return $options;
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_elementor_places_slidermap_general_setting',
            [
                'label' => __( 'General Setting', 'tm-reviews' ),
            ]
        );

        $this->add_control(
            'reviews_count',
            [
                'label' => esc_html__( 'Reviews Count', 'tm-helper-core' ),
                'type' => Controls_Manager::NUMBER,
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => esc_html__( 'Enter your posts per page', 'tm-reviews' ),
                'default' => 9,
            ]
        );

        $this->add_control(
            'reviews_style',
            [
                'label'   => __( 'Style', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'reviews-style-two',
                'options' => [
                    'review-style-one'              =>         esc_attr__('Style One','tm-reviews'),
                    'review-style-two'              =>         esc_attr__('Style Two','tm-reviews'),
                ],
            ]
        );

        $this->add_control(
            'dots',
            [
                'label'   => __( 'Dots', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'enable',
                'options' => [
                    'enable'              =>          esc_attr__('Enable','tm-reviews'),
                    'disable'              =>         esc_attr__('Disable','tm-reviews'),
                ],

                'condition' => [
                    'reviews_style' => 'review-style-one',
                ],
            ]
        );

        $this->add_control(
            'arrows',
            [
                'label'   => __( 'Arrows', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'enable',
                'options' => [
                    'enable'              =>          esc_attr__('Enable','tm-reviews'),
                    'disable'              =>         esc_attr__('Disable','tm-reviews'),
                ],

                'condition' => [
                    'reviews_style' => 'review-style-one',
                ],
            ]
        );


        $this->end_controls_section();


    }

    protected function render() {
        global $args;
        $this->add_render_attribute( 'wrapper_slider', 'class', 'tmreviews-reviews-slider' );
        $settings = $this->get_settings_for_display();

        $idf = uniqid('').'-'.rand(100,9999);

        $comments = get_comments(array('post_type' => tmreviews_get_post_type(), 'status' => 'approve', 'number' => $settings['reviews_count']));
        if(isset($settings['reviews_style']) && $settings['reviews_style'] == 'review-style-one'){
            echo '<div class="fl-reviews-slider fl-reviews-style-one-' . $idf . '">';
            if(count($comments) < 3){
                $slidestoshow = count($comments);
            }else{
                $slidestoshow = 3;
            }
            if(isset($comments) && !empty($comments)){
                foreach ($comments as $c){
                    $post_id = $c->comment_post_ID;
                    $rate =  get_comment_meta($c->comment_ID, 'rating', true);
                    if(isset($rate) && $rate != ''){
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
            }



            echo '</div>';



            $dots = 'dots: false,';
            if(isset($settings['dots']) && $settings['dots'] != 'disable'){
                $dots = 'dots: true,';
            }


            $arrows = 'arrows: false';
            if(isset($settings['arrows']) && $settings['arrows'] != 'disable'){
                $arrows = 'arrows: true,';

                echo '<div class="fl-places-arrows">
                            <span class="fl-places-arrows-left"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
                            <span class="fl-places-arrows-right"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
                        </div>';

            }

            ?>
            <script>
                jQuery.noConflict()(function($) {

                    jQuery('.fl-reviews-style-one-<?php echo $idf;?>').slick({
                        <?php echo esc_attr($dots);?>
                        <?php echo esc_attr($arrows);?>

                        speed: 300,
                        slidesToShow: <?php echo $slidestoshow?>,
                        slidesToScroll: 3,
                        //centerMode: true,
                        variableWidth: true,

                        prevArrow: ".fl-places-arrows-left",
                        nextArrow: ".fl-places-arrows-right",

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

        } elseif (isset($settings['reviews_style']) && $settings['reviews_style'] == 'review-style-two'){
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


        ?>




        <?php
    }
}