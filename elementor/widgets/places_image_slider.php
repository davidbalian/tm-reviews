<?php
use Elementor\Control_Media;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TMReviews_Places_Image_Slider extends Widget_Base {

    public function get_name() {
        return 'tmreviews-image-gallery';
    }

    public function get_title() {
        return esc_html__( 'Image Slider', 'tm-reviews' );
    }

    public function get_icon() {
        return 'fa fa-file-image-o tm-reviews-icon';
    }

    public function get_categories() {
        return array('tm-reviews-helper-core-elements');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_elementor_image_slider_general_setting',
            [
                'label' => __( 'General Setting', 'tm-reviews' ),
            ]
        );

        $repeater = new Repeater();
          $repeater->add_control(
              'image',
              [
                  'label'             => __( 'Choose Image', 'tm-reviews' ),
                  'type'              => Controls_Manager::MEDIA,
                  'label_block'       => true,
                  'default' => [
                      'url' => Utils::get_placeholder_image_src(),
                  ],
              ]
          );
          $repeater->add_group_control(
              Group_Control_Image_Size::get_type(), [
                  'name' => 'image', // Usage: `{name}_size` and `{name}_custom_dimension`, in this case `testimonial_image_size` and `testimonial_image_custom_dimension`.
                  'default' => 'kaskad_size_730x400_crop',
              ]
          );

          $this->add_control(
              'gallery_item_list',
              [
                  'label'       => '',
                  'type'        => Controls_Manager::REPEATER,
                  'fields'      => $repeater->get_controls(),
                  'default'     => [  ],
              ]
          );

          $this->end_controls_section();
  }

  protected function render() {
      $result = '';
      $idf_gallery_popup = uniqid('fl-magnific-popup').'-'.rand(100,9999);
      $idf_slider = uniqid('fl-image-slider-for-').'-'.rand(100,9999);
      $idf_slider_nav = uniqid('fl-image-slider-nav-').'-'.rand(100,9999);
      $this->add_render_attribute( 'wrapper', 'class', 'fl-gallery fl-magic-popup fl-gallery-popup '.$idf_gallery_popup.' '.$idf_slider );
      $this->add_render_attribute( 'wrapper', 'data-custom-class', ''.$idf_gallery_popup.'' );


      $settings = $this->get_settings_for_display();

      $result .='<div class="page-builder-image-gallery-wrap">';
        $result .='<div '.$this->get_render_attribute_string('wrapper').'>';
        foreach ( $settings['gallery_item_list'] as $index => $item ) :
            if ( $item['image'] ) {
                $result .='<a class="gallery-builder-item image-item" href="'.wp_get_attachment_image_url($item['image']['id'], 'full').'">';
                    $result .='<div class="entry-content">';
                        $result .= wp_get_attachment_image( $item[ 'image' ][ 'id' ], $item[ 'image_size' ], false, ["class" => "img-scale"], ['loading' => 'lazy'] );
                     $result .='</div>';
                $result .='</a>';
            }

        endforeach;
        $result .='</div>';

        $result .='<div class="fl-image-slider-nav '.$idf_slider_nav.'">';
        foreach ( $settings['gallery_item_list'] as $index => $item ) :
            if ( $item['image'] ) {
                $result .='<div class="entry-content">';
                $result .= wp_get_attachment_image( $item[ 'image' ][ 'id' ], 'kaskad_size_105x80_crop', false, ["class" => "img-scale"], ['loading' => 'lazy'] );
                $result .='</div>';
            }
        endforeach;
        $result .='</div>';

        $result .= '    <script>
                            jQuery.noConflict()(function ($){ 
                                 var place_slider_for = $(\'.' . $idf_slider . '\');  
                                 place_slider_for.slick({ 
                                            arrows: false,
                                            dots: false,
                                            autoplay:false,
                                            autoplaySpeed: 6000,
                                            speed: 500,
                                            slidesToShow: 1,
                                            slidesToScroll: 1,
                                            fade: true,
                                            asNavFor: $(\'.' . $idf_slider_nav . '\')
                                 });
                                 
                                 var place_slider_nav = $(\'.' . $idf_slider_nav . '\');  
                                 place_slider_nav.slick({
                                      slidesToShow: 6,
                                      slidesToScroll: 1,
                                      asNavFor:  $(\'.' . $idf_slider . '\'),
                                      dots: false,
                                      arrows: false, 
                                      centerMode: false,
                                      focusOnSelect: true,
                                      infinite: false,
                                      variableWidth: true, 
                                       responsive: [           
                                                {
                                                  breakpoint: 1200,
                                                  settings: {
                                                    slidesToShow: 6,
                                                    slidesToScroll: 1,
                                                    arrows: false,
                                                    variableWidth: false, 
                                                  }
                                                },
                                               
                                            ]
                                    });
                             });
                        </script>';
        $result .='</div>';

        echo  $result;

    }
}