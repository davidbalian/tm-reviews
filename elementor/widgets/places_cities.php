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

class TMReviews_Places_Cities extends Widget_Base {

    public function get_name() {
        return 'tmreviews-places-cities';
    }

    public function get_title() {
        return esc_html__( 'Cities', 'tm-reviews' );
    }

    public function get_icon() {
        return 'fa fa fa-globe tm-reviews-icon';
    }

    public function get_categories() {
        return array('tm-reviews-helper-core-elements');
    }

    public function tmreviews_get_cities($type = null){

        $terms = get_terms( array(
            'taxonomy' => tmreviews_get_post_type() . '-city',
            'hide_empty' => false,
        ) );

        $options[0] = esc_html__( 'Select a Cities', 'tm-reviews' );

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
            'section_elementor_places_cities_general_setting',
            [
                'label' => __( 'General Setting', 'tm-reviews' ),
            ]
        );

        $this->add_control(
            'places_cities', [
                'label' => esc_html__( 'Choose Cities', 'tm-reviews' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->tmreviews_get_cities(),
                'show_label' => true,
                'label_block' => true,

            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $args;
        $tm_reviews_result_city = '';
        $this->add_render_attribute( 'wrapper', 'class', 'tmreviews-places-cities-wrap' );
        $this->add_render_attribute( 'wrapper_slider', 'class', 'tmreviews-places-cities' );
        $settings = $this->get_settings_for_display();



        ?>

        <?php if (isset($settings['places_cities']) && $settings['places_cities'] != '') { ?>
            <div class="gallery__wrapper">
                <?php $f = 1; foreach ($settings['places_cities'] as $city) {
                    $size = 'full';
                    $city_term = get_term_by('slug', $city, tmreviews_get_post_type() . '-city');
                    $term = get_term( $city_term, tmreviews_get_post_type() . '-city' );
                    $city_bg_id = get_field('city_image', $city_term);
                    $city_bg = wp_get_attachment_image_url($city_bg_id, $size);
                    $redirect_url = get_home_url() . '?c=' . $city_term->slug . '&post_type=' . tmreviews_get_post_type();
                    ?>
                    <?php if($f == 1) { ?>
                        <ul class="gallery__items">
                        <?php $row = 'row-2';?>
                    <?php } else { ?>
                        <?php $row = 'row-1';?>
                    <?php } ?>
                        <li class="gallery__item <?php echo $row;?>">
                            <div class="featured-dis">
                                <?php if(isset($city_bg) && $city_bg != ''){ ?>
                                    <span style="background-image: url(<?php echo esc_url($city_bg);?>)" class="gallery__image""></span>
                                <?php } ?>
                                <div class="disbox">
                                    <span class="disname"><?php echo esc_html($city_term->name)?></span>
                                    <a href="<?php echo esc_url($redirect_url)?>"><?php echo __("View Destination ", "tm-reviews")?><i class="fa-solid fa-angles-right"></i></a>
                                </div>
                            </div>
                        </li>
                    <?php if($f == 3 or $f == count($settings['places_cities'])) { ?>
                        </ul>
                    <?php $f = 0; } ?>
                <?php $f++; } ?>
            </div>
         <?php } ?>
        <?php

    }
}