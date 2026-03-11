<?php

if ( function_exists( 'vc_add_shortcode_param' ) ) {
    vc_add_shortcode_param( 'tmreviews_date_time_picker', 'tmreviews_date_time_picker_settings_field', TMREVIEWS_THEME_HELPER_ROOT_DIR . '/assets/params/js/bootstrap-datetimepicker.min.js');
}

function tmreviews_date_time_picker_settings_field($settings, $value) {
    $param_name     = isset($settings['param_name']) ? $settings['param_name'] : '';
    $type           = isset($settings['type']) ? $settings['type'] : '';
    $class          = isset($settings['class']) ? $settings['class'] : '';
    $output         = '<div class="tmreviews_datatime_picker"><div class="tmreviews_date_picker_bg"></div><input data-format="dd/MM/yyyy hh:mm:ss" readonly class="wpb_vc_param_value ' . esc_attr($param_name) . ' ' . esc_attr($type) . ' ' . esc_attr($class) . '" name="' . $param_name . '" value="'.$value.'" /><span class="add-on"><span class="fl-calendar-icon dashicons dashicons-calendar-alt"></span></span></div>';

    return $output;
}