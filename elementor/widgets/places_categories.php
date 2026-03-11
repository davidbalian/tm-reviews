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

class TMReviews_Places_Categories extends Widget_Base {

    public function get_name() {
        return 'tmreviews-places-categories';
    }

    public function get_title() {
        return esc_html__( 'All Categories', 'tm-reviews' );
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
                $options[$cat->slug] = $cat->name;
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
            'icons_color',
            [
                'label' => __( 'Icons Color', 'tm-reviews' ),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .fl-icon-contain svg'  => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .fl-icon-contain svg path'  => 'fill: {{VALUE}};',
                    '{{WRAPPER}} .fl-icon-contain svg rect'  => 'fill: {{VALUE}};',
                ],
            ]
        );


        $this->end_controls_section();
    }

    protected function render() {
        global $args;
        $this->add_render_attribute( 'wrapper', 'class', 'tmreviews-all-categories-wrap' );
        $this->add_render_attribute( 'wrapper_slider', 'class', 'tmreviews-all-categories' );
        $settings = $this->get_settings_for_display();
        ?>

        <div class="container__ads">
            <?php
            $args = array(
                'taxonomy' => tmreviews_get_post_type() . '-category',
            );
            $i = 1;
            $k = 1;
            $cat_par_count = 0;
            $cats = get_categories($args);
            foreach ($cats as $c){
                if(isset($c->category_parent) && $c->category_parent == 0){
                    $cat_par_count++;
                }
            }
            if(isset($cats) && !empty($cats)){
                foreach ($cats as $cat){ ?>
                    <?php if(isset($cat->category_parent) && $cat->category_parent == 0){ ?>
                        <?php if($i == 1){ ?>
                            <div class="row ">
                        <?php } ?>
                        <div class="col-12 col-lg-3">
                            <div class="fl-category-single">
                                <?php $cat_parent_id = $cat->term_id;?>
                                <?php
                                $icon_css = get_field('cat_icon', $cat);

                                $url = $icon_css["url"];
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                                curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
                                $svg = curl_exec($ch);
                                curl_close($ch);

                                $category_url = get_home_url() . '?s=&n=' . tmreviews_get_post_type() . '-category&t=' . $cat->slug . '&post_type=' . tmreviews_get_post_type() . '';

                                if (isset($icon_css["url"] ) && $icon_css["url"]  != ''){
                                    if($icon_css['mime_type'] == 'image/svg+xml'){
                                        $icon = '<a class="place_cat_link" href="'. $category_url . '"><span class="fl-icon-contain">' . $svg . '</span></a>';
                                    } elseif ($icon_css['mime_type'] == 'image/png' or $icon_css['mime_type'] == 'image/jpg' or $icon_css['mime_type'] == 'image/jpeg'){
                                        $icon = '<a  class="place_cat_link" href="' . $category_url.'"><span class="fl-icon-contain"><img src="' . esc_url( $icon_css["url"], "tm-reviews" ). '"/></span></a>';
                                    }
                                } else {
                                    $icon = '';
                                }
                                if(isset($icon) && $icon != ''){
                                    echo $icon;
                                }
                                ?>
                                <a href="<?php echo $category_url ?>"><span class="fl-places-categories-title"><?php echo $cat->name; ?></span></a>
                                <?php
                                $args_sub = array('taxonomy' => tmreviews_get_post_type() . '-category', 'parent' => $cat_parent_id);
                                $sub_cats = get_categories( $args_sub );
                                foreach ($sub_cats as $sub_cat){
                                    $sub_category_url = get_home_url() . '?s=&n=' . tmreviews_get_post_type() . '-category&t=' . $sub_cat->slug . '&post_type=' . tmreviews_get_post_type() . '';
                                    ?>
                                    <a href="<?php echo $sub_category_url ?>">
                                        <span class="fl-cat-post-name"><?php echo $sub_cat->name; ?></span>
                                        <span class="fl-cat-post-count"><?php echo '('.$sub_cat->count.')'; ?></span>
                                    </a>
                                <?php } ?>
                            </div>
                        </div>
                        <?php if($i == 4 || $cat_par_count == $k ){ ?>
                            <?php $i = 0;?>
                            </div>
                        <?php } ?>
                        <?php $i++; $k++; ?>
                    <?php } ?>
                <?php } ?>
            <?php } else { ?>
                <?php echo __('Please, add some categories for post type', 'tm-reviews'); ?>

            <?php } ?>

        </div>




        <?php

    }
}