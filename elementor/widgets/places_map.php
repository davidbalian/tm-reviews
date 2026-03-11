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

class TMReviews_Places_Map_Routing extends Widget_Base {

    public function get_name() {
        return 'tmreviews-places-map-route';
    }

    public function get_title() {
        return esc_html__( 'Map Routing', 'tm-reviews' );
    }

    public function get_icon() {
        return 'fa fa-location-arrow tm-reviews-icon';
    }

    public function get_categories() {
        return array('tm-reviews-helper-core-elements');
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
            'city', [
                'label' => esc_html__( 'Choose City', 'tm-reviews' ),
                'type' => Controls_Manager::SELECT,
                'multiple' => true,
                'options' => $this->tmreviews_get_city(),
                'show_label' => true,
                'label_block' => true,
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
        $this->add_render_attribute( 'wrapper_slider', 'class', 'tmreviews-places-map-routing' );
        $settings = $this->get_settings_for_display();

        $locations = tm_reviews_get_locations();
        $argss = array(
            'fields' => 'ids',
            'numberposts' => 1,
            'post_type'   => tmreviews_get_post_type()
        );
        $place_id = get_posts($argss);
        $center_lng = get_post_meta($place_id[0], 'place_lng', true);
        $center_lat = get_post_meta($place_id[0], 'place_lat', true);


       // $center_lat = 49.41169;
       // $center_lng = 75.47037;

        if(isset($center_lat) && $center_lat != '' && isset($center_lng) && $center_lng != ''){
            $center =  '{ lat: ' . $center_lat . ', lng: ' . $center_lng . ' }';
            echo '<script>
            var locations =  ' . $locations . ';
            var center = ' . $center . ';
        </script>';
        }?>
        <?php
        $args = array(
            'post_type' => tmreviews_get_post_type(),
            'status'    => 'publish',
            'posts_per_page'            => $settings['posts_per_page'],
        );
        $places = new WP_Query($args);?>

        <div class="tmreviews_places_archive_cont routing_map">
            <?php include( TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/templates/sidebar/route_map.php' ); ?>
        </div>


    <?php }
}