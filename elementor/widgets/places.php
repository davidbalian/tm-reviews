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

class TMReviews_Places extends Widget_Base {

    public function get_name() {
        return 'tmreviews-places';
    }

    public function get_title() {
        return esc_html__( 'Catalog', 'tm-reviews' );
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
            'posts_per_page',
            [
                'label' => esc_html__( 'Post per Page', 'tm-helper-core' ),
                'type' => Controls_Manager::TEXTAREA,
                'dynamic' => [
                    'active' => true,
                ],
                'placeholder' => esc_html__( 'Enter your posts per page', 'tm-reviews' ),
                'default' => 9,
            ]
        );



        $this->end_controls_section();


    }

    protected function render() {
        global $args;
        $tm_reviews_result = '';
        $this->add_render_attribute( 'wrapper_slider', 'class', 'tmreviews-places-slider' );
        $settings = $this->get_settings_for_display();

        $this->add_render_attribute( 'wrapper', 'class', 'tmreviews-places-slider-wrap tmreviews-places-slider-wrap-with-map' ); ?>
        <?php
        $args = array(
            'post_type' => tmreviews_get_post_type(),
            'status'    => 'publish',
            'posts_per_page'            => $settings['posts_per_page'],
        );
        $places = new WP_Query($args);?>

        <div class="section-carousel__inner">
            <div class="js-slider vh-slider" data-slick="{&quot;slidesToShow&quot;: 5,  &quot;slidesToScroll&quot;: 5, &quot;infinite&quot;: true, &quot;responsive&quot;: [{&quot;breakpoint&quot;: 1800, &quot;settings&quot;: {&quot;slidesToShow&quot;: 4, &quot;slidesToScroll&quot;: 4}}, {&quot;breakpoint&quot;: 1400, &quot;settings&quot;: {&quot;slidesToShow&quot;: 3, &quot;slidesToScroll&quot;: 1}}, {&quot;breakpoint&quot;: 1040, &quot;settings&quot;: {&quot;slidesToShow&quot;: 2, &quot;slidesToScroll&quot;: 1}}, {&quot;breakpoint&quot;: 767, &quot;settings&quot;: {&quot;slidesToShow&quot;: 1, &quot;slidesToScroll&quot;: 1}}]}">
                <?php if ($places->have_posts()) : while ($places->have_posts()) : $places->the_post(); ?>
                    <?php include( TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/templates/elementor/places-one.php' ); ?>
                <?php endwhile; endif; wp_reset_query();?>
            </div>
        </div>


        <?php
    }
}