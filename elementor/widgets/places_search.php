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

class TMReviews_Places_Search extends Widget_Base {

    public function get_name() {
        return 'tmreviews-places-search';
    }

    public function get_title() {
        return esc_html__( 'Catalog Search', 'tm-reviews' );
    }

    public function get_icon() {
        return 'fa fa-search tm-reviews-icon';
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


    protected function register_controls() {
        $this->start_controls_section(
            'section_elementor_places_search_general_setting',
            [
                'label' => __( 'General Setting', 'tm-reviews' ),
            ]
        );

        $this->add_control(
            'search-tab-tax',
            [
                'label'   => __( 'Search Tab Taxonomy', 'tm-reviews' ),
                'type'    => Controls_Manager::SELECT2,
                'default' => tmreviews_get_post_type() . '-category',
                'multiple' => true,
                'options' => [
                    tmreviews_get_post_type() . '-category'    =>         esc_attr__('Category','tm-reviews'),
                    tmreviews_get_post_type() . '-direction'    =>         esc_attr__('Direction','tm-reviews'),
                    tmreviews_get_post_type() . '-tax-one'    =>         esc_attr__('Filter One','tm-reviews'),
                    tmreviews_get_post_type() . '-tax-two'    =>          esc_attr__('Filter Two','tm-reviews'),
                    tmreviews_get_post_type() . '-tax-three'    =>          esc_attr__('Filter Three','tm-reviews'),
                    tmreviews_get_post_type() . '-tax-four'    =>          esc_attr__('Filter Four','tm-reviews'),
                ],
            ]
        );

        $this->add_control(
            'search_select',
            [
                'label' => __( 'Search City Select Placeholder', 'tm-reviews' ),
                'type' => Controls_Manager::TEXT,
                'separator' => 'before',
                'label_block' => true,
                'default' => __("Where", "tm-reviews"),
            ]
        );
        $this->add_control(
            'search_button',
            [
                'label' => __( 'Search Button Text', 'tm-reviews' ),
                'type' => Controls_Manager::TEXT,
                'separator' => 'before',
                'label_block' => true,
                'default' => __("LIST MY RV", "tm-reviews"),
            ]
        );


        $this->end_controls_section();
    }

    protected function render() {
        global $args;
        $this->add_render_attribute( 'wrapper', 'class', 'tmreviews-places-search-wrap ' );
        $this->add_render_attribute( 'wrapper_slider', 'class', 'tmreviews-places-search' );
        $settings = $this->get_settings_for_display(); ?>




        <div class="b-find">
            <div class="b-find-content tab-content" id="findTabContent">
                <div class="tab-pane fade show active" id="content-allCar">
                    <div class="b-find__form">
                        <div class="b-find__row">
                            <div class="b-find__main">
                                <div class="b-find__inner">
                                    <div class="b-find__item">
                                        <?php $search_select = __("Where", "tm-reviews");
                                        if(isset( $settings['search_select']) &&  $settings['search_select'] != ''){
                                            $search_select = $settings['search_select'];
                                        } ?>
                                        <div class="b-find__label"><span class="b-find__number">01</span> <?php echo $search_select;?></div>
                                        <div class="b-find__selector">
                                            <select id="fl-places-city" class="selectpicker" data-width="100%" data-style="ui-select" name="c">
                                                <?php $taxomony_city = get_terms(tmreviews_get_post_type() . '-city');
                                                if(isset($taxomony_city) && !empty($taxomony_city)){ ?>
                                                    <option value="<?php echo esc_attr('all');?>">
                                                        <?php echo esc_html('All')?>
                                                    </option>
                                                    <?php foreach ($taxomony_city as $t){ ?>
                                                        <option value="<?php echo esc_attr($t->slug);?>">
                                                            <?php echo esc_html($t->name)?>
                                                        </option>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <?php $l = 2; $sel_name = ''; if(isset($settings['search-tab-tax']) && !empty($settings['search-tab-tax'])){ ?>
                                        <?php foreach($settings['search-tab-tax'] as $item){
                                            switch ($item) {
                                                case tmreviews_get_post_type() . "-category":
                                                    $text_val = __("Category", "tm-reviews");;
                                                    $sel_name = 't';
                                                    break;
                                                case tmreviews_get_post_type() . "-direction":
                                                    $text_val = __("Directory", "tm-reviews");;
                                                    $sel_name = 'd';
                                                    break;
                                                case tmreviews_get_post_type() . "-tax-one":
                                                    $tmreviews_tax_one = get_option('tmreviews_tax-one');
                                                    if(!$tmreviews_tax_one){
                                                        $tmreviews_tax_one = __("Filter one", "tm-reviews");
                                                    }
                                                    $text_val = $tmreviews_tax_one;
                                                    $sel_name = 'fs';
                                                    break;
                                                case tmreviews_get_post_type() . "-tax-two":
                                                    $tmreviews_tax_two = get_option('tmreviews_tax-two');
                                                    if(!$tmreviews_tax_two){
                                                        $tmreviews_tax_two = __("Filter two", "tm-reviews");
                                                    }
                                                    $text_val = $tmreviews_tax_two;
                                                    $sel_name = 'tw';
                                                    break;
                                                case tmreviews_get_post_type() . "-tax-three":
                                                    $tmreviews_tax_three = get_option('tmreviews_tax-three');
                                                    if(!$tmreviews_tax_three){
                                                        $tmreviews_tax_three = __("Filter three", "tm-reviews");
                                                    }
                                                    $text_val = $tmreviews_tax_three;
                                                    $sel_name = 'th';
                                                    break;
                                                case tmreviews_get_post_type() . "-tax-four":
                                                    $tmreviews_tax_four = get_option('tmreviews_tax-four');
                                                    if(!$tmreviews_tax_four){
                                                        $tmreviews_tax_four = __("Filter four", "tm-reviews");
                                                    }
                                                    $text_val = $tmreviews_tax_four;

                                                    $sel_name = 'fr';
                                                    break;
                                            }
                                            ?>
                                            <?php $tax_to_select_s = get_terms($item, array('hide_empty' => false));
                                            ?>
                                            <?php if(isset($tax_to_select_s) && !empty($tax_to_select_s)){ ?>
                                                <div class="b-find__item">
                                                    <div class="b-find__label"><span class="b-find__number"><?php echo '0' . $l;?></span> <?php echo $text_val;?></div>
                                                    <div class="b-find__selector">
                                                        <select name="<?php echo $sel_name;?>" class="selectpicker tax_select" data-width="100%" data-style="ui-select">
                                                            <option value="<?php echo esc_attr('all');?>">
                                                                <?php echo esc_html('All')?>
                                                            </option>
                                                            <?php foreach ($tax_to_select_s as $tx){ ?>
                                                                <option value="<?php echo esc_attr($tx->slug);?>">
                                                                    <?php echo esc_html($tx->name)?>
                                                                </option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                            <?php } ?>
                                        <?php $l++; } ?>
                                    <?php } ?>
                                </div>
                            </div>
                            <?php
                            $btn_text = __("LIST MY RV", "tm-reviews");
                            if(isset( $settings['search_button']) &&  $settings['search_button'] != ''){
                                $btn_text = $settings['search_button'];
                            } ?>
                            <button class="b-find__btn btn btn-primary fl-places-search-button"><?php echo $btn_text;?></button>
                        </div>

                        <div class="b-find__checkbox-group">
                            <span class="b-find__checkbox-item">
                                <input class="forms__check" id="newCars" type="checkbox" checked="checked" />
                                <label class="forms__label forms__label-check" for="newCars">Return to same pickup location</label></span><span class="b-find__checkbox-item">
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script>
            jQuery(document).ready(function($) {
                var places_city = 'all';
                var places_cat = 'all';
                var places_dir = 'all';
                var places_tax_one = 'all';
                var places_tax_two = 'all';
                var places_tax_three = 'all';
                var places_tax_four = 'all';

                places_city = jQuery('#fl-places-city').val();
                places_tax_cat = jQuery('select[name="t"]').val();
                places_dir = jQuery('select[name="d"]').val();
                places_tax_one = jQuery('select[name="fs"]').val();
                places_tax_two = jQuery('select[name="tw"]').val();
                places_tax_three = jQuery('select[name="th"]').val();
                places_tax_four = jQuery('select[name="fr"]').val();

                jQuery('select').on('change', function (e) {
                    if(jQuery(this).attr('name') === "d"){
                        places_dir = jQuery(this).val();
                    }
                    if(jQuery(this).attr('name') === "t"){
                        places_cat = jQuery(this).val();
                    }
                    if(jQuery(this).attr('name') === "c"){
                        places_city = jQuery(this).val();
                    }
                    if(jQuery(this).attr('name') === "fs"){
                        places_tax_one = jQuery(this).val();
                    }
                    if(jQuery(this).attr('name') === "tw"){
                        places_tax_two = jQuery(this).val();
                    }
                    if(jQuery(this).attr('name') === "th"){
                        places_tax_three = jQuery(this).val();
                    }
                    if(jQuery(this).attr('name') === "fr"){
                        places_tax_four = jQuery(this).val();
                    }
                });

                jQuery(".fl-places-search-button").on("click", function () {
                    text_s = jQuery(".fl-place-search-text").val();
                    var redirect_url = "<?php echo home_url();?>" + "/?c=" + places_city +
                        '&t=' + places_cat +
                        '&d=' + places_dir +
                        '&fs=' + places_tax_one +
                        '&tw=' + places_tax_two +
                        '&th=' + places_tax_three +
                        '&fr=' + places_tax_four +
                        '&post_type=<?php echo tmreviews_get_post_type();?>';
                    window.location.replace(redirect_url);
                })
            });
        </script>



        <?php

    }
}