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

class TMReviews_Places_Slider_No_map extends Widget_Base {

    public function get_name() {
        return 'tmreviews-places-slider';
    }

    public function get_title() {
        return esc_html__( 'Slider No Map', 'tm-reviews' );
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
            'status' => 'published',
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
            'section_elementor_places_slider_general_setting',
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

        $this->add_control(
            'sorting-style',
            [
                'label'   => __( 'Sorting', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'enable',
                'options' => [
                    'enable'              =>         esc_attr__('Enable Sorting','tm-reviews'),
                    'disable'              =>         esc_attr__('Disable Sorting','tm-reviews'),
                ],
            ]
        );

        $repeater = new Repeater();


        $repeater->add_control(
            'slider_or_grid',
            [
                'label'   => __( 'Slider/Grid', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'slider',
                'options' => [
                    'slider'              =>         esc_attr__('Slider','tm-reviews'),
                    'grid'                =>         esc_attr__('Grid','tm-reviews'),
                ],
            ]
        );
        $repeater->add_control(
            'rows',
            [
                'label' => __( 'Rows', 'tm-reviews' ),
                'type' => Controls_Manager::NUMBER,
                'separator' => 'before',
                'label_block' => true,
                'min' => 1,
                'max' => 5,
                'step' => 1,
                'default' => 3,
                'condition' => [
                    'slider_or_grid' => 'grid',
                ],
            ]
        );
        $repeater->add_control(
            'slider_slides_to_show',
            [
                'label' => __( 'Slider Slides to Show', 'tm-reviews' ),
                'type' => Controls_Manager::NUMBER,
                'separator' => 'before',
                'label_block' => true,
                'min' => 1,
                'max' => 5,
                'step' => 1,
                'default' => 5,
                'condition' => [
                    'slider_or_grid' => 'slider',
                ],
            ]
        );

        $repeater->add_control(
            'slider_arrows',
            [
                'label'   => __( 'Arrows', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'enable',
                'options' => [
                    'enable'              =>         esc_attr__('Enable','tm-reviews'),
                    'disable'              =>         esc_attr__('Disable','tm-reviews'),
                ],
                'condition' => [
                    'slider_or_grid' => 'slider',
                ],
            ]
        );

        $repeater->add_control(
            'slider_arrows_style',
            [
                'label'   => __( 'Arrows', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'style_one',
                'options' => [
                    'style_one'              =>         esc_attr__('Style One','tm-reviews'),
                    'style_two'              =>         esc_attr__('Style Two','tm-reviews'),
                ],
                'condition' => [
                    'slider_arrows' => 'enable',
                    'slider_or_grid' => 'slider',
                ],
            ]
        );

        $repeater->add_control(
            'slider_dots',
            [
                'label'   => __( 'Dots', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'enable',
                'options' => [
                    'enable'              =>         esc_attr__('Enable','tm-reviews'),
                    'disable'              =>         esc_attr__('Disable','tm-reviews'),
                ],
                'condition' => [
                    'slider_or_grid' => 'slider',
                ],
            ]
        );

        $repeater->add_control(
            'category', [
                'label' => esc_html__( 'Choose Categories', 'tm-reviews' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->tmreviews_get_taxonomy(),
                'show_label' => true,
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'featured_places', [
                'label' => esc_html__( 'Choose Featured Places', 'tm-reviews' ),
                'type' => Controls_Manager::SELECT2,
                'multiple' => true,
                'options' => $this->tmreviews_get_places(),
                'show_label' => true,
                'label_block' => true,
            ]
        );


        $this->add_control(
            'places_slider_list',
            [
                'label'       => '',
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $args;
        $tm_reviews_result = '';
        $this->add_render_attribute( 'wrapper_slider', 'class', 'tmreviews-places-slider' );
        $settings = $this->get_settings_for_display();

        $this->add_render_attribute( 'wrapper', 'class', 'tmreviews-places-slider-wrap tmreviews-places-slider-wrap-no-map' );

        $tm_reviews_result .='<div '.$this->get_render_attribute_string('wrapper').'>';

        if(isset($_GET[tmreviews_get_post_type() . '-city']) && $_GET[tmreviews_get_post_type() . '-city'] != ''){
            $city = $_GET[tmreviews_get_post_type() . '-city'];
        } elseif(isset( $settings['city']) && $settings['city'] != '' && $settings['city'] != '0') {
            $city = $settings['city'];
        } else {
            $city = '';
        }

            $tm_reviews_result .= '<div class="fl-places-container fl-places-container-no-map">
                <input id="fl-city-hidden-val" type="hidden"  value="' . esc_attr($city, 'tm-reviews') .'">';

            if($settings['sorting-style'] == 'enable'){
                include( TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/templates/sorting.php' );
            }



            foreach ( $settings['places_slider_list'] as $index => $item ) :
                if(isset($item['rows']) && $item['rows'] != ''){
                    switch ($item['rows']) {
                        case 1:
                            $column_class = 'one_column';
                            break;
                        case 2:
                            $column_class = 'two_column';
                            break;
                        case 3:
                            $column_class = 'three_column';
                            break;
                        case 4:
                            $column_class = 'four_column';
                            break;
                        case 5:
                            $column_class = 'five_column';
                            break;
                    }
                }

                if (isset($item['slider_or_grid']) && $item['slider_or_grid'] == 'grid'){
                    $tm_reviews_result .='<div class="fl-places-grid-wrapper '. $column_class .'" id="fl-places-ajax-container">';

                } elseif (isset($item['slider_or_grid']) && $item['slider_or_grid'] == 'slider'){
                    $tm_reviews_result .='<div class="fl-places-slider-wrapper" id="fl-places-ajax-container">';
                }

                if(isset($_GET[tmreviews_get_post_type() . '-city']) && $_GET[tmreviews_get_post_type() . '-city'] != ''){
                    $city = $_GET[tmreviews_get_post_type() . '-city'];
                } else {
                    $city = '';
                }

                if(isset($_GET[tmreviews_get_post_type() . '-category']) && $_GET[tmreviews_get_post_type() . '-category'] != '' && $_GET[tmreviews_get_post_type() . '-category'] != 'null'){
                    $places_category = $_GET[tmreviews_get_post_type() . '-category'];
                    $tax_query_cat =
                        array(
                            'taxonomy' => tmreviews_get_post_type() . '-category',
                            'field' => 'slug',
                            'terms' => $places_category,
                            'posts_per_page'            => $settings['posts_per_page'],
                        );
                } elseif(isset($item['category']) && !empty($item['category'])) {
                    $places_category = $item['category'];
                    $tax_query_cat =
                        array(
                            'taxonomy' => tmreviews_get_post_type() . '-category',
                            'field' => 'slug',
                            'terms' => $places_category,
                            'posts_per_page'            => $settings['posts_per_page'],
                        );
                } else {
                    $places_category = '';
                    $tax_query_cat = '';
                }

                $featured_places = array();
                if (isset($item['featured_places']) && !empty($item['featured_places'])){
                    foreach ($item['featured_places'] as $key => $value){
                        $featured_places[] += $value;
                    }
                }
                if(isset($city) && $city != ''){
                    $tax_query_city =   array(
                        'taxonomy' => tmreviews_get_post_type() . '-city',
                        'field' => 'slug',
                        'terms' => $city,
                        'posts_per_page'            => $settings['posts_per_page'],
                    );
                } else {
                    $tax_query_city = '';
                }


                $args = array(
                    'post_type' => tmreviews_get_post_type(),
                    'status'    => 'publish',
                    'posts_per_page'            => $settings['posts_per_page'],
                    'post__in'  => $featured_places,
                    'tax_query' => array(
                        $tax_query_cat,
                        $tax_query_city
                    )
                );
                $places = new WP_Query($args);



                if(isset($item['slider_or_grid']) && $item['slider_or_grid'] == 'slider'){

                    if (isset($item['slider_arrows']) && $item['slider_arrows'] == 'enable'){
                        $arrows = 'arrows: true,';
                    } else {
                        $arrows = 'arrows: false,';
                    }

                    if (isset($item['slider_dots']) && $item['slider_dots'] == 'enable'){
                        $dots = 'dots: true,';
                    } else {
                        $dots = 'dots: false,';
                    }



                    $slider_class = uniqid('fl-places-slider-').'-'.rand(100,9999);

                    $tm_reviews_result .= '<div class="fl-places-slider '. $slider_class .'">';
                    include( TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/templates/places-elementor-list.php' );
                    $tm_reviews_result .= '</div>';

                    if (isset($item['slider_arrows_style']) && $item['slider_arrows_style'] == 'style_one'){
                        $tm_reviews_result .= '<div class="fl-places-arrows-one">
                                <span class="fl-places-arrows-left"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
                                <span class="fl-places-arrows-right"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
                            </div>';
                    } elseif (isset($item['slider_arrows_style']) && $item['slider_arrows_style'] == 'style_two'){
                        $tm_reviews_result .= '<div class="fl-places-arrows-two">
                                <span class="fl-places-arrows-left"><i class="fa fa-angle-left" aria-hidden="true"></i></span>
                                <span class="fl-places-arrows-right"><i class="fa fa-angle-right" aria-hidden="true"></i></span>
                            </div>';
                    }




                    $tm_reviews_result .= '<script>
                            jQuery.noConflict()(function ($){ 
                                
                                 var place_slider = $(\'' .  '.fl-places-container-no-map  .' . $slider_class . '\');  
                                 place_slider.slick({ 
                                            ' . $arrows . $dots . '
                                            prevArrow: ".fl-places-arrows-left",
                                            nextArrow: ".fl-places-arrows-right",
                                            autoplay: false,
                                            autoplaySpeed: 6000,
                                            speed: 500,
                                            slidesToShow: ' . $item['slider_slides_to_show'] . ',
                                            slidesToScroll: 1,
                                            draggable: true,
                                             responsive: [
                                                        {
                                                          breakpoint: 1920,
                                                          settings: {
                                                            slidesToShow: 5,
                                                            slidesToScroll: 1
                                                          }
                                                        },
                                                        {
                                                          breakpoint: 1700,
                                                          settings: {
                                                            slidesToShow: 4,
                                                            slidesToScroll: 1
                                                          }
                                                        },
                                                       
                                                        {
                                                          breakpoint: 1300,
                                                          settings: {
                                                            slidesToShow: 3,
                                                            slidesToScroll: 1
                                                          }
                                                        },
                                                        {
                                                          breakpoint: 992,
                                                          settings: {
                                                            slidesToShow: 1,
                                                            slidesToScroll: 2,
                                                          }
                                                        },
                                                        {
                                                          breakpoint: 870,
                                                          settings: {
                                                            slidesToShow: 1,
                                                            slidesToScroll: 1,
                                                          }
                                                        },
                                                    ]
                                 })
                             });
                        </script>';


                } elseif (isset($item['slider_or_grid']) && $item['slider_or_grid'] == 'grid'){
                    $tm_reviews_result .= '<div class="fl-places-slider">';
                    include( TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/templates/places-elementor-list.php' );
                    $tm_reviews_result .= '</div>';

                }


            endforeach;

            $tm_reviews_result .='</div>';
            $tm_reviews_result .='</div>';




        $tm_reviews_result .='</div>';

        echo  $tm_reviews_result;

    }
}