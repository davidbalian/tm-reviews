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

class TMReviews_Places_Category extends Widget_Base {

    public function get_name() {
        return 'tmreviews-places-category';
    }

    public function get_title() {
        return esc_html__( 'Categories', 'tm-reviews' );
    }

    public function get_icon() {
        return 'fa fa fa-globe tm-reviews-icon';
    }

    public function get_categories() {
        return array('tm-reviews-helper-core-elements');
    }

    public function tmreviews_get_category($type = null){

        $terms = get_terms( array(
            'taxonomy' => tmreviews_get_post_type() . '-category',
            'hide_empty' => false,
        ) );

        $options[0] = esc_html__( 'Select a Categories', 'tm-reviews' );

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            foreach ( $terms as $cat ) {
                $options[$cat->term_id] = $cat->name;
            }
        } else {
            $options[0] = esc_html__( 'Create a Category First', 'tm-reviews' );
        }

        return $options;
    }


    protected function register_controls() {
        $this->start_controls_section(
            'section_elementor_places_cities_general_setting',
            [
                'label' => __( 'General Setting', 'tm-reviews' ),
            ]
        );
        $this->add_control(
            'places_taxes', [
                'label' => esc_html__( 'Choose Featured Categories', 'tm-reviews' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->tmreviews_get_category(),
                'show_label' => true,
                'label_block' => true,
            ]
        );

        $this->add_control(
            'places_style',
            [
                'label'   => __( 'Style', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'places-style-two',
                'options' => [
                    'places-style-one'              =>         esc_attr__('Style One','tm-reviews'),
                    'places-style-two'              =>         esc_attr__('Style Two','tm-reviews'),
                ],
            ]
        );

        $this->end_controls_section();


    }

    protected function render() {
        global $args;
        $this->add_render_attribute( 'wrapper', 'class', 'tmreviews-places-categories-wrap' );
        $this->add_render_attribute( 'wrapper_slider', 'class', 'tmreviews-places-categories' );
        $settings = $this->get_settings_for_display();



        $terms = get_terms( array(
            'taxonomy' => tmreviews_get_post_type() . '-category',
            'hide_empty' => false,
            'term_taxonomy_id' => $settings['places_taxes']
        ) );


        ?>
        <?php
        if (isset($settings['places_style']) && $settings['places_style'] == 'places-style-two'){
            echo '<div class="fl-places-cat-contain fl-places-style-two">';
                $i = 1;
                $k = 1;
                $len = count($terms);
                foreach ($terms as $term){
                    $cat_bg_id = get_field('cat_image', $term);
                    $cat_bg = wp_get_attachment_image_url($cat_bg_id, 'gazek_size_570x400_crop');

                    $childs = get_term_children($term->term_id, tmreviews_get_post_type() . '-category');


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
                        echo '<a class = "fl-places-cat-contain" style="'.$cat_bg_style.'" href="' . get_term_link( $term ) . '"></a>';
                    } else {
                        $cat_bg_style = 'background-image: url('.get_template_directory_uri().'/assets/css/images/cat045.jpg); background-size: cover;';
                        echo '<a class = "fl-places-cat-contain" style="'.$cat_bg_style.'" href="' . get_term_link( $term ) . '"></a>';
                    }

                    echo '<div class="fl-places-meta">';
                    echo '<div class="fl-service-bottom">';
                    echo '<span class="fl-places-results">' . $results . '</span>';
                    echo '<span class="fl-places-reviews-count"><i class="fa fa-comment" aria-hidden="true"></i>' . $cat_review_count . __(' Reviews', 'tm-reviews')  .'</span>';
                    echo '</div>';
                    echo '<a class="fl-places-title fl-text-title-style" href="' . get_term_link( $term ) . '">' . $term->name . '</a>';

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
        } elseif (isset($settings['places_style']) && $settings['places_style'] == 'places-style-one'){
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
                    echo '<a class = "fl-places-cat-contain" style="'.$cat_bg_style.'" href="' . get_term_link( $term ) . '"></a>';
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
        ?>

        <?php

    }
}