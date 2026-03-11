<?php

namespace ElementorWidgets\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Elementor Custom Widget Test
 */
class Custom_Widget extends Widget_Base {

	public function get_name() {
		return 'test_addon';
	}

	public function get_title() {
		return __('Test Box', 'tm-reviews');
	}

	public function get_categories() {
		return ['general'];
	}

	/**
	 * Register the widget controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'page_layout_section',
			[
				'label' => __('Page Layout', 'tm-reviews'),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		//Image selector
		$this->add_control(
			'page_layout',
			[
				'label' => esc_html__('Layout', 'tm-reviews'),
				'type' => \Elementor\CustomControl\ImageSelector_Control::ImageSelector,
				'options' => [
					'left-sidebar' => [
						'title' => esc_html__('Left', 'tm-reviews'),
						'url' => 'http://localhost/test/wp-content/themes/test/assets/images/left-sidebar.png',
					],
					'right-sidebar' => [
						'title' => esc_html__('Right', 'tm-reviews'),
						'url' => 'http://localhost/test/wp-content/themes/test/assets/images/right-sidebar.png',
					],
					'no-sidebar' => [
						'title' => esc_html__('No Sidebar', 'tm-reviews'),
						'url' => 'http://localhost/test/wp-content/themes/test/assets/images/no-sidebar.png',
					],
				],
				'default' => 'right-sidebar',
			]
		);
		$this->end_controls_section();

	}
}