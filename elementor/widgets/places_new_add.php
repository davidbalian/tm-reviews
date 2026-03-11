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

class TMReviews_Places_Add extends Widget_Base {

    public function get_name() {
        return 'tmreviews-places-add';
    }

    public function get_title() {
        return esc_html__( 'Add new Places', 'tm-reviews' );
    }

    public function get_icon() {
        return 'fa fa fa-globe tm-reviews-icon';
    }

    public function get_categories() {
        return array('tm-reviews-helper-core-elements');
    }


    protected function register_controls() {
        $this->start_controls_section(
            'section_elementor_places_cities_general_setting',
            [
                'label' => __( 'General Setting', 'tm-reviews' ),
            ]
        );


        $this->end_controls_section();
    }


    protected function render() {
        $this->add_render_attribute( 'wrapper', 'class', 'tmreviews-add-place' );
        $settings = $this->get_settings_for_display();


        $template_city_page_args = get_pages( array(
            'post_type' => 'page',
            'meta_key' => '_wp_page_template',
            'hierarchical' => 0,
            'meta_value' => 'template-city.php'
        ));
        $template_city_pages = get_posts( $template_city_page_args );
        foreach ($template_city_page_args as $city_page){
            $city_page = get_permalink($city_page->ID) . '?add=ok';
        }
        ob_start();
        acf_form(array(
                'post_id' => 'new_post',
                'field_groups' => array('group_5ed556e72f17b123'),
                'new_post'	=> array(
                    'post_type'	=> tmreviews_get_post_type(),
                    'post_status'=> 'pending', // Post Content ACF field key
                ),
                'id' => 'form_draft',
                'html_after_fields' => '<input type="hidden" id="hiddenId" name="acf[current_step]" value="1"/>',
                'return' => home_url() . '/places?add=ok',
                'post_title' => true,
                'post_content' => true,
                'submit_value' => 'Submit'
            )
        );

    }
}