<?php
/*
 * @copyright
 * Version: 1.0.0
 * Author: Alessandro Benoit
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/
if ( ! class_exists( 'tmreviews_helping_acf_field_customiconpicker' ) ) :
class tmreviews_helping_acf_field_customiconpicker extends acf_field {

    // Vars
    var $settings,
        $defaults,
        $json_content_font_awesome;

    /**
     *  __construct
     *
     *  @since	1.0.0
     */
    function __construct() {

        // Vars
        $this->name = 'customiconpicker';
        $this->label = esc_html__('Custom Icon Picker');
        $this->category = "Basic";

        parent::__construct();

        // Settings
        $this->settings = array(
            'dir' 		            =>  plugins_url('', __FILE__) . '/',
            'path'		            =>	plugins_url('', __FILE__) . '/',
            'config_custom_font' 	=> 	plugins_url('./icons/config_custom_font.json', __FILE__),
            'version' 	            => 	'1.0.0'
        );

        // Apply a filter so that you can load icon set from theme
        $this->settings = apply_filters( 'acf/acf_field_customiconpicker/settings', $this->settings );


        // Load icons list from the icons JSON file
        if ( is_admin() ){
            $custom_font_json_file = @file_get_contents( $this->settings['config_custom_font'] );

            $this->json_content_custom_font = @json_decode( $custom_font_json_file, true );
        }

    }


    /**
     *  create_field()
     *
     *  @param	$field - An array holding all the field's data
     *
     *  @since	1.0.0
     */
    function render_field( $field ) {

        if ( !isset( $this->json_content_custom_font['custom_icons'] ) ){
            esc_html__('No icons found');
            return;
        }




        // icons SELECT input
        echo '<select name="'. $field['name'] .'" id="'. $field['name'] .'" class="acf-iconpicker">';
        echo '<option value="">'. __('None').'</option>';
        foreach ( $this->json_content_custom_font['custom_icons'] as $custom_icons ) {
            $fonts_full = $this->json_content_custom_font['css_prefix_text'] . $custom_icons['css'];
            echo '<option value="'. $fonts_full .'" '. selected( $field['value'], $fonts_full, false ) .'>'. $custom_icons['css'] .'</option>';
        }
        foreach ( $this->json_content_custom_font['font_custom'] as $font_custom ) {
            $fonts_full = $this->json_content_custom_font['css_prefix_text'] . $font_custom['css'];
            echo '<option value="'. $fonts_full .'" '. selected( $field['value'], $fonts_full, false ) .'>'. $font_custom['css'] .'</option>';
        }


        echo '</select>';

    }


    /**
     *  input_admin_enqueue_scripts()
     *
     *  @since	1.0.0
     */
    function input_admin_enqueue_scripts() {

        // Scripts
        wp_register_script( 'acf-customiconpicker', $this->settings['dir'] . './js/jquery.customiconpicker.min.js', array('jquery'), $this->settings['version'] );
        wp_register_script( 'acf-customiconpicker-input', $this->settings['dir'] . './js/input.js', array('acf-customiconpicker'), $this->settings['version'] );
        wp_enqueue_script( 'acf-customiconpicker-input' );

        // Styles
        wp_register_style( 'acf-customiconpicker-style', $this->settings['dir'] . './css/jquery.customiconpicker.css', false, $this->settings['version'] );
        wp_enqueue_style( array( 'acf-customiconpicker-style' ) );

    }

}

new tmreviews_helping_acf_field_customiconpicker();
endif; // class_exists check