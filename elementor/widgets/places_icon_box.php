<?php
use Elementor\Control_Media;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TMReviews_Places_Icon_Box extends Widget_Base {

    public function get_name() {
        return 'tmreviews-icon-box';
    }

    public function get_title() {
        return esc_html__( 'Icon Box', 'tm-reviews' );
    }

    public function get_icon() {
        return 'fa fa-font-awesome tm-reviews-icon';
    }

    public function get_categories() {
        return array('tm-reviews-helper-core-elements');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_elementor_featured_icons_general_setting',
            [
                'label' => __( 'General Setting', 'tm-reviews' ),
            ]
        );
        $this->add_control(
            'featured_title',
            [
                'label' => __( 'Features Box Title', 'tm-reviews' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Main Features', 'tm-reviews' ),
            ]
        );
        $repeater = new Repeater();
        $repeater->add_control(
            'icon_title',
            [
                'label' => __( 'Icon Title', 'tm-reviews' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Icon Title', 'tm-reviews' ),
            ]
        );
        $repeater->add_control(
            'icon_svg',
            [
                'label'            => __( 'Icon', 'tm-reviews' ),
                'type'             => Controls_Manager::ICONS,
                'label_block'      => true,
                'default'          => [
                    'value'   => 'fas fa-star',
                    'library' => 'fa-solid',
                ],
                'fa4compatibility' => 'icon'
            ]
        );

        $this->add_control(
            'icon_item_list',
            [
                'label'       => '',
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),

            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $idf_icon_featured = uniqid('fl-icon-box').'-'.rand(100,9999);
        $this->add_render_attribute( 'wrapper', 'class', 'fl-place-single-features '.$idf_icon_featured.' ' );


        $settings = $this->get_settings_for_display();
        $i = 1;
        $k = 1;
        if($settings['icon_item_list']){
            $count_list = count($settings['icon_item_list']);
        }
        ?>
        <div class="page-builder-places-icon-box-wrap">
            <?php if(isset($settings['featured_title']) && $settings['featured_title'] != ''){ ?>
                <i class="icon-diamond" aria-hidden="true"></i>
                <span class="fl-place-rating-title fl-text-bold-style">
                        <?php echo esc_attr($settings['featured_title'], 'tm-reviews');?>
                    </span>
            <?php } ?>
            <div <?php echo $this->get_render_attribute_string('wrapper')?>>
                <?php foreach ( $settings['icon_item_list'] as $index => $item ) : ?>
                    <?php if($i <= 4){?>
                        <div class="fl-features-contain">
                            <?php if ( $item['icon_svg'] ) { ?>
                                <div class="fl-icon-wrap">
                                    <?php Icons_Manager::render_icon( $item['icon_svg']); ?>
                                </div>
                            <?php } ?>
                            <?php if ( $item['icon_title'] ) { ?>
                                <span class="fl-icon-title"><?php echo $item['icon_title']; ?></span>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <?php if($k == 1){ ?>
                            <script>
                                jQuery.noConflict()(function ($){
                                    jQuery('.fl-dots-featured').on('click', function () {
                                        if(jQuery(this).siblings('.fl-features-contain-hidden').hasClass('active')){
                                            jQuery(this).siblings('.fl-features-contain-hidden').removeClass('active');
                                        } else {
                                            jQuery(this).siblings('.fl-features-contain-hidden').addClass('active');
                                        }
                                    })
                                });
                            </script>
                            <span class="fl-dots-featured">
                                <span></span>
                                <span></span>
                                <span></span>
                            </span>
                            <div class="fl-features-contain-hidden">
                        <?php } ?>
                            <div class="fl-features-hidden">
                                <?php if ( $item['icon_title'] ) { ?>
                                    <span class="fl-icon-title"><?php echo $item['icon_title']; ?></span>
                                <?php } ?>
                            </div>

                    <?php if($k == $count_list){ ?>
                          </div>
                    <?php } ?>

                    <?php $k++; } ?>
                <?php $i++; endforeach; ?>
            </div>
        </div>

        <?php
    }
}
?>