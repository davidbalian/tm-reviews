<?php
    if ( ! defined( 'ABSPATH' ) ) { exit; }

    if(function_exists('vc_add_shortcode_param')) {
        vc_add_shortcode_param('tmreviews_checkbox' , 'tmreviews_checkbox_settings_field');
    }
function tmreviews_checkbox_settings_field($settings, $value) {
                $param_name = isset($settings['param_name']) ? $settings['param_name'] : '';
                $type = isset($settings['type']) ? $settings['type'] : '';
                $options = isset($settings['options']) ? $settings['options'] : '';
                $class = isset($settings['class']) ? $settings['class'] : '';

                $output = $checked = '';

                if(is_array($options) && !empty($options)){
                    foreach($options as $key => $opts){
                        $checked = "";
                        $animation_class = 'active';
                        $data_val = $key;
                        if($value == $key){
                            $checked = "checked";
                            $animation_class = '';
                        }

                        $uniq_id = uniqid('dfd_single_checkbox-'.rand());
                        if(isset($opts['label']))
                            $label = $opts['label'];
                        else
                            $label = '';

                        $output .= '<div class="tmreviews_checkbox_wrap">
									<input type="checkbox" name="'.esc_attr($param_name).'" value="'.esc_attr($value).'" class="wpb_vc_param_value ' . esc_attr($param_name) . ' ' . esc_attr($type) . ' ' . esc_attr($class) . '" id="'.esc_attr($uniq_id).'" '.$checked.'>
									<label class="tmreviews_checkbox_label" for="'.esc_attr($param_name).'" data-value="'.esc_attr($data_val).'">
										<span class="fl-btn-checkbox '.esc_attr($animation_class).'"></span>
									</label>
									<span class="param-title">'.esc_html($label).'</span>
								</div>';
                    }
                }

                $output .= '<script >
							jQuery("#'.esc_js($uniq_id).'").next(".tmreviews_checkbox_label").click(function(){
								var $self = jQuery(this),
									$button = $self.find(".fl-btn-checkbox"), 
									$checkbox = $self.siblings("#'.esc_js($uniq_id).'");
										
								$button.toggleClass("active");

								if($self.find(".fl-btn-checkbox").hasClass("active")) {
									$checkbox.removeAttr("checked").val("");
								} else {
									$checkbox.attr("checked","checked").val($self.data("value"));
								}

								$checkbox.trigger("change");
							});
						</script>';

                return $output;
            }
