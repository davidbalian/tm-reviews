<?php

/**
 * Class description
 *
 * @author    TMReviews
 * @license   GPL-2.0+
 */

// If this file is called directly, abort.
use Elementor\Controls_Manager;
use Elementor\Utils;

if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! class_exists( 'TMReviews_Helper_Core_Elements_Section' ) ) {

	/**
	 * Define TMReviews_Helper_Core_Elements_Ext_Section class
	 */
	class TMReviews_Helper_Core_Elements_Section {

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

		private $section_id = 'tmreviews_section_background_decor';

		/**
		 * Init Handler
		 */
		public function init() {
            // Добавляем секцию только для секций Elementor
            add_action('elementor/element/section/section_layout/after_section_end', array($this, 'register_controls'), 10);
		}


		public function register_controls( $element ) {
            // Проверяем, не существует ли уже секция
            $stack = $element->get_controls();
            if (isset($stack[$this->section_id])) {
                return;
            }

            $element->start_controls_section(
                $this->section_id,
                [
                    'label' => esc_html__( 'Temlines Decor Background', 'tm-reviews' ),
                    'tab'   => Elementor\Controls_Manager::TAB_ADVANCED,
                ]
            );

            $element->add_responsive_control(
                'tm_reviews_sd-bg-decor',
                [
                    'label'   => esc_html__( 'Background Decor Clip Part', 'tm-reviews' ),
                    'type'    => Controls_Manager::SELECT,
                    'default' => '',
                    'options' => [
                        ''                       =>         esc_attr__('Disable','tm-reviews'),
                        'top-left'               =>         esc_attr__('Top Left','tm-reviews'),
                        'top-right'              =>         esc_attr__('Top Right','tm-reviews'),
                        'bottom-left'            =>         esc_attr__('Bottom Left','tm-reviews'),
                        'bottom-right'           =>         esc_attr__('Bottom Right','tm-reviews')
                    ],
                    'prefix_class' => 'tm_reviews_sd-bg-decor-',
                ]
            );

            $element->end_controls_section();
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
 * Returns instance of TMReviews_Helper_Core_Elements_Ext_Section
 *
 * @return object
 */
function tmreviews_helper_core_elements_section() {
	return TMReviews_Helper_Core_Elements_Section::get_instance();
}
