<?php
namespace Elementor\CustomControl;

use \Elementor\Base_Data_Control;

class TMReviewsImageSelector_Control extends Base_Data_Control {


	const ImageSelector = 'image_selector';

	/**
	 * Set control type.
	 */
	public function get_type() {
		return self::ImageSelector;
	}

	/**
	 * Enqueue control scripts and styles.
	 */
	public function enqueue() {
		$url = TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'/elementor/custom-controle/image-selector/';
		// Styles
		wp_enqueue_style('image-selector', $url.'image-selector.css', array(), '');
	}

	/**
	 * Set default settings
	 */
	protected function get_default_settings() {
		return [
			'label_block' => true,
			'toggle' => true,
			'options' => [],
            'style_mod' => '',
		];
	}
	
	/**
	 * control field markup
	 */
	public function content_template() {
		$control_uid = $this->get_control_uid('{{ value }}');
		?>
		<div class="elementor-control-field">
			<label class="elementor-control-title">{{{ data.label }}}</label>
			<div class="elementor-control-image-selector-wrapper {{{ data.style_mod }}}">
				<# _.each( data.options, function( options, value ) { #>
				<input id="<?php echo $control_uid; ?>" type="radio" name="elementor-image-selector-{{ data.name }}-{{ data._cid }}" value="{{ value }}" data-setting="{{ data.name }}">
				<label class="elementor-image-selector-label tooltip-target" for="<?php echo $control_uid; ?>" data-tooltip="{{ options.title }}" title="{{ options.title }}">
					<img src="{{ options.url }}" alt="{{ options.title }}">
					<span class="elementor-screen-only">{{{ options.title }}}</span>
				</label>
				<# } ); #>
			</div>
		</div>
		<# if ( data.description ) { #>
		<div class="elementor-control-field-description">{{{ data.description }}}</div>
		<# } #>
		<?php
	}

}