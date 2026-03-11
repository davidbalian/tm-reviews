<?php
namespace TMReviewsElementorControls;
if (!defined('ABSPATH')) exit; // Exit if accessed directly

class TMReviewsElementor_Custom_Controls {

	public function includes() {
		require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/elementor/custom-controle/image-selector/image-selector-control.php');
	}

	public function register_controls() {
		$this->includes();
		$controls_manager = \Elementor\Plugin::$instance->controls_manager;
		$controls_manager->register_control(\Elementor\CustomControl\TMReviewsImageSelector_Control::ImageSelector, new \Elementor\CustomControl\TMReviewsImageSelector_Control());
	}

	public function __construct() {
		add_action('elementor/controls/controls_registered', [$this, 'register_controls']);
	}

}
new TMReviewsElementor_Custom_Controls();