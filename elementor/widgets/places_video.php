<?php
use Elementor\Control_Media;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use Elementor\Utils;
use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Group_Control_Typography;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class TMReviews_Custom_Videor extends Widget_Base {

    public function get_name() {
        return 'tm-reviews-custom-video';
    }

    public function get_title() {
        return esc_html__( 'Custom Video', 'tm-reviews' );
    }

    public function get_icon() {
        return 'fa fa-camera tm-reviews-icon';
    }

    public function get_categories() {
        return array('tm-reviews-helper-core-elements');
    }

    protected function register_controls() {
        $this->start_controls_section(
            'section_elementor_video_general_style',
            [
                'label' => __( 'General Styles', 'tm-reviews' ),
            ]
        );
        $this->add_control(
            'title',
            [
                'label' => __( 'Video Content Title', 'tm-reviews' ),
                'type' => Controls_Manager::TEXT,
                'default' => __( 'Intro Video', 'tm-reviews' ),
            ]
        );
        $this->add_control(
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
        $this->add_control(
            'video_link',
            [
                'label' => __( 'Video Link (YouTube)', 'tm-reviews' ),
                'type' => Controls_Manager::URL,
                'dynamic' => [
                    'active' => true,
                ],
                'default' => [
                    'url' => 'https://youtu.be/bjN1-C76ugQ',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $result = '';
        $this->add_render_attribute( 'wrapper', 'class', 'page-builder-custom-video-content-wrap' );
        $settings = $this->get_settings_for_display();


        $result .='<div '.$this->get_render_attribute_string('wrapper').'>';
                if(isset($settings['title']) && $settings['title'] != ''){
                    $result .= '<i class="icon-control-play" aria-hidden="true"></i>';
                    $result .= '<span class="fl-place-rating-title fl-text-bold-style">';
                    $result .= esc_attr($settings['title'], 'tm-reviews');
                    $result .= '</span>';
                 }
                $result .= '<div class="fl-content-video">';

                //Video
                $video = $settings['video_link']['url'];

                $video_image = wp_get_attachment_image_url($settings['image']['id'], 'kaskad_size_730x435_crop');
                $video_html = '';
                if(isset($video) && $video != '' && isset($video_image) && $video_image != ''){
                    if (isset($video) && $video != '' && isset($video_image) && $video_image != ''){
                        $video_html .= '<img src="' . esc_url($video_image) . '">';

                        $video_html .= '<div class="video-btn-wrap">';
                        $video_html .= '<a class="video-btn venobox ternary-video-btn-style" data-vbtype="video" data-autoplay="true" href="' . esc_url($video) . '">';
                        $video_html .= '<i class="fa fa-play"></i>';
                        $video_html .= '<div class="pulsing-bg"></div>';
                        $video_html .= '</a>';
                        $video_html .= '</div>';
                    }
                }
                $result .= $video_html;

                $result .='</div>';

        $result .='</div>';

        echo  $result;

    }
}