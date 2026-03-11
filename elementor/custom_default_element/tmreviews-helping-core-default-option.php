<?php

/**
 * Class description
 *
 * @author    TMReviews
 * @license   GPL-2.0+
 */
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Typography;

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

if ( ! class_exists( 'TMReviews_Helper_Core_Default_Item_Option' ) ) {

	/**
	 * Define TMReviews_Helper_Core_Elements_Ext_Section class
	 */
	class TMReviews_Helper_Core_Default_Item_Option {

		/**
		 * [$parallax_sections description]
		 * @var array
		 */
		public $parallax_sections = array();

		/**
		 * A reference to an instance of this class.
		 *
		 * @since  1.0.0
		 * @access private
		 * @var    object
		 */
		private static $instance = null;

		/**
		 * Init Handler
		 */
        private $section_id = 'tmreviews_widget_animation_section';
        
        public function init() {
            // Добавляем секцию только для виджетов
            add_action('elementor/element/common/_section_style/after_section_end', [$this, 'register_controls'], 10);
            add_action('elementor/widget/before_render_content', array($this, 'before_render'));
        }

        public function register_controls($element) {
            // Проверяем, не существует ли уже секция
            $stack = $element->get_controls();
            if (isset($stack[$this->section_id])) {
                return;
            }

            $element->start_controls_section(
                $this->section_id,
                [
                    'label' => esc_html__('Temlines Animation', 'tm-reviews'),
                    'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
                ]
            );

            $element->add_control(
                'tm_reviews_custom_animation',
                [
                    'label'   => __( 'Animation', 'tm-reviews' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => 'disable',
                    'options' => [
                        'disable'                   =>           esc_attr__('Disable','tm-reviews'),
                        'fadeIn'                    =>           'fadeIn',
                        'flipXIn'                   =>           'flipXIn',
                        'flipYIn'                   =>           'flipYIn',
                        'flipBounceXIn'             =>           'flipBounceXIn',
                        'flipBounceYIn'             =>           'flipBounceYIn',
                        'swoopIn'                   =>           'swoopIn',
                        'raise'                     =>           'raise',
                        'whirlIn'                   =>           'whirlIn',
                        'shrinkIn'                  =>           'shrinkIn',
                        'expandIn'                  =>           'expandIn',
                        'bounceIn'                  =>           'bounceIn',
                        'bounceUpIn'                =>           'bounceUpIn',
                        'bounceDownIn'              =>           'bounceDownIn',
                        'bounceLeftIn'              =>           'bounceLeftIn',
                        'bounceRightIn'             =>           'bounceRightIn',
                        'slideUpIn'                 =>           'slideUpIn',
                        'slideDownIn'               =>           'slideDownIn',
                        'slideLeftIn'               =>           'slideLeftIn',
                        'slideRightIn'              =>           'slideRightIn',
                        'slideUpBigIn'              =>           'slideUpBigIn',
                        'slideDownBigIn'            =>           'slideDownBigIn',
                        'slideLeftBigIn'            =>           'slideLeftBigIn',
                        'slideRightBigIn'           =>           'slideRightBigIn',
                        'perspectiveUpIn'           =>           'perspectiveUpIn',
                        'perspectiveDownIn'         =>           'perspectiveDownIn',
                        'perspectiveLeftIn'         =>           'perspectiveLeftIn',
                        'perspectiveRightIn'        =>           'perspectiveRightIn',
                        'zoomIn'                    =>           'zoomIn',
                        'slideInRightVeryBig'       =>           'slideInRightVeryBig',
                        'slideInLeftVeryBig'        =>           'slideInLeftVeryBig',
                    ],
                ]
            );
            $element->add_control(
                'tm_reviews_animation_delay',
                [
                    'label' => __( 'Animation Delay', 'tm-reviews' ) . ' (ms)',
                    'type' => Controls_Manager::NUMBER,
                    'default' => '',
                    'min' => 0,
                    'step' => 100,
                    'condition' => [
                        'tm_reviews_custom_animation!' => 'disable',
                    ],
                    'render_type' => 'none',
                    'frontend_available' => true,
                ]
            );

            $element->add_control(
                'tm_reviews_item_for_animation',
                [
                    'label' => __( 'Item for animated <br> Example: .class', 'tm-reviews' ),
                    'type' => Controls_Manager::TEXT,
                    'frontend_available' => true,
                    'condition' => [
                        'tm_reviews_custom_animation!' => 'disable',
                    ],
                ]
            );


			$element->end_controls_section();
		}


        public function before_render($element)
        {
            if ($element->get_settings('tm_reviews_custom_animation') != 'disable') {
                $element->add_render_attribute('_wrapper', [
                    'class' => 'fl-custom-animation-'.$element->get_id().'',
                ]);
            }
            if ($element->get_settings('tm_reviews_custom_animation') != 'disable') {
                $element->add_render_attribute('_wrapper', [
                    'data-animate-type' => $element->get_settings('tm_reviews_custom_animation'),
                    'class' => 'fl-animated-item-velocity',
                ]);
            }
            if ($element->get_settings('tm_reviews_custom_animation') != 'disable' && $element->get_settings('tm_reviews_animation_delay') != '') {
                $element->add_render_attribute('_wrapper', [
                    'data-item-delay' => $element->get_settings('tm_reviews_animation_delay'),
                ]);
            }
            if ($element->get_settings('tm_reviews_custom_animation') != 'disable' && $element->get_settings('tm_reviews_item_for_animation') != '') {
                $element->add_render_attribute('_wrapper', [
                    'data-item-for-animated' => $element->get_settings('tm_reviews_item_for_animation'),
                ]);
            }

        }

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @access public
		 * @return object
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}
			return self::$instance;
		}
	}
}

/**
 * Returns instance of TMReviews_Helper_Core_Default_Item_Option
 *
 * @return object
 */
function tmreviews_helper_core_default_item_option() {
	return TMReviews_Helper_Core_Default_Item_Option::get_instance();
}
