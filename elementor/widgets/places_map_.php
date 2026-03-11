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
        if(isset($center_lat) && $center_lat != '' && isset($center_lng) && $center_lng != ''){
            $center = '{ lat: 49.41278, lng: 75.46889 }';
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

        <div class="tmreviews_places_archive_cont">

            <main class="main col-4">
                <section class="page-card">

                    <div class="listing-top-info d-flex flex-wrap justify-content-sm-between justify-content-center align-items-center">
                        <div class="listing-top-text mb-md-0 mb-4"><?php echo __('Your search has', 'tm-reviews');?><strong> <?php echo esc_html($places->found_posts)?> </strong><?php echo __('results', 'tm-reviews')?></div>
                        <div class="buttons-list__grid mb-md-0 mb-4">
                            <a href="<?php echo esc_url('?view=grid' . $text_to_url);?>" class="grid filter__active">
                                <i class="fa-solid fa-grip"></i>
                            </a>
                        </div>
                    </div>

                    <div class="container d-flex">
                        <?php include( TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/templates/sidebar/filters.php' ); ?>

                        <div class="listing__products col-6">
                            <div class="row" id="fl-places-ajax-container">
                                <?php if ($places->have_posts()) : while ($places->have_posts()) : $places->the_post(); ?>
                                    <?php include( TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/templates/loop/grid.php' ); ?>
                                <?php endwhile; else : ?>
                                    <?php include( TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/templates/loop/none.php' ); ?>
                                <?php endif;?>
                            </div>
                            <div class="tmreviews_pagination">
                                <?php
                                $tm_reviews_result_pag = '';
                                if($wp_query->post_count >= $post_count){
                                    $tm_reviews_result_pag .= '<a id="tmreviews_loadmore" class="fl-header-btn fl-custom-btn" data-count = "' . $wp_query->post_count . '" data-tax="' . $_GET["tax"] . '" data-city="' . $_GET["city"] . '" data-search="' . $_GET["s"] . '" data-reset="' . __( "Reset filters", "tm-reviews" ) . '" data-no="' . __( "No posts", "tm-reviews" ) . '" data-yes="' . __('Load More', 'tm-reviews') . '">' . __('Load More', 'tm-reviews') . '</a>';
                                }
                                echo $tm_reviews_result_pag;
                                wp_reset_query();
                                ?>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <?php include( TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/templates/sidebar/route_map.php' ); ?>
        </div>


    <?php }
}