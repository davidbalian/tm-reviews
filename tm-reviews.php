<?php
/**
 * Plugin Name: TM Reviews
 * Plugin URI:: https://themeforest.net/user/tm_colors
 * Description: Reviews plugin for TM_Colors, VK Themes and Templines themes. Don't delete this plugin.
 * Version: 2.5.7
 * Author:TM_Colors
 * Author URI: https://themeforest.net/user/tm_colors
 * License: GPL v2
 */

require 'plugin-update-checker/plugin-update-checker.php';
$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'http://assets.templines.com/plugins/theme/gazek/tm-review.json',
    __FILE__,
    'tm-review'
);

/**
 * Класс для работы с отзывами
 */
class TMReviews_Plugin {
    /**
     * Получение популярных вариантов из комментариев
     *
     * @param string $meta_key Ключ метаданных (pros или cons)
     * @param int $limit Максимальное количество возвращаемых вариантов
     * @return array Массив популярных вариантов
     */
    public function get_popular_items($meta_key, $limit = 10) {
        global $wpdb;

        // Получаем все значения метаданных
        $results = $wpdb->get_col($wpdb->prepare(
            "SELECT meta_value FROM {$wpdb->commentmeta} WHERE meta_key = %s",
            $meta_key
        ));
        
        $all_items = array();
        foreach ($results as $serialized) {
            $items = maybe_unserialize($serialized);
            if (is_array($items)) {
                // Фильтруем пустые значения
                $items = array_filter($items, function($item) {
                    return !empty(trim($item));
                });
                $all_items = array_merge($all_items, $items);
            }
        }

        // Удаляем дубликаты без учета регистра
        $all_items = array_map('mb_strtolower', $all_items);
        $all_items = array_map('trim', $all_items);
        
        // Подсчитываем количество повторений
        $item_counts = array_count_values($all_items);
        arsort($item_counts);

        // Возвращаем только уникальные значения
        $popular_items = array_slice(array_keys($item_counts), 0, $limit);
        
        // Приводим первую букву к верхнему регистру
        return array_map('ucfirst', $popular_items);
    }

    /**
     * Рендеринг полей pros и cons
     */
    public function render_comments_meta_pros_cons_fields() {
        if(get_post_type() == tmreviews_get_post_type()){
            try {
                $pros_suggestions = $this->get_popular_items('tmreviews_review_pros');
                $cons_suggestions = $this->get_popular_items('tmreviews_review_cons');
            } catch (Exception $e) {
                $pros_suggestions = array();
                $cons_suggestions = array();
            }

            // Подключаем стили и скрипты
            wp_enqueue_style('tmreviews-suggestions', plugins_url('assets/css/suggestions.css', __FILE__));
            wp_enqueue_script('tmreviews-suggestions', plugins_url('assets/js/suggestions.js', __FILE__), array('jquery'), null, true);
            
            // Передаем данные в JavaScript
            wp_localize_script('tmreviews-suggestions', 'tmreviewsSuggestions', array(
                'pros' => $pros_suggestions,
                'cons' => $cons_suggestions
            ));

            echo '<div class="tmreviews-meta-fields">';
            
            // Pros field
            echo '<div class="tmreviews-meta-field">';
            echo '<label for="tmreviews_review_pros[]">' . esc_html__('Pros', 'tmreviews') . '</label>';
            echo '<div class="tmreviews-input-wrapper">';
            echo '<input type="text" name="tmreviews_review_pros[]" class="tmreviews-pros-input" value="" placeholder="' . esc_attr__('Enter pros', 'tmreviews') . '" autocomplete="off" />';
            echo '</div>';
            echo '</div>';
            
            // Cons field
            echo '<div class="tmreviews-meta-field">';
            echo '<label for="tmreviews_review_cons[]">' . esc_html__('Cons', 'tmreviews') . '</label>';
            echo '<div class="tmreviews-input-wrapper">';
            echo '<input type="text" name="tmreviews_review_cons[]" class="tmreviews-cons-input" value="" placeholder="' . esc_attr__('Enter cons', 'tmreviews') . '" autocomplete="off" />';
            echo '</div>';
            echo '</div>';
            
            echo '</div>';
        }
    }
}

// Register activation hook
register_activation_hook(__FILE__, 'tmreviews_plugin_activation');

function tmreviews_plugin_activation() {
    if (!function_exists('wp_get_current_user')) {
        include(ABSPATH . "wp-includes/pluggable.php");
    }
    
    $plugin = new TMReviews__Helping_Addons();
    $plugin->create_plugin_pages();
}




/**====================================================================
==  Make sure we don't expose any info if called directly
====================================================================*/
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

/**====================================================================
==  Load Text domain
====================================================================*/
add_action('plugins_loaded', 'tmreviews_helper_load_textdomain');
function tmreviews_helper_load_textdomain(){
    load_plugin_textdomain('tm-reviews', false, dirname(plugin_basename(__FILE__)) . '/languages/');
}

// Подключаем файлы
require_once plugin_dir_path(__FILE__) . 'custom_taxonomy/places.php';
require_once plugin_dir_path(__FILE__) . 'widgets/recent-places-widget.php';
require_once plugin_dir_path(__FILE__) . 'widgets/popular-categories-widget.php';

require_once plugin_dir_path(__FILE__) . 'includes/tmreviews-notifications.php';
require_once plugin_dir_path(__FILE__) . 'dashboard/admin-scripts.php';

// Подключаем стили виджетов
function tmreviews_enqueue_widget_styles() {
    wp_enqueue_style('tmreviews-widgets', plugin_dir_url(__FILE__) . 'assets/css/widgets.css', array(), '1.0.0');
    wp_enqueue_style('tmreviews-categories-widget', plugin_dir_url(__FILE__) . 'assets/css/tmreviews-categories-widget.css', array(), '1.0.0');
}
add_action('wp_enqueue_scripts', 'tmreviews_enqueue_widget_styles');

function tmreviews_init(){
    // Добавляем фильтр для удаления атрибута sizes из изображений
    add_filter('wp_get_attachment_image_attributes', 'tmreviews_remove_image_sizes_attr', 10, 3);
}
add_action('plugins_loaded', 'tmreviews_init');

/**
 * Удаляем атрибут sizes из изображений
 *
 * @param array $attr Массив атрибутов изображения
 * @param WP_Post $attachment Объект вложения
 * @param string|array $size Размер изображения
 * @return array Модифицированный массив атрибутов
 */
function tmreviews_remove_image_sizes_attr($attr, $attachment, $size) {
    unset($attr['sizes']);
    return $attr;
}

define('TMREVIEWS_THEME_HELPER_PLUGIN_PATH', plugin_dir_path(__FILE__));
defined('TMREVIEWS_HELPING_PLUGIN_VERSION' )   or define( 'TMREVIEWS_HELPING_PLUGIN_VERSION', '1.0');
defined('TMREVIEWS_THEME_HELPER_ROOT_DIR' )    or define( 'TMREVIEWS_THEME_HELPER_ROOT_DIR', plugins_url() . '/tm-reviews');
defined('TMREVIEWS_HELPING_PREVIEW_IMAGE')     or define('TMREVIEWS_HELPING_PREVIEW_IMAGE', plugin_dir_url(__FILE__) . '/assets/images/presentation-images');
defined('TMREVIEWS_THEME_HELPER_URL' )         or define( 'TMREVIEWS_THEME_HELPER_URL', plugin_dir_url( __FILE__ ));



/**====================================================================
==  Require TMReviews_ theme
====================================================================*/
if( !class_exists('TMReviews__Helping_Addons') ) {

    class TMReviews__Helping_Addons {

        // Construct
        public function __construct() {
            $this->addOptions();
            $this->addSocial();

            $this->addLike();
            $this->addCustomFunction();
            $this->addCustomTaxonomyServices();
            $this->addDashboard();
            $this->addTemplates();
            $this->template_pages_install();

            add_action('after_setup_theme', array($this, 'addVcCustomElements'));
            add_action('acf/include_field_types',  array($this, 'include_field_types'));

            add_action('wp_enqueue_scripts', array($this, 'addStyles'));
            
            // Добавляем AJAX обработчики для автозаполнения
            add_action('wp_ajax_tmreviews_get_pros', array($this, 'get_pros_suggestions'));
            add_action('wp_ajax_nopriv_tmreviews_get_pros', array($this, 'get_pros_suggestions'));
            add_action('wp_ajax_tmreviews_get_cons', array($this, 'get_cons_suggestions'));
            add_action('wp_ajax_nopriv_tmreviews_get_cons', array($this, 'get_cons_suggestions'));

            $this->addYouzify();
        }

        /**
         * Получение подсказок для pros
         */
        public function get_pros_suggestions() {
            check_ajax_referer('tmreviews_autocomplete_nonce', '_ajax_nonce');
            
            global $wpdb;
            $term = '%' . $wpdb->esc_like(sanitize_text_field($_REQUEST['term'])) . '%';
            
            // Получаем все значения pros из массива meta_value
            $query = $wpdb->prepare(
                "SELECT DISTINCT meta_value 
                FROM {$wpdb->commentmeta} 
                WHERE meta_key LIKE %s 
                AND meta_value LIKE %s 
                AND meta_value != '' 
                LIMIT 10",
                'tmreviews_review_pros%',
                $term
            );
            
            $results = $wpdb->get_col($query);
            
            $suggestions = array();
            if ($results && !is_wp_error($results)) {
                foreach ($results as $value) {
                    $value = trim($value);
                    if (!empty($value)) {
                        $suggestions[] = $value;
                    }
                }
            }
            
            wp_send_json($suggestions);
            wp_die();
        }

        /**
         * Получение подсказок для cons
         */
        public function get_cons_suggestions() {
            check_ajax_referer('tmreviews_autocomplete_nonce', '_ajax_nonce');
            
            global $wpdb;
            $term = '%' . $wpdb->esc_like(sanitize_text_field($_REQUEST['term'])) . '%';
            
            // Получаем все значения cons из массива meta_value
            $query = $wpdb->prepare(
                "SELECT DISTINCT meta_value 
                FROM {$wpdb->commentmeta} 
                WHERE meta_key LIKE %s 
                AND meta_value LIKE %s 
                AND meta_value != '' 
                LIMIT 10",
                'tmreviews_review_cons%',
                $term
            );
            
            $results = $wpdb->get_col($query);
            
            $suggestions = array();
            if ($results && !is_wp_error($results)) {
                foreach ($results as $value) {
                    $value = trim($value);
                    if (!empty($value)) {
                        $suggestions[] = $value;
                    }
                }
            }
            
            wp_send_json($suggestions);
            wp_die();
        }


        function include_field_types( $version ) {
            include_once('afc_custom_fields/icon_picker/acf-fonticonpicker-v5.php');
            include_once('afc_custom_fields/custom_icon_picker/acf-customiconpicker-v5.php');
            include_once('afc_custom_fields/image_selector/acf-image_select-v5.php');
        }


        public function addStyles() {
            wp_enqueue_style('places-vc', plugin_dir_url( __FILE__ ). '/vc_custom/css/places_styles.css', array(),'1.0');

            // Подключаем jQuery UI для автозаполнения
            wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
            wp_enqueue_script('jquery-ui-autocomplete');
            
            // Подключаем скрипт автозаполнения
            wp_enqueue_script('tmreviews-autocomplete', plugin_dir_url(__FILE__) . 'assets/js/tmreviews-autocomplete.js', array('jquery', 'jquery-ui-autocomplete'), '1.0', true);
            
            // Передаем данные в JavaScript
            wp_localize_script('tmreviews-autocomplete', 'tmreviews_autocomplete', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('tmreviews_autocomplete_nonce'),
                'no_results' => __('No results found', 'tm-reviews')
            ));
        }


        public function addOptions() {

            if(!get_option('tmreviews_post_type')){
                $tmreviews_post_type = array();
                $tmreviews_post_type['old'] = 'places';
                $tmreviews_post_type['new'] = 'places';
                add_option( 'tmreviews_post_type', $tmreviews_post_type, true);
            }

        }
        /** Add Youzify*/
        public function addYouzify() {
            if(class_exists('BuddyPress')){
                require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'youzify/youzify.php');
            }
        }

        /** Add Social Share Function*/
        public function addSocial() {
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'function/social-share/social.php');
        }
        public function addTemplates() {
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'inc/class-template-loader.php');
        }

        /** Add Like Function*/
        public function addLike() {
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'function/like/post-like.php');
        }
        /** Add Custom Function*/
        public function addCustomFunction() {
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'function/custom_function.php');
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'function/public_function.php');
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'function/load-more-car.php');
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'membership/membership_option.php');
        }

        /** Add Custom Taxonomy Work*/
        public function addCustomTaxonomyServices() {
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'custom_taxonomy/places.php');
            require_once('acf-metaboxes/acf-metaboxes.php');
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'acf-metaboxes/places/places_option.php');
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'custom_taxonomy/taxonomy-meta/taxonomy_option.php');


        }
        /** Add Custom Widgets*/
        public function addDashboard() {
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'dashboard/dashboard.php');
        }



        public function tmreviews_helping_addons_init() {
            // Load Custom Maps.php For VC.
            $this->tmreviews_helping_addons_vc_integration();
        }

        public function tmreviews_helping_addons_vc_integration()
        {
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'vc_custom/vc_include.php');
        }

        public function template_pages_install(){
            require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'templates/reviews-walker.php');
            add_action('admin_init', array($this, 'create_plugin_pages'));
        }

        public function create_plugin_pages() {
            if (!function_exists('wp_get_current_user')) {
                include(ABSPATH . "wp-includes/pluggable.php");
            }

            // Create Add Place page
            $add_place_page = array(
                'post_title'    => esc_html__('Add Place', 'tm-reviews'),
                'post_content'  => '[tmreviews_add_places]',
                'post_status'   => 'publish',
                'post_type'     => 'page'
            );
            
            // Create My Places page
            $my_places_page = array(
                'post_title'    => esc_html__('My Places', 'tm-reviews'),
                'post_content'  => '[tmreviews_your_places]',
                'post_status'   => 'publish',
                'post_type'     => 'page'
            );
            
            // Create Your Profile page
            $your_profile_page = array(
                'post_title'    => esc_html__('Your Profile', 'tm-reviews'),
                'post_content'  => '[tmreviews_account_settings]',
                'post_status'   => 'publish',
                'post_type'     => 'page'
            );
            
            // Check if pages don't exist before creating them
            $existing_add_place = get_page_by_title('Add Place');
            $existing_my_places = get_page_by_title('My Places');
            $existing_your_profile = get_page_by_title('Your Profile');
            
            if (!$existing_add_place) {
                $add_place_id = wp_insert_post($add_place_page);
                update_option('tmreviews_add_place_page_id', $add_place_id);
            }
            
            if (!$existing_my_places) {
                $my_places_id = wp_insert_post($my_places_page);
                update_option('tmreviews_my_places_page_id', $my_places_id);
            }
            
            if (!$existing_your_profile) {
                $your_profile_id = wp_insert_post($your_profile_page);
                update_option('tmreviews_your_profile_page_id', $your_profile_id);
            }
        }

        /** Add custom VC Function and elements*/
        public function addVcCustomElements() {
            if ( class_exists( 'Vc_Manager', false ) ) {
                require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'vc_custom/vc.php');
                require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH.'vc_custom/custom_params_option.php');
            }
            if(class_exists('Vc_Manager')){
                add_action('init', array($this,'tmreviews_helping_addons_init'),40);
            }

        }

    } // end of class

} // end of class_exists


function tmreviews_helping_addons(){
    return new TMReviews__Helping_Addons();
}

tmreviews_helping_addons();







function tmreviews_get_post_type(){
    return get_option('tmreviews_post_type')['new'];
}

function tmreviews_get_post_type_old(){
    return get_option('tmreviews_post_type')['old'];
}



// Custom Elementor Option
require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/elementor/elementor.php' );
function TMReviews_Helper_Core_Elementor() {
    $instance = TMReviews_Helper_Core_Elementor::instance( __FILE__, TMREVIEWS_THEME_HELPER_PLUGIN_PATH );

    return $instance;
}

TMReviews_Helper_Core_Elementor();




//Admin Notice
function tmreviews_admin_notice() {
    $args = array(
        'post_type' => tmreviews_get_post_type(),
        'post_status' => 'pending',
        'posts_per_page'		    => -1,
    );
    $places = get_posts($args);
    ?>
    <?php foreach ($places as $p){ ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <?php _e( 'Sended New Item to review.', 'tm-reviews' ); ?>
                    <a href="<?php echo get_admin_url() . 'post.php?post='. $p->ID .'&action=edit';?>">
                        <?php echo __('Please check', 'tm-reviews');?>
                    </a>
            </p>
        </div>
        <?php } ?>
            <?php $users = get_users();
    foreach ($users as $user){ ?>
                <?php
        $tmreviews_dl_sended = get_user_meta($user->ID, 'tmreviews_dl_sended', 'true');
        if(isset($tmreviews_dl_sended) && $tmreviews_dl_sended == 'sended'){  ?>
                    <div class="notice notice-warning is-dismissible">
                        <p>
                            <?php _e( 'The user sent the documents Verification.', 'tm-reviews' ); ?>
                                <a href="<?php echo get_admin_url() . 'user-edit.php?user_id=' . $user->ID;?>">
                                    <?php echo __('Please check', 'tm-reviews');?>
                                </a>
                        </p>
                    </div>
                    <?php } ?>
                        <?php } ?>
                            <?php
}
add_action( 'admin_notices', 'tmreviews_admin_notice' );




add_action( 'wp_enqueue_scripts', 'tmreviews_equeue_scripts');
function tmreviews_equeue_scripts(){
    wp_enqueue_script   ('tmreviews__custom_admin_js',  plugin_dir_url( __FILE__ ) .  '/assets/js/reviews_scripts.js', '', '', true);
    wp_enqueue_script   ('tmreviews__custom_uikit_js',  plugin_dir_url( __FILE__ ) .  '/assets/js/uikit.js', '', '', true);
    wp_enqueue_media();
    wp_enqueue_script   ('tmreviews__custom_upload_js',  plugin_dir_url( __FILE__ ) .  '/assets/js/custom-upload.js', '', '', true);
}



add_action('wp_enqueue_scripts', 'tmreviews_enqueue_style');
function tmreviews_enqueue_style(){
    wp_enqueue_style   ( 'tmreviews_custom_font_acf', plugin_dir_url( __FILE__ ). '/afc_custom_fields/custom_icon_picker/css/jquery.customiconpicker.css');
    wp_enqueue_style   ( 'tmreviews_nice_select_css', plugin_dir_url( __FILE__ ). '/assets/css/nice-select.css');
    wp_enqueue_style   ( 'tmreviews_libs_css', plugin_dir_url( __FILE__ ). '/assets/css/libs.min.css');
    //wp_enqueue_style   ( 'tmreviews_nice_select_css', plugin_dir_url( __FILE__ ). '/assets/css/style.css');
    wp_enqueue_script   ( 'tmreviews_nice_select_js', plugin_dir_url( __FILE__ ). '/assets/js/jquery.nice-select.min.js');
    wp_enqueue_script   ( 'tmreviews_sticky_js', plugin_dir_url( __FILE__ ). '/assets/js/theia-sticky-sidebar.js');
}


add_filter( 'script_loader_tag', 'tmreviews_helping_remove_type', 10, 3 );
add_filter( 'style_loader_tag', 'tmreviews_helping_remove_type', 10, 3 );  // Ignore the $media argument to allow for a common function.
function tmreviews_helping_remove_type( $markup, $handle, $href ) {
    //error_log( 'Markup: ' . $markup );
    //error_log( 'Handle: ' . $handle );
    //error_log( 'Href: ' . $href );
    // Remove the 'type' attribute.
    $markup = str_replace( " type='text/javascript'", '', $markup );
    $markup = str_replace( " type='text/css'", '', $markup );
    return $markup;
}
// Store and process wp_head output to operate on inline scripts and styles.
add_action( 'wp_head', 'tmreviews_helping_wp_head_ob_start', 0 );
function tmreviews_helping_wp_head_ob_start() {
    ob_start();
}
add_action( 'wp_head', 'tmreviews_helping_wp_head_ob_end', 10000 );
function tmreviews_helping_wp_head_ob_end() {
    $wp_head_markup = ob_get_contents();
    ob_end_clean();

    // Remove the 'type' attribute. Note the use of single and double quotes.
    $wp_head_markup = str_replace( " type='text/javascript'", '', $wp_head_markup );
    $wp_head_markup = str_replace( ' type="text/javascript"', '', $wp_head_markup );
    $wp_head_markup = str_replace( ' type="text/css"', '', $wp_head_markup );
    $wp_head_markup = str_replace( " type='text/css'", '', $wp_head_markup );
    echo $wp_head_markup;
}


// Store and process wp_footer output to operate on inline scripts and styles.
add_action( 'wp_footer', 'tmreviews_helping_wp_footer_ob_start', 0 );
function tmreviews_helping_wp_footer_ob_start() {
    ob_start();
}

add_action( 'wp_footer', 'tmreviews_helping_wp_footer_ob_end', 10000 );
function tmreviews_helping_wp_footer_ob_end() {
    $wp_footer_markup = ob_get_contents();
    ob_end_clean();

    // Remove the 'type' attribute. Note the use of single and double quotes.
    $wp_footer_markup = str_replace( " type='text/javascript'", '', $wp_footer_markup );
    $wp_footer_markup = str_replace( ' type="text/javascript"', '', $wp_footer_markup );
    $wp_footer_markup = str_replace( ' type="text/css"', '', $wp_footer_markup );
    $wp_footer_markup = str_replace( " type='text/css'", '', $wp_footer_markup );
    echo $wp_footer_markup;
}






//Google Maps API
function my_acf_google_map_api( $api ){
    $api['key'] = gazek_get_theme_mod('google_api_key');
    return $api;
}
add_filter('acf/fields/google_map/api', 'my_acf_google_map_api');





// COMMENT RATING
add_action( 'comment_form', 'isnaider_extend_comment_rating_fields' );
//add_filter( 'comment_text', 'isnaider_extend_comment_rating');
add_action( 'comment_post', 'isnaider_save_extend_comment_meta_rating' );
add_filter('comment_form_fields', 'isnaider_reorder_comment_fields' );
function isnaider_extend_comment_rating_fields() {
    global $post;
    if($post->post_type == tmreviews_get_post_type()){
        echo '<div class="comment-form-rating col-md-12">
        <label>'.esc_attr__('Your rating', 'tm-reviews').'</label>
        <select name="rating" id="rating-autos" required style="display: none;">
                            <option value="">Rate…</option>
                            <option value="5">Perfect</option>
                            <option value="4">Good</option>
                            <option value="3">Average</option>
                            <option value="2">Not that bad</option>
                            <option value="1">Very poor</option>
                        </select></div>';
    }
    echo '<script>
        jQuery.noConflict()(function($) {
            jQuery(".comments-list").find("#rating-autos").remove();
        });
    </script>';
}




add_action( 'comment_form', 'tmreviews_extend_comment_employers' );
add_action( 'comment_post', 'tmreviews_extend_comment_employers_save' );

function tmreviews_extend_comment_employers(){
    global $post;
    if($post->post_type == tmreviews_get_post_type()){
        $empls = tm_reviews_get_metabox('employers', $post->ID);
        if(isset($empls) && is_array($empls) && !empty($empls)){
            echo '<select name="employers" class="fl-employers-comment-wrap">';
            echo '<option value="all">';
                echo __('About ', 'tm-reviews') . get_the_title($post->ID);
            echo '</option>';
            foreach ($empls as $empl){
                echo '<option value="'.$empl['empl_name'].'">' . esc_html($empl['empl_name']) . ' - ' . $empl['empl_position'] . '</option>';
            }
            echo '</select>';
        }
    }
}
function tmreviews_extend_comment_employers_save($comment_id){
    if( !empty( $_POST['employers'] ) ){
        add_comment_meta( $comment_id, 'employer', $_POST['employers'] );
    }
}




function isnaider_save_extend_comment_meta_rating( $comment_id ){
    if( !empty( $_POST['rating'] ) ){
        $rating = intval($_POST['rating']);
        add_comment_meta( $comment_id, 'rating', $rating );
        $comment_id_7 = get_comment( $comment_id );
        $post_id = $comment_id_7->comment_post_ID ;
        //Reviews
        $reviews = get_comments(array('post_id' => $post_id));
        if(isset($reviews) && !empty($reviews)){
            $i = 0;
            foreach ($reviews as $r){
                $rate = get_comment_meta( $r->comment_ID, 'rating', true );
                if(isset($rate) && $rate != ''){
                    $i++;
                }
            }
        }
        $reviews_count = $i;

        $total = 0;
        if(isset($reviews) && !empty($reviews)){
            foreach ($reviews as $c){
                $total += intval(get_comment_meta($c->comment_ID, 'rating', true));
            }
            if($total != 0){
                $average = $total / $reviews_count;
            }else{
                $average = 0;
            }

            $post_average_meta = get_post_meta($post_id, 'tmreviews_post_average', true);
            if(isset($post_average_meta) && $post_average_meta != ''){
                update_post_meta( $post_id, 'tmreviews_post_average', $average );
            } else {
                add_post_meta( $post_id, 'tmreviews_post_average', $average );
            }
        }
    }
}



$args_average = array(
    'post_type' => tmreviews_get_post_type(),
    'status' => 'published',
    'posts_per_page' => -1
);
$places_for_average_meta = get_posts($args_average);
foreach ($places_for_average_meta as $p){
    $reviews = get_comments(array('post_id' => $p->ID));
    $i = 0;
    if(isset($reviews) && !empty($reviews)){

        foreach ($reviews as $r){
            $rate = get_comment_meta( $r->comment_ID, 'rating', true );
            if(isset($rate) && $rate != ''){
                $i++;
            }
        }
    }

    if(get_post_meta($p->ID, 'review_rating', true) != ''){
        $i++;
    }


    $reviews_count = $i;
    $total = 0;
    if(isset($reviews) && !empty($reviews)){
        foreach ($reviews as $c){
            $total += intval(get_comment_meta($c->comment_ID, 'rating', true));
        }

        if(get_post_meta($p->ID, 'review_rating', true) != ''){
            $total += intval(get_post_meta($p->ID, 'review_rating', true));
        }

        if($total != 0){
            $average = $total / $reviews_count;
        }else{
            $average = 0;
        }
        $post_average_meta = get_post_meta($p->ID, 'tmreviews_post_average', true);
        if(isset($post_average_meta) && $post_average_meta != ''){
            update_post_meta( $p->ID, 'tmreviews_post_average', $average );
        } else {
            add_post_meta( $p->ID, 'tmreviews_post_average', $average );
        }
    }
}



function isnaider_extend_comment_rating(){

    $comment_id = get_comment_ID();

    if(get_post_type() == tmreviews_get_post_type()){
        if( $rating = intval( get_comment_meta( $comment_id, 'rating', true ) ) ) {
            $rating_icons = '';
            $i = 1;
            while ($i <= $rating){
                $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                $i++;
            }
            if($rating < 5){
                $asd = 5 - $rating;
                $k = 1;
                while ($k <= $asd){
                    $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                    $k++;
                }
            }
            $commentrating = '<div class="fl-rate-icons">'.
                $rating_icons .
                '</div>
                            <div class="star-rating"><span style="width:' . ( $rating * 20 ) . '%">' . sprintf( __( '%s out of 5', 'tm-reviews' ), $rating ) . '</span></div>';
            return $commentrating;
        } else {
            return '';
        }
    }
}

function isnaider_extend_comment_rating_single(){

    $comment_id = get_comment_ID();

    if(get_post_type() == tmreviews_get_post_type()){
        if( $rating = intval( get_comment_meta( $comment_id, 'rating', true ) ) ) {
            $rating_icons = '';
            $i = 1;
            while ($i <= $rating){
                $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                $i++;
            }
            if($rating < 5){
                $asd = 5 - $rating;
                $k = 1;
                while ($k <= $asd){
                    $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                    $k++;
                }
            }
            $commentrating = '<div class="fl-rate-icons">' . $rating_icons . '</div>';
            return $commentrating;
        } else {
            return '';
        }
    }
}



function tmreviews_extend_comment_rating_single_by_postid($id){

    $comment_id = $id;

    if(get_post_type() == tmreviews_get_post_type()){

        if( $rating = intval( get_post_meta( $comment_id, 'review_rating', true ) ) ) {

            $rating_icons = '';
            $i = 1;
            while ($i <= $rating){
                $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                $i++;
            }
            if($rating < 5){
                $asd = 5 - $rating;
                $k = 1;
                while ($k <= $asd){
                    $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                    $k++;
                }
            }
            $commentrating = '<div class="fl-rate-icons">' . $rating_icons . '</div>';
            return $commentrating;
        } else {
            return '';
        }
    }
}


function isnaider_reorder_comment_fields( $fields ){
    $new_fields = array(); // сюда соберем поля в новом порядке
    if(array_key_exists('rating', $fields)){
        $order = array('rating', 'author','email','comment');
    }else{
        $order = array( 'author','email','comment');
    }
    foreach( $order as $key ){
        $new_fields[ $key ] = $fields[ $key ];
        unset( $fields[ $key ] );
    }
    if( $fields )
        foreach( $fields as $key => $val )
            $new_fields[ $key ] = $val;

    return $new_fields;
}




//Comment Meta Title
function render_comments_meta_fields(){
    if(get_post_type() == tmreviews_get_post_type()){
        echo '<div class="author_comment_title">';
        echo '<input id="fl-title" name="tmreviews_review_title" type="text" class="fl-title" placeholder="' . __("Title", "tm-reviews") . '">';
        echo '</div>';
    }
}
add_action('comment_form_after_fields', 'render_comments_meta_fields');
add_action('comment_form_logged_in_after', 'render_comments_meta_fields');

// Инициализация плагина
$tmreviews_plugin = new TMReviews_Plugin();

if(get_option('tmreviews_proscons_enable', true) == 'enable'){
    add_action('comment_form_after_fields', array($tmreviews_plugin, 'render_comments_meta_pros_cons_fields'));
    add_action('comment_form_logged_in_after', array($tmreviews_plugin, 'render_comments_meta_pros_cons_fields'));
}


/**
 * Сохраняем метаданные комментария
 *
 * @param int $comment_id ID комментария
 */
function save_comment_meta_data($comment_id) {
    // Сохраняем заголовок
    if (!empty($_POST['tmreviews_review_title'])) {
        add_comment_meta($comment_id, 'tmreviews_review_title', sanitize_text_field($_POST['tmreviews_review_title']));
    }

    // Сохраняем pros
    if (!empty($_POST['tmreviews_review_pros'])) {
        $pros = array_map('sanitize_text_field', array_filter($_POST['tmreviews_review_pros']));
        if (!empty($pros)) {
            add_comment_meta($comment_id, 'tmreviews_review_pros', $pros);
        }
    }

    // Сохраняем cons
    if (!empty($_POST['tmreviews_review_cons'])) {
        $cons = array_map('sanitize_text_field', array_filter($_POST['tmreviews_review_cons']));
        if (!empty($cons)) {
            add_comment_meta($comment_id, 'tmreviews_review_cons', $cons);
        }
    }
}
add_action('comment_post', 'save_comment_meta_data');







function tmreviews_gmap_print(){
    wp_print_scripts( 'gmap3' );

}


function get_rating(){
    $comment_args = array('status' => 'approve', 'post_id' => get_the_ID());
    $comments = get_comments($comment_args);
    foreach ($comments as $comment) {
        echo get_comment_meta($comment->comment_ID, 'rating', true).'<br>';
    }
}



if (!function_exists('tmreviews_get_mod')) {
    function tmreviews_get_mod($name = null, $use_acf = null, $postId = null, $acf_name = null)
    {
        $value = null;


        // try get value from meta box
        if ($use_acf) {
            $value = tmreviews_get_metabox($acf_name ? $acf_name : $name, $postId);
        }


        $value = apply_filters('gazek_filter_get_theme_mod', $value, $name);
        return $value;
    }
}
// get metabox
if (!function_exists( 'tmreviews_get_metabox' )):
    function tmreviews_get_metabox($name = null, $postId = null)
    {
        $value = null;

        // try get value from meta box
        if (function_exists('get_field')) {
            if ($postId == null) {
                $postId = get_the_ID();
            }
            $value = get_field($name, $postId);
        }

        return $value;
    }
endif;










function tmreviews_template_add_redirect() {

    $add_place_option = get_option('tmreviews_add_place_page_id', true);
    $user_reviews_option = get_option('tmreviews_user_reviews_page_id', true);

    $page_id = get_the_ID();

    if ($page_id == intval($add_place_option)) {
        $template = TMREVIEWS_THEME_HELPER_PLUGIN_PATH . '/templates/template-add-place.php';
        if ( '' != $template ) {
            return $template ;
        }
    } elseif ($page_id == intval($user_reviews_option)) {
        $template = TMREVIEWS_THEME_HELPER_PLUGIN_PATH . 'templates/template-user-reviews.php';
        if ( '' != $template ) {
            return $template ;
        }
    } else {
        $template = get_template_directory().'/page.php';
        return $template ;
    }
}
add_action( 'page_template', 'tmreviews_template_add_redirect' );



//Post Type Templates
function tmreview_load_places_templates_single( $template ) {
    global $post;
    if ( tmreviews_get_post_type() === $post->post_type && locate_template( array( '/templates/archive-places.php' ) ) !== $template ) {

        return plugin_dir_path( __FILE__ ) . '/templates/single-places.php';
    }
    return $template;
}

function tmreview_get_custom_post_type_template( $archive_template ) {
    global $post;
    if ( is_post_type_archive ( tmreviews_get_post_type() ) ) {
        $archive_template = dirname( __FILE__ ) . '/templates/archive-places.php';
    }
    return $archive_template;
}
add_filter( 'archive_template', 'tmreview_get_custom_post_type_template' ) ;
add_filter( 'single_template', 'tmreview_load_places_templates_single' );


function tmreview_call_taxonomy_template_from_directory( $category_template ) {
    if ( is_tax ( tmreviews_get_post_type() . '-category' )) {
        $category_template = dirname( __FILE__ ) . '/templates/category-places.php';
    }
    return $category_template;
}
add_filter('taxonomy_template', 'tmreview_call_taxonomy_template_from_directory');


function tmreviews_search_template($template){
    global $wp_query;
    if (!$wp_query->is_search)
        return $template;

        return dirname( __FILE__ ) . '/templates/places-search.php';
}
if(isset($_GET["post_type"]) && $_GET["post_type"] == tmreviews_get_post_type()){
    add_filter('template_include','tmreviews_search_template');
}

function custom_search_where($where){
    global $wpdb;
    if (is_search() && get_search_query())
        $where .= "OR ((t.name LIKE '%".get_search_query()."%' OR t.slug LIKE '%".get_search_query()."%') AND {$wpdb->posts}.post_status = 'publish')";
    return $where;
}

function custom_search_join($join){
    global $wpdb;
    if (is_search()&& get_search_query())
        $join .= "LEFT JOIN {$wpdb->term_relationships} tr ON {$wpdb->posts}.ID = tr.object_id INNER JOIN {$wpdb->term_taxonomy} tt ON tt.term_taxonomy_id=tr.term_taxonomy_id INNER JOIN {$wpdb->terms} t ON t.term_id = tt.term_id";
    return $join;
}

function custom_search_groupby($groupby){
    global $wpdb;

    // we need to group on post ID
    $groupby_id = "{$wpdb->posts}.ID";
    if(!is_search() || strpos($groupby, $groupby_id) !== false || !get_search_query()) return $groupby;

    // groupby was empty, use ours
    if(!strlen(trim($groupby))) return $groupby_id;

    // wasn't empty, append ours
    return $groupby.", ".$groupby_id;
}

//add_filter('posts_where','custom_search_where');
//add_filter('posts_join', 'custom_search_join');
//('posts_groupby', 'custom_search_groupby');


function tmreviews_get_breadcrumbs() {

    $text['home']           = esc_html__('Home','tm-reviews');
    $text['category']       = esc_html__('Archive','tm-reviews').' "%s"';
    $text['search']         = esc_html__('Search results:','tm-reviews').' "%s"';
    $text['tag']            = esc_html__('Tag','tm-reviews').' "%s"';
    $text['author']         = esc_html__('Author','tm-reviews').' %s';
    $text['404']            = esc_html__('Error 404','tm-reviews');
    $text['shop_page']      = esc_html__('Shop','tm-reviews');

    $show_current           = 1;
    $show_on_home           = 0;
    $show_home_link         = 1;
    $show_title             = 1;
    $delimiter              = '<span class="fl-breadcrumbs-delimiter fa fa-chevron-right"></span>';
    $before                 = '<span class="current">';
    $after                  = '</span>';

    global $post;
    $home_link    = esc_url(home_url('/'));
    $link_before  = '<span typeof="v:Breadcrumb">';
    $link_after   = '</span>';
    $link_attr    = ' rel="v:url" property="v:title"';
    $link         = $link_before . '<a' . $link_attr . ' href="%1$s">%2$s</a>' . $link_after;
    if(isset($post->post_parent)){$my_post_parent = $post->post_parent;}else{$my_post_parent=1;}
    $parent_id    = $parent_id_2 = $my_post_parent;
    $frontpage_id = get_option('page_on_front');

    if (is_home() || is_front_page()) {

        if ($show_on_home == 1) echo '<div class="breadcrumbs"><a href="' . $home_link . '">' . $text['home'] . '</a></div>';

        if(get_option( 'page_for_posts' )){
            echo '<div class="breadcrumbs"><a href="' . esc_url($home_link) . '">' . esc_attr($text['home']) . '</a>'.ihsan_wp_kses($delimiter).' '.__('Blog','tm-reviews').'</div>';
        }
    } else {

        echo '<div class="breadcrumbs">';
        if ($show_home_link == 1) {
            echo sprintf($link, $home_link, $text['home']);
            if ($frontpage_id == 0 || $parent_id != $frontpage_id) echo tmreviews_wp_kses($delimiter);
        }



        if ( taxonomy_exists(tmreviews_get_post_type() . '-category') ) {
                if(isset($_GET[tmreviews_get_post_type() . '-category']) && $_GET[tmreviews_get_post_type() . '-category'] != ''){
                    $this_cat = get_term_by('slug', $_GET[tmreviews_get_post_type() . '-category'], tmreviews_get_post_type() . '-category');;
                    if (isset($this_cat->parent) && $this_cat->parent != 0) {
                        $cats = get_category_parents($this_cat->parent, TRUE, $delimiter);
                        if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
                        $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
                        $cats = str_replace('</a>', '</a>' . $link_after, $cats);
                        if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
                        echo tmreviews_wp_kses($cats);
                        if ($show_current == 1) echo tmreviews_wp_kses($before) . sprintf($text['category'], single_cat_title('', false)) . tmreviews_wp_kses($after);
                    }
                } else {
                    if ($show_current == 1) echo tmreviews_wp_kses($before) . ucfirst(tmreviews_get_post_type()) . tmreviews_wp_kses($after);

                }

        } elseif ( is_search() ) {
            if(is_post_type_archive(tmreviews_get_post_type())){
                global $wp_query;
                echo tmreviews_wp_kses($before) . sprintf($text['search'], $wp_query->found_posts) . tmreviews_wp_kses($after);

            } else {
                echo tmreviews_wp_kses($before) . sprintf($text['search'], get_search_query()) . tmreviews_wp_kses($after);

            }
        } elseif ( is_day() ) {
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . tmreviews_wp_kses($delimiter);
            echo sprintf($link, get_month_link(get_the_time('Y'),get_the_time('m')), get_the_time('F')) . tmreviews_wp_kses($delimiter);
            echo tmreviews_wp_kses($before) . get_the_time('d') . tmreviews_wp_kses($after);

        } elseif ( is_month() ) {
            echo sprintf($link, get_year_link(get_the_time('Y')), get_the_time('Y')) . tmreviews_wp_kses($delimiter);
            echo tmreviews_wp_kses($before) . get_the_time('F') . tmreviews_wp_kses($after);

        } elseif ( is_year() ) {
            echo tmreviews_wp_kses($before) . get_the_time('Y') . tmreviews_wp_kses($after);

        } elseif ( is_single() && !is_attachment() ) {
            if ( get_post_type() != 'post' ) {

                if('topic' == get_post_type()){
                    $post_type = get_post_type_object(get_post_type());
                    $link = bbp_get_forum_permalink(bbp_get_forum_id());
                    echo '<a href="'.$link.'">'. $post_type->labels->singular_name .'</a>';
                }else{
                    $post_type = get_post_type_object(get_post_type());
                    $slug = $post_type->rewrite;
                    printf($link, $home_link . '/' . $slug['slug'] . '/', $post_type->labels->singular_name);
                }
                if ($show_current == 1) echo tmreviews_wp_kses($delimiter) . tmreviews_wp_kses($before) . get_the_title() . tmreviews_wp_kses($after);


            } else {

                $cat = get_the_category(); $cat = $cat[0];
                $cats = get_category_parents($cat, TRUE, $delimiter);
                if ($show_current == 0) $cats = preg_replace("#^(.+)$delimiter$#", "$1", $cats);
                $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
                $cats = str_replace('</a>', '</a>' . $link_after, $cats);
                if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
                echo tmreviews_wp_kses($cats);
                if ($show_current == 1) echo tmreviews_wp_kses($before) . get_the_title() . tmreviews_wp_kses($after);
            }

        } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
            $post_type = get_post_type_object(get_post_type());

            if(!empty($post_type) && $post_type != ''){
                if(is_post_type_archive(tmreviews_get_post_type())){
                    echo tmreviews_wp_kses($before) . __('All categories', 'tm-reviews') . tmreviews_wp_kses($after);
                }else{
                    echo tmreviews_wp_kses($before) . esc_attr($post_type->labels->singular_name) . tmreviews_wp_kses($after);
                }
            }

        } elseif ( is_attachment() ) {
            $parent = get_post($parent_id);
            $cat = get_the_category($parent->ID); $cat = $cat[0];
            $cats = get_category_parents($cat, TRUE, $delimiter);
            $cats = str_replace('<a', $link_before . '<a' . $link_attr, $cats);
            $cats = str_replace('</a>', '</a>' . $link_after, $cats);
            if ($show_title == 0) $cats = preg_replace('/ title="(.*?)"/', '', $cats);
            echo tmreviews_wp_kses($cats);
            printf($link, get_permalink($parent), $parent->post_title);
            if ($show_current == 1) echo tmreviews_wp_kses($delimiter) . tmreviews_wp_kses($before) . get_the_title() . tmreviews_wp_kses($after);

        } elseif ( is_page() && !$parent_id ) {
            if ($show_current == 1) echo tmreviews_wp_kses($before) . get_the_title() . tmreviews_wp_kses($after);

        } elseif ( is_page() && $parent_id ) {
            if ($parent_id != $frontpage_id) {
                $breadcrumbs = array();
                while ($parent_id) {
                    $page = get_page($parent_id);
                    if ($parent_id != $frontpage_id) {
                        $breadcrumbs[] = sprintf($link, get_permalink($page->ID), get_the_title($page->ID));
                    }
                    $parent_id = $page->post_parent;
                }
                $breadcrumbs = array_reverse($breadcrumbs);
                for ($i = 0; $i < count($breadcrumbs); $i++) {
                    echo tmreviews_wp_kses($breadcrumbs[$i]);
                    if ($i != count($breadcrumbs)-1) echo tmreviews_wp_kses($delimiter);
                }
            }
            if ($show_current == 1) {
                if ($show_home_link == 1 || ($parent_id_2 != 0 && $parent_id_2 != $frontpage_id)) echo tmreviews_wp_kses($delimiter);
                echo tmreviews_wp_kses($before) . get_the_title() . tmreviews_wp_kses($after);
            }

        } elseif ( is_tag() ) {
            echo tmreviews_wp_kses($before) . sprintf($text['tag'], single_tag_title('', false)) . tmreviews_wp_kses($after);

        } elseif ( is_author() ) {

            global $author;
            $userdata = get_userdata($author);
            echo tmreviews_wp_kses($before) . sprintf($text['author'], $userdata->display_name) . tmreviews_wp_kses($after);

        } elseif ( is_404() ) {
            echo tmreviews_wp_kses($before) . esc_attr($text['404']) . tmreviews_wp_kses($after);

        }



        if ( get_query_var('paged') ) {
            if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ' (';
            echo '<span class="fl-breadcrumbs-delimiter fa fa-chevron-right"></span><span>' . __('Page','tm-reviews') . ' ' . get_query_var('paged') . '</span>';
            if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() ) echo ')';

        }



        echo '</div>';

    }
}

function tmreviews_recursive_sanitize_text_field($array) {
    foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = recursive_sanitize_text_field($value);
        }
        else {
            $value = sanitize_text_field( $value );
        }
    }

    return $array;
}

function tmreview_make_upload_and_get_attached_id($filefield,$allowed_ext,$require_imagesize=array(),$parent_post_id=0)
{
    $allowfieldstypes = strtolower(trim($allowed_ext));
    $attach_id = '';
    if(is_array($filefield) && !empty($filefield))
    {
        $file = array('name' => $filefield['name'],
            'type' => $filefield['type'],
            'tmp_name' => $filefield['tmp_name'],
            'error' => $filefield['error'],
            'size' => $filefield['size']);

        if(!empty($require_imagesize) && !empty($file['tmp_name']))
        {
            $imagesize = getimagesize($file['tmp_name']);
            $image_width = $imagesize[0];
            $image_height = $imagesize[1];

            if(isset($require_imagesize[2]) && $file['size'] > $require_imagesize[2])
            {
                $too_small = sprintf( __( 'Image size exceeds the maximum limit. Maximum allowed image size is %d byte.','tm-reviews' ), $require_imagesize['2'] );
            }
            elseif ( $image_width < $require_imagesize['0'] || $image_height < $require_imagesize['1'] )
            {
                $too_small = sprintf( __( 'Image dimensions are too small. Minimum size is %d by %d pixels.','tm-reviews' ), $require_imagesize['0'],$require_imagesize['1'] );
            }
            else
            {
                $too_small = false;
            }
        }
        else
        {
            $too_small = false;
        }

        if ($filefield['error']=== 0)
        {
            if ( ! function_exists( 'wp_handle_upload' ) )
            {
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
            }
            $upload_overrides = array( 'test_form' => false );
            $movefile = wp_handle_upload( $file, $upload_overrides );
            if ( $movefile )
            {
                // $filename should be the path to a file in the upload directory.
                $filename = $movefile['file'];
                // The ID of the post this attachment is for.

                // Check the type of tile. We'll use this as the 'post_mime_type'.
                $filetype = wp_check_filetype( basename( $filename ), null );
                $current_file_type = strtolower($filetype['ext']);
                if(strpos($allowfieldstypes,$current_file_type)!==false && $too_small==false)
                {

                    // Get the path to the upload directory.
                    $wp_upload_dir = wp_upload_dir();
                    // Prepare an array of post data for the attachment.
                    $attachment = array(
                        'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ),
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
                    // Insert the attachment.
                    include_once( ABSPATH . 'wp-admin/includes/image.php' );
                    $attach_id = wp_insert_attachment( $attachment, $filename, $parent_post_id );
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                    wp_update_attachment_metadata( $attach_id, $attach_data );

                }
                else
                {
                    if(strpos($allowfieldstypes,$current_file_type)===false)
                    {
                        return __("This file type is not allowed.",'tm-reviews');
                    }
                    else
                    {
                        return $too_small;
                    }
                }
            }
        }
    }
    return $attach_id;

}

function wpse_plugin_comment_template( $comment_template ) {
    global $post;
    if ( !( is_singular() && ( have_comments() || 'open' == $post->comment_status ) ) ) {
        // leave the standard comments template for standard post types
        return;
    }
    if($post->post_type == tmreviews_get_post_type()){ // assuming there is a post type called business
        // This is where you would use your commentsnew.php, or review.php
        return dirname( __FILE__ ) . '/templates/places-comments.php';

    }
}
// throw this into your plugin or your functions.php file to define the custom comments template.
add_filter( "comments_template", "wpse_plugin_comment_template" );


add_action( 'admin_init', 'wpse_74018_enable_draft_comments' );


function wpse_74018_enable_draft_comments()
{
    if( isset( $_GET['post'] ) )
    {
        $post_id = absint( $_GET['post'] );
        $post = get_post( $post_id );
        if ( 'pending' == $post->post_status || 'pending' == $post->post_status )
            add_meta_box(
                'commentsdiv',
                __('Comments', 'tm-reviews'),
                'post_comment_meta_box',
                tmreviews_get_post_type(), // CHANGE FOR YOUR CPT
                'normal',
                'core'
            );
    }
}
add_action( 'admin_init', 'wpse_74018_enable_custom_ajax_comments' );
function wpse_74018_enable_custom_ajax_comments(){
    add_action( 'wp_ajax_replyto-comment', 'wpse_74018_custom_callback', 0 );
}
function wpse_74018_custom_callback( $action ) {
    global $wp_list_table, $wpdb;
    if ( empty( $action ) )
        $action = 'replyto-comment';

    check_ajax_referer( $action, '_ajax_nonce-replyto-comment' );

    set_current_screen( 'edit-comments' );

    $comment_post_ID = (int) $_POST['comment_post_ID'];
    if ( !current_user_can( 'edit_post', $comment_post_ID ) )
        wp_die( -1 );

    $status = $wpdb->get_var( $wpdb->prepare("SELECT post_status FROM $wpdb->posts WHERE ID = %d", $comment_post_ID) );

    if( tmreviews_get_post_type() == get_post_type( $comment_post_ID ) )
        $diff_status = array('trash');
    else
        $diff_status = array('draft','pending','trash');

    if ( empty($status) )
        wp_die( 1 );
    elseif ( in_array($status, $diff_status ) )
        wp_die( __('ERROR: you are replying to a comment on a draft post.', 'tm-reviews') );

    $user = wp_get_current_user();
    if ( $user->exists() ) {
        $user_ID = $user->ID;
        $comment_author       = $wpdb->escape($user->display_name);
        $comment_author_email = $wpdb->escape($user->user_email);
        $comment_author_url   = $wpdb->escape($user->user_url);
        $comment_content      = trim($_POST['content']);
        if ( current_user_can( 'unfiltered_html' ) ) {
            if ( wp_create_nonce( 'unfiltered-html-comment' ) != $_POST['_wp_unfiltered_html_comment'] ) {
                kses_remove_filters(); // start with a clean slate
                kses_init_filters(); // set up the filters
            }
        }
    } else {
        wp_die( __( 'Sorry, you must be logged in to reply to a comment.', 'tm-reviews' ) );
    }

    if ( '' == $comment_content )
        wp_die( __( 'ERROR: please type a comment.', 'tm-reviews' ) );

    $comment_parent = absint($_POST['comment_ID']);
    $comment_auto_approved = false;
    $commentdata = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_content', 'comment_type', 'comment_parent', 'user_ID');

    $comment_id = wp_new_comment( $commentdata );
    $comment = get_comment($comment_id);
    if ( ! $comment ) wp_die( 1 );

    $position = ( isset($_POST['position']) && (int) $_POST['position'] ) ? (int) $_POST['position'] : '-1';

    // automatically approve parent comment
    if ( !empty($_POST['approve_parent']) ) {
        $parent = get_comment( $comment_parent );

        if ( $parent && $parent->comment_approved === '0' && $parent->comment_post_ID == $comment_post_ID ) {
            if ( wp_set_comment_status( $parent->comment_ID, 'approve' ) )
                $comment_auto_approved = true;
        }
    }

    ob_start();
    if ( 'dashboard' == $_REQUEST['mode'] ) {
        require_once( ABSPATH . 'wp-admin/includes/dashboard.php' );
        _wp_dashboard_recent_comments_row( $comment );
    } else {
        if ( 'single' == $_REQUEST['mode'] ) {
            $wp_list_table = _get_list_table('WP_Post_Comments_List_Table');
        } else {
            $wp_list_table = _get_list_table('WP_Comments_List_Table');
        }
        $wp_list_table->single_row( $comment );
    }
    $comment_list_item = ob_get_contents();
    ob_end_clean();

    $response =  array(
        'what' => 'comment',
        'id' => $comment->comment_ID,
        'data' => $comment_list_item,
        'position' => $position
    );

    if ( $comment_auto_approved )
        $response['supplemental'] = array( 'parent_approved' => $parent->comment_ID );

    $x = new WP_Ajax_Response();
    $x->add( $response );
    $x->send();
}




function is_a_preview_commenter() {
    // change this roles according to your needs
    $roles = array('administrator', 'editor', 'author', 'contributor');
    $allowed_roles = apply_filters('preview_comment_allowed_roles', $roles);
    $user = wp_get_current_user();
    $inrole = array_intersect($user->roles, $allowed_roles);
    return ! empty( $inrole );
}


function additional_comment_fields() {
    if ( is_preview() && is_a_preview_commenter() ) {
        $nonce = wp_create_nonce('comment_preview');
        echo '<input type="hidden" name="check_the_preview" value="' . $nonce . '" />';
    }
}
add_action( 'comment_form_logged_in_after', 'additional_comment_fields');



function fake_public_pending() {
    global $pagenow;
    if ( $pagenow === 'wp-comments-post.php' && is_a_preview_commenter() ) {
        $nonce = filter_input(INPUT_POST, 'check_the_preview', FILTER_SANITIZE_STRING);
        if ( empty($nonce) || ! wp_verify_nonce($nonce, 'comment_preview') ) return;
        global $wp_post_statuses;
        // let WordPress believe all pending posts are published post
        $wp_post_statuses['pending'] = $wp_post_statuses['publish'];
    }
}
add_action('wp_loaded', 'fake_public_pending');



function redirect_to_preview( $comment, $user ) {
    if ( ! is_a_preview_commenter() ) return;
    $nonce = filter_input(INPUT_POST, 'check_the_preview', FILTER_SANITIZE_STRING);
    if ( empty($nonce) || ! wp_verify_nonce($nonce, 'comment_preview') ) return;
    $link = get_permalink($comment->comment_post_ID);
    $url = add_query_arg( array('preview' => 'true'), $link );
    wp_safe_redirect("{$url}#comment-{$comment->comment_ID}", 302);
    exit();
}
add_action('set_comment_cookies', 'redirect_to_preview', 9999, 2 );



function tmreview_limit_excerpt($limit) {
    $excerpt = explode(' ', get_the_excerpt(), $limit);
    if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt).' ...';
    } else {
        $excerpt = implode(" ",$excerpt);
    }
    $patterns = "/\[[\/]?vc_[^\]]*\]/";
    $replacements = "";

    $excerpt = preg_replace( $patterns, $replacements, $excerpt);
    return $excerpt;
}


//Add place shortcode
function tmreviews_add_places_function(){
    if(is_user_logged_in()){
        add_action( 'wp_enqueue_scripts', 'autozone_enqueue_media' );
        $settings = array('wpautop' => false,'media_buttons' => false,'textarea_name' => 'blog_description',
            'textarea_rows' => 10,'tabindex' => '','tabfocus_elements' => ':prev,:next','editor_css' => '',
            'editor_class' => '','teeny' => false,'dfw' => false,'tinymce' => false,'quicktags' => false
        );
        if(isset($_POST['place_title'])) {
            $retrieved_nonce = filter_input(INPUT_POST,'_wpnonce');
            if (!wp_verify_nonce($retrieved_nonce, 'tmrv_blog_post' ) ) die( __('Failed security check','tmreviews') );
            $exclude = array("_wpnonce","_wp_http_referer","pg_blog_submit");
            $post = $_POST;
            if(!isset($post['blog_tags']))$post['blog_tags']='';
            $allowed_ext = 'jpg|jpeg|png|gif';

            $arg = array(
                'post_title' =>$post['place_title'],
                'post_status' => 'pending',
                'post_type'  => tmreviews_get_post_type(),
                'post_content' => wp_rel_nofollow($post['blog_description']),
            );
            $postid = wp_insert_post($arg);

            $tax_array = array();
            if(isset($post['tax']) && !empty($post['tax'])){
                foreach ($post['tax'] as $p){
                    $tax_array[] = intval($p);
                }

                wp_set_object_terms($postid, $tax_array, tmreviews_get_post_type() . '-category');

            }

            update_post_meta($postid, 'place_bg_cl', '#32297b');
            update_post_meta($postid, '_place_bg_cl', 'field_asd3adsdw842b');

            //Gallery
            if(isset($post['place_gallery_ids']) && $post['place_gallery_ids'] != ''){

                $encode_gallery = explode(',', $post['place_gallery_ids']);

                update_post_meta($postid, 'place_gallery', $encode_gallery);
                update_post_meta($postid, '_place_gallery', 'field_5f03547a50164');

            }

            //Images
            if(isset($_FILES['thumbnail_image']))
            {
                $attchment_th_id = tmreview_make_upload_and_get_attached_id($_FILES['thumbnail_image'],$allowed_ext,array(),$postid);
                set_post_thumbnail($postid, $attchment_th_id );
            }

            if(isset($_FILES['logo_image']))
            {
                $attchment_lg_id = tmreview_make_upload_and_get_attached_id($_FILES['logo_image'],$allowed_ext,array(),$postid);
                update_post_meta($postid, 'place_logo', $attchment_lg_id);
                update_post_meta($postid, '_place_logo', 'field_asd345r4842b');
            }

            if(isset($_FILES['bg_image']))
            {
                $attchment_bg_id = tmreview_make_upload_and_get_attached_id($_FILES['bg_image'],$allowed_ext,array(),$postid);
                update_post_meta($postid, 'place_bg', $attchment_bg_id);
                update_post_meta($postid, '_place_bg', 'field_asd34asdw842b');
            }

            //Text fields
            if(isset($post['place_sub_title']))
            {
                $place_sub_title = $post['place_sub_title'];
                update_post_meta($postid, 'place_subtitle', $place_sub_title);
                update_post_meta($postid, '_place_subtitle', 'field_asd356f24842b');
            }

            if(isset($post['place_phone']))
            {
                $place_phone = $post['place_phone'];
                update_post_meta($postid, 'place_phone', $place_phone);
                update_post_meta($postid, '_place_phone', 'field_5ed556f24asdwd');
            }

            if(isset($post['place_email']))
            {
                $place_email = $post['place_email'];
                update_post_meta($postid, 'place_email', $place_email);
                update_post_meta($postid, '_place_email', 'field_5edascaf24aacwd');
            }

            if(isset($post['place_website']))
            {
                $place_website = $post['place_website'];
                update_post_meta($postid, 'place_website', $place_website);
                update_post_meta($postid, '_place_website', 'field_5edascaf24asdwd');
            }

            //Socials
            if(isset($post['place_facebook']))
            {
                $place_sub_title = $post['place_facebook'];
                update_post_meta($postid, 'socials_facebook', $place_sub_title);
                update_post_meta($postid, '_socials_facebook', 'field_5ed7d1f7f9966');
                update_post_meta($postid, '_socials', 'field_5ed7d1edf9965');
            }

            if(isset($post['place_twitter']))
            {
                $place_sub_title = $post['place_twitter'];
                update_post_meta($postid, 'socials_twitter', $place_sub_title);
                update_post_meta($postid, '_socials_twitter', 'field_5ed7d209f9967');
                update_post_meta($postid, '_socials', 'field_5ed7d1edf9965');
            }


            if(isset($post['place_dribble']))
            {
                $place_sub_title = $post['place_dribble'];
                update_post_meta($postid, 'socials_dribble', $place_sub_title);
                update_post_meta($postid, '_socials_dribble', 'field_5ed7d220f9968');
                update_post_meta($postid, '_socials', 'field_5ed7d1edf9965');
            }

            if(isset($post['place_linkedin']))
            {
                $place_sub_title = $post['place_linkedin'];
                update_post_meta($postid, 'socials_linkedin', $place_sub_title);
                update_post_meta($postid, '_socials_linkedin', 'field_5ed7d22ef9969');
                update_post_meta($postid, '_socials', 'field_5ed7d1edf9965');
            }

            if(isset($post['place_behance']))
            {
                $place_sub_title = $post['place_behance'];
                update_post_meta($postid, 'socials_behance', $place_sub_title);
                update_post_meta($postid, '_socials_behance', 'field_5ed7d23cf996a');
                update_post_meta($postid, '_socials', 'field_5ed7d1edf9965');
            }

            if(isset($post['place_instagram']))
            {
                $place_sub_title = $post['place_instagram'];
                update_post_meta($postid, 'socials_instagram', $place_sub_title);
                update_post_meta($postid, '_socials_instagram', 'field_5ed7d245f996b');
                update_post_meta($postid, '_socials', 'field_5ed7d1edf9965');
            }

            $added_notice = '<span class="tmreviews_added_notice tmreviews_added_notice_visible">' . esc_attr('Submitted for moderation') . '</span>';

            //$redirect_url = get_the_permalink();
            //echo ("<script>location.href = '".$redirect_url."'</script>");

        }
        $tmreview_add_result = '';
        $redirect_url = get_the_permalink(get_the_ID());
        if(isset($added_notice) && $added_notice != ''){
            $tmreview_add_result .= $added_notice;
        }
        if ( has_nav_menu( 'account-menu' ) ) {
            wp_nav_menu(array(
                'theme_location'    => 'account-menu',
                'class'             => 'account-menu account-menu',
                'container'         => false,
                'id'                => 'account-menu',
                'depth'             => 8,
                'fallback_cb'       => 'gazek_menu_fallback'
            ));
        }

        if(class_exists('MemberOrder')) {
            if(isset($_GET['id']) && $_GET['id'] != '') {
                if(is_string( get_post_status( $_GET['id'] ) )){
                    acf_form(array(
                        'post_id' => $_GET['id'],
                        'post_title' => true,
                        'post_content' => true,

                        'fields' => array(
                            'field_asd345r4842b',
                            'field_asd34asdw842b',
                            'field_asd356f24842b',
                            'field_5f03547a50164',
                            'field_5ed556f24842b',
                            'field_5ed556f24asdwd',
                            'field_5edascaf24aacwd',
                            'field_5edascaf24asdwd',
                            'field_5ed7d1edf9965',

                            'field_5ed7ffedfsw99asf5',
                            'field_5edadf24asdwa23fassfs',
                            'field2_5dds2343423asdh232nusadus232n65'
                        ),
                        'submit_value' => __('Update')
                    ));
                } else {
                    echo tmreviews_user_can_add_return_text(get_current_user_ID());
                    if (tmreviews_user_can_add(get_current_user_ID())){
                        $tmreview_add_result .= '<div class="tmreviews-add-place container">';
                        $tmreview_add_result .= '<form class="tmreviewsagic-form tmreviews-dbfl" method="post" action="'.$redirect_url.'" id="tmreviews_add_blog_post" name="tmreviews_add_blog_post" enctype="multipart/form-data">';
                        $tmreview_add_result .= '<div class="tmreviewsrow">';
                        $tmreview_add_result .= '<span class="fl-add-place-row-title">' . __('General','tm-reviews') . '</span>';
                        $tmreview_add_result .= '<div class="tmreviews-col">';
                        $tmreview_add_result .= '<div class="tmreviews-form-field-icon"></div>';
                        $tmreview_add_result .= '<div class="tmreviews-field-lable">';
                        $tmreview_add_result .= '<label>' . __('Title','tm-reviews') . '<sup class="tmreviews_estric">*</sup></label>';
                        $tmreview_add_result .= '</div>';
                        $tmreview_add_result .= '<div class="tmreviews-field-input tmreviews_required">';
                        $tmreview_add_result .= '<input title="Enter your title" type="text" class="" value="" id="place_title" name="place_title" placeholder="">';
                        $tmreview_add_result .= '<div class="errortext" style="display:none;"></div>';
                        $tmreview_add_result .= '</div>';
                        $tmreview_add_result .= '</div>';
                        $tmreview_add_result .= '<div class="tmreviews-col">';
                        $tmreview_add_result .= '<div class="tmreviews-form-field-icon"></div>';
                        $tmreview_add_result .= '<div class="tmreviews-field-lable">';
                        $tmreview_add_result .= '<label>' . __('Sub Title','tm-reviews') . '<sup class="tmreviews_estric">*</sup></label>';
                        $tmreview_add_result .= '</div>';
                        $tmreview_add_result .= '<div class="tmreviews-field-input tmreviews_required">';
                        $tmreview_add_result .= '<input title="Enter your title" type="text" class="" value="" id="place_sub_title" name="place_sub_title" placeholder="">';
                        $tmreview_add_result .= '<div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">';
                        $tmreview_add_result .= '<label>' . __('Description','tm-reviews') . '</label>';

                        ob_start();

                        wp_editor('', 'blog_description', $settings);
                        $editor_contents = ob_get_clean();
                        $tmreview_add_result .= '</div>
                            <div class="tmreviews-field-input">';
                        $tmreview_add_result .= __($editor_contents);
                        $tmreview_add_result .= '<div class="errortext" style="display:none;"></div>';
                        $tmreview_add_result .= '</div>';
                        $tmreview_add_result .= '</div>';
                        $tmreview_add_result .= '</div>';
                        $tmreview_add_result .= '<div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Contacts','tmreviews') . '</span>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Phone','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_phone"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Email','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_email"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Website','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_website"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Socials','tm-reviews') . '</span>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Facebook','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_facebook"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Twitter','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_twitter"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Dribble','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_dribble"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('LinkedIn','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_linkedin"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Behance','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_behance"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Instagram','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_instagram"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Images','tm-reviews') . '</span>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Thumbnail Image','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input tmreviews_fileinput">
                            <div class="tmreviews_repeat">
                                <input title="" type="file" class="tmreviews_file" name="thumbnail_image" data-filter-placeholder=""/>
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Logo Image','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input tmreviews_fileinput">
                            <div class="tmreviews_repeat">
                                <input title="" type="file" class="tmreviews_file" name="logo_image" data-filter-placeholder=""/>
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Background Image','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input tmreviews_fileinput">
                            <div class="tmreviews_repeat">
                                <input title="" type="file" class="tmreviews_file" name="bg_image" data-filter-placeholder="" />
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>';

                        $manage_gallery = __('Manage gallery', 'tm-reviews');
                        $clear_gallery  = __('Clear gallery', 'tm-reviews');
                        if(is_user_logged_in()){
                            if(isset($values['pixad_auto_gallery'])) {
                                $ids = json_decode($values['pixad_auto_gallery'][0]);
                            }
                            else {
                                $ids = array();
                            }
                            $cs_ids = is_array($ids) ? implode(",", $ids) : '';
                            $html  = do_shortcode('[gallery ids="'.$cs_ids.'"]');
                            $html .= '<input id="pixad_auto_gallery_ids" type="hidden" name="place_gallery_ids" value="-1" />';
                            $html .= '<input id="manage_gallery" title="'.esc_html($manage_gallery).'" type="button" value="'.esc_html($manage_gallery).'" />';
                            $html .= '<input id="clear_gallery" title="'.esc_html($clear_gallery).'" type="button" value="'.esc_html($clear_gallery).'" />';
                            $tmreview_add_result .= wp_specialchars_decode($html);
                        }

                        $tmreview_add_result .= '</div>';
                        $tmreview_add_result .= '<div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Category','tm-reviews') . '</span>
                    <div class="tmreviews-col">';
                        $taxomony = get_terms(tmreviews_get_post_type() . '-category');
                        $taxonomy_html = '';
                        if(isset($taxomony) && !empty($taxomony)){
                            $taxonomy_html .= '<select multiple name="tax[]">';
                            foreach ($taxomony as $t){
                                $taxonomy_html .= '<option value = "'.$t->term_id.'">'.$t->name.'</option>';
                            }
                            $taxonomy_html .= '</select>';
                        }
                        $tmreview_add_result .= $taxonomy_html;
                        $tmreview_add_result .= '</div>
                    <div class="buttonarea tmreviews-full-width-container">
                        <button type="submit" class="fl-custom-btn fl-font-style-bolt-two primary-style"><span>' . __('Submit','tm-reviews') . '</span></button>';
                        $tmreview_add_result .= wp_nonce_field( 'tmrv_blog_post' );
                        $tmreview_add_result .= '</div>
                </div>
                <div class="all_errors" style="display:none;"></div>
            </form>
        </div>';


                        return $tmreview_add_result;
                    }
                }
            } else {
                echo tmreviews_user_can_add_return_text(get_current_user_ID());
                if (tmreviews_user_can_add(get_current_user_ID())){
                    $tmreview_add_result .= '<div class="tmreviews-add-place container">';
                    $tmreview_add_result .= '<form class="tmreviewsagic-form tmreviews-dbfl" method="post" action="'.$redirect_url.'" id="tmreviews_add_blog_post" name="tmreviews_add_blog_post" enctype="multipart/form-data">';
                    $tmreview_add_result .= '<div class="tmreviewsrow">';
                    $tmreview_add_result .= '<span class="fl-add-place-row-title">' . __('General','tm-reviews') . '</span>';
                    $tmreview_add_result .= '<div class="tmreviews-col">';
                    $tmreview_add_result .= '<div class="tmreviews-form-field-icon"></div>';
                    $tmreview_add_result .= '<div class="tmreviews-field-lable">';
                    $tmreview_add_result .= '<label>' . __('Title','tm-reviews') . '<sup class="tmreviews_estric">*</sup></label>';
                    $tmreview_add_result .= '</div>';
                    $tmreview_add_result .= '<div class="tmreviews-field-input tmreviews_required">';
                    $tmreview_add_result .= '<input title="Enter your title" type="text" class="" value="" id="place_title" name="place_title" placeholder="">';
                    $tmreview_add_result .= '<div class="errortext" style="display:none;"></div>';
                    $tmreview_add_result .= '</div>';
                    $tmreview_add_result .= '</div>';
                    $tmreview_add_result .= '<div class="tmreviews-col">';
                    $tmreview_add_result .= '<div class="tmreviews-form-field-icon"></div>';
                    $tmreview_add_result .= '<div class="tmreviews-field-lable">';
                    $tmreview_add_result .= '<label>' . __('Sub Title','tm-reviews') . '<sup class="tmreviews_estric">*</sup></label>';
                    $tmreview_add_result .= '</div>';
                    $tmreview_add_result .= '<div class="tmreviews-field-input tmreviews_required">';
                    $tmreview_add_result .= '<input title="Enter your title" type="text" class="" value="" id="place_sub_title" name="place_sub_title" placeholder="">';
                    $tmreview_add_result .= '<div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">';
                    $tmreview_add_result .= '<label>' . __('Description','tm-reviews') . '</label>';

                    ob_start();

                    wp_editor('', 'blog_description', $settings);
                    $editor_contents = ob_get_clean();
                    $tmreview_add_result .= '</div>
                            <div class="tmreviews-field-input">';
                    $tmreview_add_result .= __($editor_contents);
                    $tmreview_add_result .= '<div class="errortext" style="display:none;"></div>';
                    $tmreview_add_result .= '</div>';
                    $tmreview_add_result .= '</div>';
                    $tmreview_add_result .= '</div>';
                    $tmreview_add_result .= '<div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Contacts','tmreviews') . '</span>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Phone','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_phone"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Email','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_email"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Website','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_website"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Socials','tm-reviews') . '</span>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Facebook','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_facebook"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Twitter','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_twitter"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Dribble','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_dribble"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('LinkedIn','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_linkedin"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Behance','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_behance"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Instagram','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_instagram"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Images','tm-reviews') . '</span>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Thumbnail Image','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input tmreviews_fileinput">
                            <div class="tmreviews_repeat">
                                <input title="" type="file" class="tmreviews_file" name="thumbnail_image" data-filter-placeholder=""/>
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Logo Image','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input tmreviews_fileinput">
                            <div class="tmreviews_repeat">
                                <input title="" type="file" class="tmreviews_file" name="logo_image" data-filter-placeholder=""/>
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Background Image','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input tmreviews_fileinput">
                            <div class="tmreviews_repeat">
                                <input title="" type="file" class="tmreviews_file" name="bg_image" data-filter-placeholder="" />
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>';

                    $manage_gallery = __('Manage gallery', 'tm-reviews');
                    $clear_gallery  = __('Clear gallery', 'tm-reviews');
                    if(is_user_logged_in()){
                        if(isset($values['pixad_auto_gallery'])) {
                            $ids = json_decode($values['pixad_auto_gallery'][0]);
                        }
                        else {
                            $ids = array();
                        }
                        $cs_ids = is_array($ids) ? implode(",", $ids) : '';
                        $html  = do_shortcode('[gallery ids="'.$cs_ids.'"]');
                        $html .= '<input id="pixad_auto_gallery_ids" type="hidden" name="place_gallery_ids" value="-1" />';
                        $html .= '<input id="manage_gallery" title="'.esc_html($manage_gallery).'" type="button" value="'.esc_html($manage_gallery).'" />';
                        $html .= '<input id="clear_gallery" title="'.esc_html($clear_gallery).'" type="button" value="'.esc_html($clear_gallery).'" />';
                        $tmreview_add_result .= wp_specialchars_decode($html);
                    }

                    $tmreview_add_result .= '</div>';
                    $tmreview_add_result .= '<div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Category','tm-reviews') . '</span>
                    <div class="tmreviews-col">';
                    $taxomony = get_terms(tmreviews_get_post_type() . '-category');
                    $taxonomy_html = '';
                    if(isset($taxomony) && !empty($taxomony)){
                        $taxonomy_html .= '<select multiple name="tax[]">';
                        foreach ($taxomony as $t){
                            $taxonomy_html .= '<option value = "'.$t->term_id.'">'.$t->name.'</option>';
                        }
                        $taxonomy_html .= '</select>';
                    }
                    $tmreview_add_result .= $taxonomy_html;
                    $tmreview_add_result .= '</div>
                    <div class="buttonarea tmreviews-full-width-container">
                        <button type="submit" class="fl-custom-btn fl-font-style-bolt-two primary-style"><span>' . __('Submit','tm-reviews') . '</span></button>';
                    $tmreview_add_result .= wp_nonce_field( 'tmrv_blog_post' );
                    $tmreview_add_result .= '</div>
                </div>
                <div class="all_errors" style="display:none;"></div>
            </form>
        </div>';


                    return $tmreview_add_result;
                }
            }
        } else {
            $tmreview_add_result .= '<div class="tmreviews-add-place container">';
            $tmreview_add_result .= '<form class="tmreviewsagic-form tmreviews-dbfl" method="post" action="'.$redirect_url.'" id="tmreviews_add_blog_post" name="tmreviews_add_blog_post" enctype="multipart/form-data">';
            $tmreview_add_result .= '<div class="tmreviewsrow">';
            $tmreview_add_result .= '<span class="fl-add-place-row-title">' . __('General','tm-reviews') . '</span>';
            $tmreview_add_result .= '<div class="tmreviews-col">';
            $tmreview_add_result .= '<div class="tmreviews-form-field-icon"></div>';
            $tmreview_add_result .= '<div class="tmreviews-field-lable">';
            $tmreview_add_result .= '<label>' . __('Title','tm-reviews') . '<sup class="tmreviews_estric">*</sup></label>';
            $tmreview_add_result .= '</div>';
            $tmreview_add_result .= '<div class="tmreviews-field-input tmreviews_required">';
            $tmreview_add_result .= '<input title="Enter your title" type="text" class="" value="" id="place_title" name="place_title" placeholder="">';
            $tmreview_add_result .= '<div class="errortext" style="display:none;"></div>';
            $tmreview_add_result .= '</div>';
            $tmreview_add_result .= '</div>';
            $tmreview_add_result .= '<div class="tmreviews-col">';
            $tmreview_add_result .= '<div class="tmreviews-form-field-icon"></div>';
            $tmreview_add_result .= '<div class="tmreviews-field-lable">';
            $tmreview_add_result .= '<label>' . __('Sub Title','tm-reviews') . '<sup class="tmreviews_estric">*</sup></label>';
            $tmreview_add_result .= '</div>';
            $tmreview_add_result .= '<div class="tmreviews-field-input tmreviews_required">';
            $tmreview_add_result .= '<input title="Enter your title" type="text" class="" value="" id="place_sub_title" name="place_sub_title" placeholder="">';
            $tmreview_add_result .= '<div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">';
            $tmreview_add_result .= '<label>' . __('Description','tm-reviews') . '</label>';

            ob_start();

            wp_editor('', 'blog_description', $settings);
            $editor_contents = ob_get_clean();
            $tmreview_add_result .= '</div>
                            <div class="tmreviews-field-input">';
            $tmreview_add_result .= __($editor_contents);
            $tmreview_add_result .= '<div class="errortext" style="display:none;"></div>';
            $tmreview_add_result .= '</div>';
            $tmreview_add_result .= '</div>';
            $tmreview_add_result .= '</div>';
            $tmreview_add_result .= '<div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Contacts','tmreviews') . '</span>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Phone','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_phone"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Email','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_email"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Website','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_website"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Socials','tm-reviews') . '</span>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Facebook','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_facebook"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Twitter','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_twitter"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Dribble','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_dribble"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('LinkedIn','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_linkedin"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Behance','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_behance"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Instagram','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input">
                            <input type="text" value="" tabindex="5" size="16" name="place_instagram"/>
                            <div class="errortext" style="display:none;"></div>
                        </div>
                    </div>
                </div>
                <div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Images','tm-reviews') . '</span>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Thumbnail Image','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input tmreviews_fileinput">
                            <div class="tmreviews_repeat">
                                <input title="" type="file" class="tmreviews_file" name="thumbnail_image" data-filter-placeholder=""/>
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Logo Image','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input tmreviews_fileinput">
                            <div class="tmreviews_repeat">
                                <input title="" type="file" class="tmreviews_file" name="logo_image" data-filter-placeholder=""/>
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="tmreviews-col">
                        <div class="tmreviews-form-field-icon"></div>
                        <div class="tmreviews-field-lable">
                            <label>' . __('Background Image','tm-reviews') . '</label>
                        </div>
                        <div class="tmreviews-field-input tmreviews_fileinput">
                            <div class="tmreviews_repeat">
                                <input title="" type="file" class="tmreviews_file" name="bg_image" data-filter-placeholder="" />
                                <div class="errortext" style="display:none;"></div>
                            </div>
                        </div>
                    </div>';

            $manage_gallery = __('Manage gallery', 'tm-reviews');
            $clear_gallery  = __('Clear gallery', 'tm-reviews');
            if(is_user_logged_in()){
                if(isset($values['pixad_auto_gallery'])) {
                    $ids = json_decode($values['pixad_auto_gallery'][0]);
                }
                else {
                    $ids = array();
                }
                $cs_ids = is_array($ids) ? implode(",", $ids) : '';
                $html  = do_shortcode('[gallery ids="'.$cs_ids.'"]');
                $html .= '<input id="pixad_auto_gallery_ids" type="hidden" name="place_gallery_ids" value="-1" />';
                $html .= '<input id="manage_gallery" title="'.esc_html($manage_gallery).'" type="button" value="'.esc_html($manage_gallery).'" />';
                $html .= '<input id="clear_gallery" title="'.esc_html($clear_gallery).'" type="button" value="'.esc_html($clear_gallery).'" />';
                $tmreview_add_result .= wp_specialchars_decode($html);
            }

            $tmreview_add_result .= '</div>';
            $tmreview_add_result .= '<div class="tmreviewsrow">
                    <span class="fl-add-place-row-title">' . __('Category','tm-reviews') . '</span>
                    <div class="tmreviews-col">';
            $taxomony = get_terms(tmreviews_get_post_type() . '-category');
            $taxonomy_html = '';
            if(isset($taxomony) && !empty($taxomony)){
                $taxonomy_html .= '<select multiple name="tax[]">';
                foreach ($taxomony as $t){
                    $taxonomy_html .= '<option value = "'.$t->term_id.'">'.$t->name.'</option>';
                }
                $taxonomy_html .= '</select>';
            }
            $tmreview_add_result .= $taxonomy_html;
            $tmreview_add_result .= '</div>
                    <div class="buttonarea tmreviews-full-width-container">
                        <button type="submit" class="fl-custom-btn fl-font-style-bolt-two primary-style"><span>' . __('Submit','tm-reviews') . '</span></button>';
            $tmreview_add_result .= wp_nonce_field( 'tmrv_blog_post' );
            $tmreview_add_result .= '</div>
                </div>
                <div class="all_errors" style="display:none;"></div>
            </form>
        </div>';


            return $tmreview_add_result;
        }




    }
}
add_shortcode( 'tmreviews_add_places', 'tmreviews_add_places_function' );





function my_myme_types($mime_types){
    $mime_types['svg'] = 'image/svg+xml'; //Adding svg extension
    return $mime_types;
}
add_filter('upload_mimes', 'my_myme_types', 1, 1);


function tmreviews_chech_if_exist_meta(){
    global $wpdb;
    $wpdb->get_results( "SELECT meta_key FROM ". $wpdb->prefix ."postmeta where meta_key='tmreviews_post_average'" );
    if (($wpdb->num_rows)>0) {
        return true;
    }
    else {
        return false;
    }
}



// shortcode your_places
add_shortcode( 'tmreviews_your_places', 'youzify_tmreviews_account_tab_your_places_shortcode' );
function youzify_tmreviews_account_tab_your_places_shortcode() {
    if(!is_user_logged_in()){
        $permalink_login = get_the_permalink(get_option('pmpro_login_page_id', true));
        echo '<script>window.location.href = "' . $permalink_login . '"</script>';
    }
    ob_start();
    if ( class_exists( 'TMReviews__Helping_Addons' )) {
        $author_ID = get_current_user_ID();
        $args_places = array(
            'author'    => $author_ID,
            'post_status'    => array('publish', 'pending', 'draft'),
            'numberposts' => -1,
            'post_type' => tmreviews_get_post_type()
        );
        $places      = get_posts( $args_places );

        if ( has_nav_menu( 'account-menu' ) ) {
            wp_nav_menu(array(
                'theme_location'    => 'account-menu',
                'class'             => 'account-menu account-menu',
                'container'         => false,
                'id'                => 'account-menu',
                'depth'             => 8,
                'fallback_cb'       => 'gazek_menu_fallback'
            ));
        }
        ?>
                                <div class="fl-category-container container">
                                    <?php
            $k = 0;


            foreach ( $places as $p ) {
                $k ++;
            }
            $post_count = $k;
            ?>
                                        <?php $i = 0; ?>
                                            <?php $k = 1; ?>
                                                <?php if ( isset( $places ) && ! empty( $places ) ) { ?>
                                                    <?php foreach ( $places as $p ) { ?>
                                                        <?php
                                    $tax_name = get_the_terms( $p->ID, tmreviews_get_post_type() . '-category' );
                                    //Reviews
                                    $total    = 0;
                                    $comments = get_comments( array( 'post_id' => $p->ID ) );
                                    if ( isset( $comments ) && ! empty( $comments ) ) {
                                        $r = 0;
                                        foreach ( $comments as $c ) {
                                            $total += intval( get_comment_meta( $c->comment_ID, 'rating', true ) );

                                            $rate = get_comment_meta( $c->comment_ID, 'rating', true );
                                            if ( isset( $rate ) && $rate != '' ) {
                                                $r ++;
                                            }
                                        }
                                        $reviews_count = $r;
                                        if ( $total != 0 ) {
                                            $average      = $total / $reviews_count;
                                            $rating_icons = '';
                                        } else {
                                            $average      = 0;
                                            $rating_icons = '';
                                        }

                                        $s = 1;
                                        while ( $s <= intval( $average ) ) {
                                            $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                                            $s ++;
                                        }
                                        if ( intval( $average ) < 5 ) {
                                            $asd = 5 - intval( $average );
                                            $j   = 1;
                                            while ( $j <= $asd ) {
                                                $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                                                $j ++;
                                            }
                                        }
                                    }
                                    //Category
                                    if ( isset( $tax_name ) ) {
                                        foreach ( $tax_name as $item ) {
                                            $categories_html = '<a href="' . esc_url( get_term_link( $item->slug, tmreviews_get_post_type() . '-category' ), 'tm-vendor-dashboard' ) . '">' . $item->name . '</a>';
                                        }
                                    }
                                    ?>
                                                            <?php $i ++; ?>
                                                                <?php if ( $i == 1 ) { ?>
                                                                    <div class="fl-cat-row">
                                                                        <?php } ?>
                                                                            <div class="fl-category-single col-md-4 youzify_place_<?php echo get_post_status($p->ID);?>">
                                                                                <?php

                                                                                $add = get_option('tmreviews_add_place', true);
                                                                                if(isset($add) && $add != '' && is_string($add) && $author_ID == get_current_user_ID() && $author_ID != 0 && get_current_user_ID() != 0){
                                                                                        $user = get_user_by('ID', $author_ID); ?>
                                                                                        <a class="edit_btn" href="<?php echo get_the_permalink($add).'?id=' . $p->ID;?>">
                                                                                            <?php echo __('Edit', 'tm-reviews');?>
                                                                                        </a>
                                                                                <?php } ?>
                                                                                        <?php if ( has_post_thumbnail( $p->ID ) ) { ?> <a href="<?php echo esc_url( get_the_permalink( $p->ID ), 'tm-vendor-dashboard' ); ?>"><?php echo get_the_post_thumbnail( $p->ID, 'gazek_size_360x250_crop' ); ?></a>
                                                                                            <?php } else { ?> <a href="<?php echo esc_url( get_the_permalink( $p->ID ), 'tm-vendor-dashboard' ); ?>"><img src="<?php echo esc_url( TMREVIEWS_HELPING_PREVIEW_IMAGE . '/no-image.jpg', 'tm-vendor-dashboard' ); ?>"></a>
                                                                                                <?php } ?>
                                                                                                    <?php if ( isset( $rating_icons ) && $rating_icons != '' && ! empty( $rating_icons ) ) { ?>
                                                                                                        <div class="fl-category-single-top">
                                                                                                            <div class="fl-category-single-rating">
                                                                                                                <?php echo $rating_icons; ?>
                                                                                                            </div>
                                                                                                            <?php if ( isset( $reviews_count ) && $reviews_count != '' && ! empty( $reviews_count ) ) { ?> <span class="fl-places-average"></span>
                                                                                                                <?php } ?>
                                                                                                                    <?php if ( isset( $average ) && $average != '' && ! empty( $average ) ) { ?> <span class="fl-average"><?php echo __( 'User Rating ', 'tm-vendor-dashboard' ); ?><?php echo esc_attr( number_format( $average, 1, '.', ' ' ), 'tm-vendor-dashboard' ) . '/' . '5.0'; ?></span>
                                                                                                                        <?php } ?>
                                                                                                        </div>
                                                                                                        <?php } ?>
                                                                                                            <div class="fl-category-single-middle"> <a class="fl-place-title" href="<?php echo esc_url( get_the_permalink( $p->ID ), 'tm-vendor-dashboard' ); ?>"><?php echo $p->post_title; ?></a>
                                                                                                                <div class="fl-places-average-cat">
                                                                                                                    <?php echo $categories_html; ?>
                                                                                                                </div>
                                                                                                                <?php echo tmreviews_limit_excerpt_search( 20, $p->post_content ); //$p->post_content; ?>
                                                                                                            </div>
                                                                                                            <div class="fl-category-single-bottom">
                                                                                                                <?php
                                            //Author
                                            $author_id   = get_post_field( 'post_author', $p->ID );
                                            $avatar      = get_avatar( $author_id, 'gazek_size_size_50x50_crop' );
                                            $author_name = get_the_author_meta( 'display_name', $author_id );
                                            $page_id     = get_option( 'tmreviews_user_reviews_page_id', true );
                                            if ( isset( $page_id ) && ! empty( $page_id ) ) {
                                                $user_page_link = get_permalink( $page_id );
                                            }
                                            $user_page_link = get_permalink( $page_id );
                                            ?>
                                                                                                                    <a href="<?php echo esc_url( $user_page_link ) ?>?author=<?php echo $author_id; ?>">
                                                                                                                        <?php echo $avatar; ?>
                                                                                                                    </a> <span class="fl-review-author-name"><a href="<?php echo esc_url( $user_page_link ) ?>?author=<?php echo $author_id; ?>"><?php echo $author_name; ?></a></span> </div>
                                                                            </div>
                                                                            <?php if ( $i == 3 || $post_count == $k ) {
                                        $i = 0; ?>
                                                                    </div>
                                                                    <?php } ?>
                                                                        <?php $k ++; ?>
                                                                            <?php } ?>
                                                                                <?php echo the_posts_pagination( array(
                                    'prev_text' => '<i class="fa fa-angle-left" aria-hidden="true"></i>',
                                    'next_text' => '<i class="fa fa-angle-right" aria-hidden="true"></i>',
                                )
                            ); ?>
                                                                                    <?php } else { ?> <span class="tmvendors_no_places"><?php echo __( "You didn't add any places", 'tm-vendor-dashboard' ); ?></span>
                                                                                        <?php } ?>
                                </div>
                                <?php }
    // Get All This Function Content.
    $content = ob_get_contents();

    // Clean
    ob_end_clean();

    return $content;

}



// shortcode your_reviews
add_shortcode( 'youzify_your_reviews', 'youzify_tmreviews_account_tab_your_reviews_shortcode' );
function youzify_tmreviews_account_tab_your_reviews_shortcode() {

    ob_start();
    if ( class_exists( 'TMReviews__Helping_Addons' ) && class_exists('BuddyPress') && class_exists('Youzify') ) {

        $args_places = array(
            'posts_per_page'    => -1,
            'status'    => 'publish',
            'post_type' => tmreviews_get_post_type(),
            'fields'        => 'ids'
        );
        $places      = get_posts( $args_places );
       // var_dump($places);


        $author_ID =  bp_displayed_user_id();
        $avatar    = get_avatar( $author_ID );

        ?>
                                    <div class="fl-user-reviews-content">
                                        <?php if (!empty($places)){ ?>
                                            <?php foreach ($places as $p){ ?>
                                                <?php
                $reviews   = get_comments(array( 'post_id' => $p, 'author__in' => $author_ID ) );
                $reviews_count = count( $reviews );
                $total         = 0;
                ?>
                                                    <?php foreach ( $reviews as $rev ) { ?>
                                                        <?php
                    $total   += intval( get_comment_meta( $rev->comment_ID, 'rating', true ) );
                    $average = $total / $reviews_count;
                    ?>
                                                            <?php } ?>
                                                                <?php if ( isset( $reviews ) && !empty( $reviews ) ) { ?>
                                                                    <?php foreach ( $reviews as $rev ) {
                        $rate = get_comment_meta( $rev->comment_ID, 'rating', true ); ?>
                                                                        <?php if ( isset( $rate ) && $rate != '' ) { ?>
                                                                            <div class="fl-user-reviews-contain" id="comment-<?php echo $rev->comment_ID ?>">
                                                                                <div class="fl-user-reviews-left">
                                                                                    <div class="fl-user-reviews-avatar">
                                                                                        <?php echo $avatar; ?>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="fl-user-reviews-right">
                                                                                    <div class="fl-user-reviews-top">
                                                                                        <div class="fl-user-review-top-post-title"> <span class="fl-user-review-text"><?php echo __( 'Review:', 'tm-vendor-dashboard' ) ?></span>
                                                                                            <?php $post_link = get_post_permalink( $rev->comment_post_ID ); ?>
                                                                                                <?php $post_title = get_the_title( $rev->comment_post_ID ); ?>
                                                                                                    <a class="fl-user-review-place" href="<?php echo esc_url( $post_link, 'tm-vendor-dashboard' ); ?>">
                                                                                                        <?php echo esc_attr( $post_title, 'tm-vendor-dashboard' ); ?>
                                                                                                    </a>
                                                                                        </div>
                                                                                        <div class="fl-user-reviews-rating">
                                                                                            <?php

                                            $total = intval( get_comment_meta( $rev->comment_ID, 'rating', true ));
                                            if ( $rating = intval( get_comment_meta( $rev->comment_ID, 'rating', true ) ) ) {
                                                $rating       = intval( get_comment_meta( $rev->comment_ID, 'rating', true ) );
                                                $rating_icons = '';
                                                $i            = 1;
                                                while ( $i <= $rating ) {
                                                    $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                                                    $i ++;
                                                }
                                                if ( $rating < 5 ) {
                                                    $asd = 5 - $rating;
                                                    $k   = 1;
                                                    while ( $k <= $asd ) {
                                                        $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                                                        $k ++;
                                                    }
                                                }
                                                $commentrating = '<div class="fl-rate-icons">' .
                                                    $rating_icons .
                                                    '</div>';
                                                echo $commentrating;
                                            }
                                            ?>
                                                                                                <select name="rating" id="rating-autos-edit" required="" style="display: none;">
                                                                                                    <option value="">Rate…</option>' +
                                                                                                    <option value="5" <?php echo $total==5 ? 'selected': '';?>>Perfect</option>
                                                                                                    <option value="4" <?php echo $total==4 ? 'selected': '';?>>Good</option>
                                                                                                    <option value="3" <?php echo $total==3 ? 'selected': '';?>>Average</option>
                                                                                                    <option value="2" <?php echo $total==2 ? 'selected': '';?>>Not that bad</option>
                                                                                                    <option value="1" <?php echo $total==1 ? 'selected': '';?>>Very poor</option>
                                                                                                </select>
                                                                                                <div class="fl-user-reviews-rating-text">
                                                                                                    <?php echo __( 'Rating ', 'tm-vendor-dashboard' ) . number_format( $rating, 1, '.', ' ' ) . '/5.0'; ?>
                                                                                                </div>
                                                                                        </div>
                                                                                        <div class="fl-user-reviews-date-contain"> <span class="fl-user-reviews-date-text"><?php echo __( 'Review Published:', 'tm-vendor-dashboard' ); ?></span> <span class="fl-user-reviews-date"><?php echo esc_attr( get_comment_date( 'F j, Y', $rev->comment_ID ), 'tm-vendor-dashboard' ); ?></span> </div>
                                                                                    </div>
                                                                                    <div class="fl-user-review-bottom">
                                                                                        <?php
                                                                    $title     = '"' . get_comment_meta( $rev->comment_ID, 'tmreviews_review_title', true ) . '"';
                                                                    $post_link = get_the_permalink( $rev->comment_post_ID );
                                                                    ?> <span class="fl-review-title"><?php echo esc_attr( $title, 'tm-vendor-dashboard' ); ?></span>
                                                                                            <div class="fl-user-reviews-content">
                                                                                                <?php //echo esc_attr(get_the_content(null, false, $rev->comment_post_ID), 'tm-vendor-dashboard');?>
                                                                                                    <?php echo esc_attr( $rev->comment_content, 'tm-vendor-dashboard' ); ?>
                                                                                            </div>
                                                                                            <div class="fl-user-reviews-edit"> </div>
                                                                                    </div>
                                                                                </div>
                                                                                <?php if($author_ID === get_current_user_ID()){ ?>
                                                                                    <a id="tmreviews_edit_btn_<?php echo $rev->comment_ID ?>" class="tmreviews_btn tm_edit_btn">
                                                                                        <?php echo __('Edit', 'tm-reviews');?>
                                                                                    </a>
                                                                                    <a id="tmreviews_update_btn_<?php echo $rev->comment_ID ?>" class="tmreviews_btn tm_save_btn">
                                                                                        <?php echo __("Save", "tm-reviews");?>
                                                                                    </a>
                                                                                    <a id="tmreviews_del_btn_<?php echo $rev->comment_ID ?>" class="tmreviews_btn tm_del_btn">
                                                                                        <?php echo __("Delete", "tm-reviews");?>
                                                                                    </a>
                                                                                    <script>
                                                                                        jQuery.noConflict()(function ($) {
                                                                                            var rating_htmls = jQuery('#comment-<?php echo $rev->comment_ID ?>').find('.fl-user-reviews-rating');
                                                                                            var cont_htmls = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('.fl-user-reviews-content');
                                                                                            var title_htmls = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('.fl-review-title');
                                                                                            jQuery('#tmreviews_edit_btn_<?php echo $rev->comment_ID ?>').click(function (e) {
                                                                                                if (jQuery(this).hasClass('tm_edit_cancel')) {
                                                                                                    var cont = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('textarea[name="comment"]');
                                                                                                    var title = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('input[name="tmreviews_review_title"]');
                                                                                                    var rating = jQuery('#comment-<?php echo $rev->comment_ID ?>').find('.comment-form-rating.tmeditcomment');
                                                                                                    title.replaceWith('<span class="fl-review-title">' + title_htmls.html() + '</span>');
                                                                                                    cont.replaceWith('<div class="fl-user-reviews-content">' + cont_htmls.html() + '</div>');
                                                                                                    rating.replaceWith(rating_htmls);
                                                                                                    jQuery(this).removeClass('tm_edit_cancel');
                                                                                                    jQuery('#comment-<?php echo $rev->comment_ID ?>').removeClass('tmreviews_btns_active');
                                                                                                    jQuery(this).html("<?php echo __('Edit', 'tm-reviews')?>");
                                                                                                }
                                                                                                else {
                                                                                                    var cont = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('.fl-user-reviews-content');
                                                                                                    var title = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('.fl-review-title');
                                                                                                    var rating = jQuery('#comment-<?php echo $rev->comment_ID ?>').find('.fl-user-reviews-rating');
                                                                                                    var rating_two = rating;
                                                                                                    if (cont.length !== 0) {
                                                                                                        cont.replaceWith('<textarea name="comment">' + cont.html().trim() + '</textarea>');
                                                                                                    }
                                                                                                    else {
                                                                                                        if (jQuery(this).siblings('textarea[name="comment"]').length === 0) {
                                                                                                            jQuery(this).parent().append('<textarea name="comment"></textarea>');
                                                                                                        }
                                                                                                    }
                                                                                                    if (title.length !== 0) {
                                                                                                        title.replaceWith('<input type="text" name="tmreviews_review_title" value=' + title.html() + '/>');
                                                                                                    }
                                                                                                    else {
                                                                                                        if (jQuery(this).siblings('input[name="tmreviews_review_title"]').length === 0) {
                                                                                                            jQuery(this).parent().append('<input type="text" name="tmreviews_review_title"/>');
                                                                                                        }
                                                                                                    }
                                                                                                    if (rating.length !== 0) {
                                                                                                        var rating_html = '<div class="comment-form-rating tmeditcomment">' + '        <label><?php echo __("Your rating", "tm-reviews");?></label>' + '        <p class="stars selected"><span>' + '<a class="star-1 <?php echo $total == 1 ? '
                                                                                                        active ':'
                                                                                                        ';?>">1</a>' + '<a class="star-2 <?php echo $total == 2 ? '
                                                                                                        active ':'
                                                                                                        ';?>">2</a>' + '<a class="star-3 <?php echo $total == 3 ? '
                                                                                                        active ':'
                                                                                                        ';?>">3</a>' + '<a class="star-4 <?php echo $total == 4 ? '
                                                                                                        active ':'
                                                                                                        ';?>">4</a>' + '<a class="star-5 <?php echo $total == 5 ? '
                                                                                                        active ':'
                                                                                                        ';?>">5</a>' + '</span></p><select name="rating" id="rating-autos-edit" required="" style="display: none;">' + '                            <option value="">Rate…</option>' + '                            <option value="5" <?php echo $total == 5 ? '
                                                                                                        selected ':'
                                                                                                        ';?>>Perfect</option>' + '                            <option value="4" <?php echo $total == 4 ? '
                                                                                                        selected ':'
                                                                                                        ';?>>Good</option>' + '                            <option value="3" <?php echo $total == 3 ? '
                                                                                                        selected ':'
                                                                                                        ';?>>Average</option>' + '                            <option value="2" <?php echo $total == 2 ? '
                                                                                                        selected ':'
                                                                                                        ';?>>Not that bad</option>' + '                            <option value="1" <?php echo $total == 1 ? '
                                                                                                        selected ':'
                                                                                                        ';?>>Very poor</option>' + '                        </select></div>' + '<script>jQuery.noConflict()(function($) {' + 'jQuery(".comment-form-rating.tmeditcomment a").click(function (e) {' + 'e.preventDefault();' + 'jQuery(".comment-form-rating.tmeditcomment a").removeClass("active");' + 'jQuery(this).addClass("active");' + 'jQuery("#rating-autos-edit option[value=\"+jQuery(this).html()+\"]").prop("selected", true);' + '});' + '});';
                                                                                                        rating.replaceWith(rating_html);
                                                                                                    }
                                                                                                    else {
                                                                                                        if (jQuery(this).siblings('input[name="tmreviews_review_title"]').length === 0) {
                                                                                                            jQuery(this).parent().append('<input type="text" name="tmreviews_review_title"/>');
                                                                                                        }
                                                                                                    }
                                                                                                    jQuery(this).addClass('tm_edit_cancel');
                                                                                                    jQuery('#comment-<?php echo $rev->comment_ID ?>').addClass('tmreviews_btns_active');
                                                                                                    jQuery(this).html("<?php echo __('Cancel', 'tm-reviews')?>");
                                                                                                }
                                                                                            });
                                                                                            jQuery('.comment-form-rating.tmeditcomment a').click(function (e) {
                                                                                                e.preventDefault();
                                                                                            });
                                                                                            jQuery('#tmreviews_update_btn_<?php echo $rev->comment_ID ?>').click(function (e) {
                                                                                                jQuery('#comment-<?php echo $rev->comment_ID?>').addClass('ajax-loading');
                                                                                                // var new_text = jQuery(this).siblings('textarea[name="comment"]');
                                                                                                // var new_title = jQuery(this).siblings('input[name="tmreviews_review_title"]');
                                                                                                // var new_rate = jQuery(this).parents('.comment-meta').find('#rating-autos-edit');
                                                                                                var new_text = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('textarea[name="comment"]');
                                                                                                var new_title = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('input[name="tmreviews_review_title"]');
                                                                                                var new_rate = jQuery('#comment-<?php echo $rev->comment_ID ?>').find('#rating-autos-edit');
                                                                                                var form_data = {};
                                                                                                form_data['id'] = <?php echo $rev->comment_ID;?>;
                                                                                                form_data['action'] = 'tmcomment_update';
                                                                                                form_data['text'] = new_text.val();
                                                                                                form_data['title'] = new_title.val();
                                                                                                form_data['rate'] = new_rate.val();
                                                                                                jQuery.post(tm_reviews_ajax.url, form_data, function (response) {
                                                                                                    // console.log(response['account']);
                                                                                                    var cont = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('textarea[name="comment"]');
                                                                                                    var title = jQuery('#comment-<?php echo $rev->comment_ID;?>').find('input[name="tmreviews_review_title"]');
                                                                                                    var rating = jQuery('#comment-<?php echo $rev->comment_ID ?>').find('.comment-form-rating.tmeditcomment');
                                                                                                    // title.replaceWith('<span class="fl-review-title">' + title_htmls.html() + '</span>');
                                                                                                    // cont.replaceWith('<div class="fl-user-reviews-content">' + cont_htmls.html() + '</div>');
                                                                                                    //rating.replaceWith(rating_htmls);
                                                                                                    cont.replaceWith('<div class="fl-user-reviews-content">' + cont.val() + '</span>');
                                                                                                    title.replaceWith('<span class="fl-review-title">"' + title.val() + '"</span>');
                                                                                                    var rating_html = response['account'];
                                                                                                    rating.replaceWith(rating_html);
                                                                                                    jQuery('#tmreviews_edit_btn_<?php echo $rev->comment_ID ?>').html("<?php echo __('Edit', 'tm-reviews')?>");
                                                                                                    jQuery('#comment-<?php echo $rev->comment_ID?>').removeClass('ajax-loading');
                                                                                                    jQuery('#comment-<?php echo $rev->comment_ID ?>').removeClass('tmreviews_btns_active');
                                                                                                });
                                                                                            });
                                                                                            jQuery('#tmreviews_del_btn_<?php echo $rev->comment_ID ?>').click(function (e) {
                                                                                                var form_data = {};
                                                                                                form_data['id'] = <?php echo $rev->comment_ID;?>;
                                                                                                form_data['action'] = 'tmcomment_delete';
                                                                                                jQuery.post(tm_reviews_ajax.url, form_data, function (response) {
                                                                                                    // console.log(response);
                                                                                                    jQuery('#comment-<?php echo $rev->comment_ID ?>').remove();
                                                                                                });
                                                                                            });
                                                                                        });
                                                                                    </script>
                                                                                    <?php } ?>
                                                                            </div>
                                                                            <?php } ?>
                                                                                <?php } ?>
                                                                                    <?php } ?>
                                                                                        <?php } ?>
                                                                                            <?php } else { ?> <span class="tmvendors_no_places"><?php echo __( "You didn't add any reviews", 'tm-vendor-dashboard' ); ?></span>
                                                                                                <?php } ?>
                                    </div>
                                    <?php
    }
    // Get All This Function Content.
    $content = ob_get_contents();

    // Clean
    ob_end_clean();

    return $content;

}




add_action( 'init', 'tmreviews_form_head' );
function tmreviews_form_head(){
    if (!is_admin()) {
        acf_form_head();
    }
}


add_filter( 'body_class', 'tmreviews_custom_body_class' );
function tmreviews_custom_body_class( array $classes ) {
    if(class_exists('BuddyPress') && class_exists('Youzify')){
        if (get_current_user_id() == bp_displayed_user_id()){
            $classes[] = 'youzify_your_account';
        }
    }


    return $classes;
}















// shortcode add_place
add_shortcode( 'tmreviews_add_place', 'youzify_tmreviews_account_tab_add_place_shortcode' );
function youzify_tmreviews_account_tab_add_place_shortcode() {
    if ( ! is_user_logged_in() ) {
        return;
    } else {
        if(class_exists('MemberOrder')){
            if(function_exists('pmpro_getAllLevels')){
                $levels = pmpro_getAllLevels(false, true);
                $levels_array = [];
                foreach ($levels as $value) {
                    $levels_array[] = $value->id;
                }
            }
            $member_notice = '<div class="fl-user-memb-content"><span class="tmvendors_memb">' . esc_attr( 'Membership expired' ) . '</span></div>';
            if(!pmpro_hasMembershipLevel($levels_array)) {
                if ( isset( $member_notice ) && $member_notice != ''){
                   // echo $member_notice;
                }
                return;
            }
        }
    }



    ob_start();



    if ( class_exists( 'TMReviews__Helping_Addons' ) ) {
        add_action( 'wp_enqueue_scripts', 'autozone_enqueue_media' );
        $settings = array(
            'wpautop'           => false,
            'media_buttons'     => false,
            'textarea_name'     => 'blog_description',
            'textarea_rows'     => 10,
            'tabindex'          => '',
            'tabfocus_elements' => ':prev,:next',
            'editor_css'        => '',
            'editor_class'      => '',
            'teeny'             => false,
            'dfw'               => false,
            'tinymce'           => false,
            'quicktags'         => false
        );
        if ( isset( $_POST['place_title'] ) ) {
            $retrieved_nonce = filter_input( INPUT_POST, '_wpnonce' );
            if ( ! wp_verify_nonce( $retrieved_nonce, 'tmrv_blog_post' ) ) {
                die( __( 'Failed security check', 'tmreviews' ) );
            }
            $exclude = array( "_wpnonce", "_wp_http_referer", "pg_blog_submit" );
            $post    = $_POST;
            if ( ! isset( $post['blog_tags'] ) ) {
                $post['blog_tags'] = '';
            }
            $allowed_ext = 'jpg|jpeg|png|gif';

            $arg    = array(
                'post_title'   => $post['place_title'],
                'post_status'  => 'pending',
                'post_type'    => tmreviews_get_post_type(),
                'post_content' => wp_rel_nofollow( $post['blog_description'] ),
            );
            $postid = wp_insert_post( $arg );

            $tax_array = array();
            if ( isset( $post['tax'] ) && ! empty( $post['tax'] ) ) {
                foreach ( $post['tax'] as $p ) {
                    $tax_array[] = intval( $p );
                }

                wp_set_object_terms( $postid, $tax_array, tmreviews_get_post_type() . '-category' );

            }

            update_post_meta( $postid, 'place_bg_cl', '#32297b' );
            update_post_meta( $postid, '_place_bg_cl', 'field_asd3adsdw842b' );

            //Gallery
            if ( isset( $post['place_gallery_ids'] ) && $post['place_gallery_ids'] != '' ) {

                $encode_gallery = explode( ',', $post['place_gallery_ids'] );

                update_post_meta( $postid, 'place_gallery', $encode_gallery );
                update_post_meta( $postid, '_place_gallery', 'field_5f03547a50164' );

            }

            //Images
            if ( isset( $_FILES['thumbnail_image'] ) ) {
                $attchment_th_id = tmreview_make_upload_and_get_attached_id( $_FILES['thumbnail_image'], $allowed_ext, array(), $postid );
                set_post_thumbnail( $postid, $attchment_th_id );
            }

            if ( isset( $_FILES['logo_image'] ) ) {
                $attchment_lg_id = tmreview_make_upload_and_get_attached_id( $_FILES['logo_image'], $allowed_ext, array(), $postid );
                update_post_meta( $postid, 'place_logo', $attchment_lg_id );
                update_post_meta( $postid, '_place_logo', 'field_asd345r4842b' );
            }

            if ( isset( $_FILES['bg_image'] ) ) {
                $attchment_bg_id = tmreview_make_upload_and_get_attached_id( $_FILES['bg_image'], $allowed_ext, array(), $postid );
                update_post_meta( $postid, 'place_bg', $attchment_bg_id );
                update_post_meta( $postid, '_place_bg', 'field_asd34asdw842b' );
            }

            //Text fields
            if ( isset( $post['place_sub_title'] ) ) {
                $place_sub_title = $post['place_sub_title'];
                update_post_meta( $postid, 'place_subtitle', $place_sub_title );
                update_post_meta( $postid, '_place_subtitle', 'field_asd356f24842b' );
            }

            if ( isset( $post['place_phone'] ) ) {
                $place_phone = $post['place_phone'];
                update_post_meta( $postid, 'place_phone', $place_phone );
                update_post_meta( $postid, '_place_phone', 'field_5ed556f24asdwd' );
            }

            if ( isset( $post['place_email'] ) ) {
                $place_email = $post['place_email'];
                update_post_meta( $postid, 'place_email', $place_email );
                update_post_meta( $postid, '_place_email', 'field_5edascaf24aacwd' );
            }

            if ( isset( $post['place_website'] ) ) {
                $place_website = $post['place_website'];
                update_post_meta( $postid, 'place_website', $place_website );
                update_post_meta( $postid, '_place_website', 'field_5edascaf24asdwd' );
            }

            //Socials
            if ( isset( $post['place_facebook'] ) ) {
                $place_sub_title = $post['place_facebook'];
                update_post_meta( $postid, 'socials_facebook', $place_sub_title );
                update_post_meta( $postid, '_socials_facebook', 'field_5ed7d1f7f9966' );
                update_post_meta( $postid, '_socials', 'field_5ed7d1edf9965' );
            }

            if ( isset( $post['place_twitter'] ) ) {
                $place_sub_title = $post['place_twitter'];
                update_post_meta( $postid, 'socials_twitter', $place_sub_title );
                update_post_meta( $postid, '_socials_twitter', 'field_5ed7d209f9967' );
                update_post_meta( $postid, '_socials', 'field_5ed7d1edf9965' );
            }


            if ( isset( $post['place_dribble'] ) ) {
                $place_sub_title = $post['place_dribble'];
                update_post_meta( $postid, 'socials_dribble', $place_sub_title );
                update_post_meta( $postid, '_socials_dribble', 'field_5ed7d220f9968' );
                update_post_meta( $postid, '_socials', 'field_5ed7d1edf9965' );
            }

            if ( isset( $post['place_linkedin'] ) ) {
                $place_sub_title = $post['place_linkedin'];
                update_post_meta( $postid, 'socials_linkedin', $place_sub_title );
                update_post_meta( $postid, '_socials_linkedin', 'field_5ed7d22ef9969' );
                update_post_meta( $postid, '_socials', 'field_5ed7d1edf9965' );
            }

            if ( isset( $post['place_behance'] ) ) {
                $place_sub_title = $post['place_behance'];
                update_post_meta( $postid, 'socials_behance', $place_sub_title );
                update_post_meta( $postid, '_socials_behance', 'field_5ed7d23cf996a' );
                update_post_meta( $postid, '_socials', 'field_5ed7d1edf9965' );
            }

            if ( isset( $post['place_instagram'] ) ) {
                $place_sub_title = $post['place_instagram'];
                update_post_meta( $postid, 'socials_instagram', $place_sub_title );
                update_post_meta( $postid, '_socials_instagram', 'field_5ed7d245f996b' );
                update_post_meta( $postid, '_socials', 'field_5ed7d1edf9965' );
            }



            //Rating Fields
            if ( isset( $post['tmreviews_review_title'] ) ) {
                $tmreviews_review_title = $post['tmreviews_review_title'];
                update_post_meta( $postid, 'review_tmreviews_review_title', $tmreviews_review_title );
                update_post_meta( $postid, '_review_tmreviews_review_title', 'field_5ed7vvd1f7fddasas9966' );
            }

            if ( isset( $post['review_comment'] ) ) {
                $review_comment = $post['review_comment'];
                update_post_meta( $postid, 'review_review_comment', $review_comment );
                update_post_meta( $postid, '_review_review_comment', 'field_5fas209fef9967' );
            }

            if ( isset( $post['rating'] ) ) {
                $rating = $post['rating'];
                if($rating != ''){
                    update_post_meta( $postid, 'review_rating', $rating );
                    update_post_meta( $postid, '_review_rating', 'field2_5ddasdas32nuu65' );
                }
            }



            if ( isset( $post['tmreviews_review_pros'] ) && !empty($post['tmreviews_review_pros'])) {
                $s = 0;
                foreach ($post['tmreviews_review_pros'] as $pros){
                    update_post_meta( $postid, 'review_review_pros_' . $s . '_tmreviews_review_pros', $pros );
                    update_post_meta( $postid, '_review_review_pros_' . $s . '_tmreviews_review_pros', 'field_656544d787wfgsfd2v' );
                    $s++;
                }
                update_post_meta( $postid, 'review_review_pros', count($post['tmreviews_review_pros']) );
                update_post_meta( $postid, '_review_review_pros', 'field2_5dds234fadasdas32nuu65' );
            }


            if ( isset( $post['tmreviews_review_cons'] ) && !empty($post['tmreviews_review_cons'])) {
                $c = 0;
                foreach ($post['tmreviews_review_cons'] as $cons){
                    update_post_meta( $postid, 'review_review_cons_' . $c . '_tmreviews_review_cons', $cons );
                    update_post_meta( $postid, '_review_review_cons_' . $c . '_tmreviews_review_cons', 'field_656544d787wfgsfd2v' );
                    $c++;
                }
                update_post_meta( $postid, 'review_review_cons', count($post['tmreviews_review_cons']) );
                update_post_meta( $postid, '_review_review_cons', 'field2_5dds234fa3adasdas32nuu65b' );
            }



            if ( isset( $post['affiliate_link'] ) ) {
                $affiliate_link = $post['affiliate_link'];
                update_post_meta( $postid, 'affiliate_link', $affiliate_link );
                update_post_meta( $postid, '_affiliate_link', 'field_5edascaf24asdwd333123123fasfs' );
            }


            if ( isset( $post['video_link'] ) ) {
                $video_link = $post['video_link'];
                update_post_meta( $postid, 'video_link', $video_link );
                update_post_meta( $postid, '_video_link', 'field_5edadf24asdwa23fassfs' );
            }


            if ( isset( $post['empl_name'] ) && is_array($post['empl_name'] ) && !empty($post['empl_name'])) {
                $s = 0;
                foreach ($post['empl_name'] as $empl_name){
                    if(isset($empl_name) && $empl_name != ''){

                        update_post_meta( $postid, 'employers', count($post['empl_name']) );
                        update_post_meta( $postid, '_employers', 'field2_5dds2343423asdh232nusadus232n65' );

                        update_post_meta( $postid, 'employers_' . $s . '_empl_name', $empl_name );
                        update_post_meta( $postid, '_employers_' . $s . '_empl_name', 'field_6th78s898457erbbfccc7875f' );

                        $s++;
                    }
                }
            }


            if ( isset( $post['empl_pos'] ) && is_array($post['empl_pos'] ) && !empty($post['empl_pos'])) {
                $as = 0;
                foreach ($post['empl_pos'] as $empl_pos){
                    if(isset($empl_pos) && $empl_pos != ''){

                        update_post_meta( $postid, 'employers', count($post['empl_pos']) );
                        update_post_meta( $postid, '_employers', 'field2_5dds2343423asdh232nusadus232n65' );

                        update_post_meta( $postid, 'employers_' . $as . '_empl_position', $empl_pos );
                        update_post_meta( $postid, '_employers_' . $as . '_empl_position', 'field_6thcc7875f' );

                        $as++;
                    }
                }
            }


            if ( isset( $_FILES['empl_img'] )  && is_array($_FILES['empl_img'] ) && !empty($_FILES['empl_img'])) {
                $asa = 0;

                foreach ($_FILES['empl_img']['name'] as $empl_img){

                    if(count($_FILES['empl_img']['name']) >= $asa){
                        $file['name'] = $_FILES['empl_img']['name'][$asa];
                        $file['type'] = $_FILES['empl_img']['type'][$asa];
                        $file['tmp_name'] = $_FILES['empl_img']['tmp_name'][$asa];
                        $file['error'] = $_FILES['empl_img']['error'][$asa];
                        $file['size'] = $_FILES['empl_img']['size'][$asa];


                        $attchment_th_id = tmreview_make_upload_and_get_attached_id($file, $allowed_ext, array(), $postid );

                        update_post_meta( $postid, 'employers_' . $asa . '_empl_img', $attchment_th_id );
                        update_post_meta( $postid, '_employers_' . $asa . '_empl_img', 'field_asd34asffdaffsdasfdw842b' );

                        $asa++;

                    }



                }
            }



            $added_notice = '<span class="tmreviews_added_notice tmreviews_added_notice_visible">' . esc_attr( 'Submitted for moderation' ) . '</span>';

            //$redirect_url = get_the_permalink();
            //echo ("<script>location.href = '".$redirect_url."'</script>");

        }
        $form_url     = admin_url( 'admin-post.php' );
        $redirect_url = get_the_permalink( get_the_ID() );
        ?>
                                        <?php if ( isset( $added_notice ) && $added_notice != '' ) { ?>
                                            <?php echo $added_notice; ?>
                                                <?php } ?>
                                                    <?php if(isset($_GET['id']) && $_GET['id'] != ''){
            acf_form(array(
                'post_id'       => $_GET['id'],
                'post_title'    => true,
                'post_content'  => true,

                'fields' => array(
                    'field_asd345r4842b',
                    'field_asd34asdw842b',
                    'field_asd356f24842b',
                    'field_5f03547a50164',
                    'field_5ed556f24842b',
                    'field_5ed556f24asdwd',
                    'field_5edascaf24aacwd',
                    'field_5edascaf24asdwd',
                    'field_5ed7d1edf9965',

                    'field_5ed7ffedfsw99asf5',
                    'field_5edadf24asdwa23fassfs',
                    'field2_5dds2343423asdh232nusadus232n65'
                ),
                'submit_value'  => __('Update')
            ));
        } else { ?>
                                                        <div class="tmreviews-add-place container">
                                                            <form class="tmreviewsagic-form tmreviews-dbfl" method="post" action="<?php echo esc_url( $redirect_url ); ?>" id="tmreviews_add_blog_post" name="tmreviews_add_blog_post" enctype="multipart/form-data">
                                                                <ul id="stepForm" class="ui-accordion-container">
                                                                    <li id="sf1">
                                                                        <a href='#' class="ui-accordion-link"></a>
                                                                        <div class="tmreviewsrow"> <span class="fl-add-place-row-title"><?php echo __( 'General', 'tm-vendor-dashboard' ); ?></span>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Title', 'tm-vendor-dashboard' ); ?><sup class="tmreviews_estric">*</sup></label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input tmreviews_required">
                                                                                    <input type="text" class="inputclass pageRequired" value="" id="place_title" name="place_title" placeholder="<?php _e( 'Yoga Professor', 'tm-vendor-dashboard' ); ?>">
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Sub Title', 'tm-vendor-dashboard' ); ?><sup class="tmreviews_estric">*</sup></label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input tmreviews_required">
                                                                                    <input class="inputclass pageRequired" type="text" class="" value="" id="place_sub_title" name="place_sub_title" placeholder="<?php _e( 'Health & Life', 'tm-vendor-dashboard' ); ?>">
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Description', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <?php wp_editor( '', 'blog_description', $settings ); ?>
                                                                                        <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="fxdivide"></div>
                                                                            <div class="buttonWrapper-steps buttonWrapper-step-first">
                                                                                <input name="formNext1" type="button" class="open1 nextbutton" value="Next" alt="Next" title="Next"> </div>
                                                                        </div>
                                                                    </li>
                                                                    <li id="sf2">
                                                                        <a href='#' class="ui-accordion-link"></a>
                                                                        <div class="tmreviewsrow"> <span class="fl-add-place-row-title"><?php echo __( 'Contacts', 'tmreviews' ); ?></span>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Company Phone', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <input type="number" class="inputclass  phone" value="" tabindex="5" size="16" name="place_phone" />
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Company Email', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <input type="text" class="inputclass pageRequired email" value="" tabindex="5" size="16" name="place_email" />
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Company Website URL', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <input type="text" value="" class="inputclass  " tabindex="5" size="16" name="place_website" />
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="fxdivide"></div>
                                                                            <div class="buttonWrapper-steps">
                                                                                <input name="formBack0" type="button" class="open0 prevbutton" value="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>">
                                                                                <input name="formNext2" type="button" class="open2 nextbutton" value="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>"> </div>
                                                                        </div>
                                                                    </li>
                                                                    <li id="sf3">
                                                                        <a href='#' class="ui-accordion-link"></a>
                                                                        <div class="tmreviewsrow tmreviewsrow_social"> <span class="fl-add-place-row-title"><?php echo __( 'Socials', 'tm-vendor-dashboard' ); ?></span>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Facebook', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <input type="text" value="" tabindex="5" size="16" name="place_facebook" />
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Twitter', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <input type="text" value="" tabindex="5" size="16" name="place_twitter" />
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Dribble', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <input type="text" value="" tabindex="5" size="16" name="place_dribble" />
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'LinkedIn', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <input type="text" value="" tabindex="5" size="16" name="place_linkedin" />
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Behance', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <input type="text" value="" p tabindex="5" size="16" name="place_behance" />
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Instagram', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input">
                                                                                    <input type="text" value="" tabindex="5" size="16" name="place_instagram" />
                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="fxdivide"></div>
                                                                            <div class="buttonWrapper-steps">
                                                                                <input name="formBack0" type="button" class="open1 prevbutton" value="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>">
                                                                                <input name="formNext3" type="button" class="open3 nextbutton" value="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>"> </div>
                                                                        </div>
                                                                    </li>
                                                                    <li id="sf4">
                                                                        <a href='#' class="ui-accordion-link"></a>
                                                                        <div class="tmreviewsrow"> <span class="fl-add-place-row-title"><?php echo __( 'Images', 'tm-vendor-dashboard' ); ?></span>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Catalog Image <span class="label-info-small">(recommended size 372x259)</span>', 'tm-vendor-dashboard' ); ?></label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input tmreviews_fileinput">
                                                                                    <div class="tmreviews_repeat">
                                                                                        <input title="" type="file" class="tmreviews_file " name="thumbnail_image" data-filter-placeholder="" />
                                                                                        <div class="errortext" style="display:none;"></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Logo Image', 'tm-vendor-dashboard' ); ?>
                                                                                    </label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input tmreviews_fileinput">
                                                                                    <div class="tmreviews_repeat">
                                                                                        <input title="" type="file" class="tmreviews_file pageRequired" name="logo_image" data-filter-placeholder="" />
                                                                                        <div class="errortext" style="display:none;"></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="tmreviews-col">
                                                                                <div class="tmreviews-form-field-icon"></div>
                                                                                <div class="tmreviews-field-lable">
                                                                                    <label>
                                                                                        <?php _e( 'Hero Image <span class="label-info-small">(recommended size 1620x900)</span>', 'tm-vendor-dashboard' ); ?></label>
                                                                                </div>
                                                                                <div class="tmreviews-field-input tmreviews_fileinput">
                                                                                    <div class="tmreviews_repeat">
                                                                                        <input title="" type="file" class="tmreviews_file pageRequired" name="bg_image" data-filter-placeholder="" />
                                                                                        <div class="errortext" style="display:none;"></div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <?php
                                $manage_gallery = __( 'Manage gallery', 'tm-vendor-dashboard' );
                                $clear_gallery  = __( 'Clear gallery', 'tm-vendor-dashboard' );
                                if ( is_user_logged_in() ) {
                                    if ( isset( $values['pixad_auto_gallery'] ) ) {
                                        // $ids = json_decode($values['pixad_auto_gallery'][0]);
                                    } else {
                                        //$ids = array();
                                    }
                                    //$cs_ids = is_array($ids) ? implode(",", $ids) : '';
                                    //$html  = do_shortcode('[gallery ids="'.$cs_ids.'"]');
                                    $html = '';
                                    $html .= '<input id="pixad_auto_gallery_ids" type="hidden" name="place_gallery_ids" value="-1" class="pageRequired" />';
                                    $html .= '<input id="manage_gallery" title="' . esc_html( $manage_gallery ) . '" type="button" value="' . esc_html( $manage_gallery ) . '" />';
                                    $html .= '<input id="clear_gallery" title="' . esc_html( $clear_gallery ) . '" type="button" value="' . esc_html( $clear_gallery ) . '" />';
                                    echo wp_specialchars_decode( $html );
                                }
                                ?>
                                                                                <div class="fxdivide"></div>
                                                                                <div class="buttonWrapper-steps">
                                                                                    <input name="formBack3" type="button" class="open2 prevbutton" value="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>">
                                                                                    <input name="formNext4" type="button" class="open4 nextbutton" value="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>"> </div>
                                                                        </div>
                                                                    </li>
                                                                    <li id="sf5">
                                                                        <a href='#' class="ui-accordion-link"></a>
                                                                        <div class="tmreviewsrow"> <span class="fl-add-place-row-title"><?php echo __('Author Review','tm-reviews');?></span>
                                                                            <div class="tmreviews-col" id="respond">
                                                                                <div class="comment-form-rating">
                                                                                    <label>Your rating</label>
                                                                                    <select name="rating" id="rating-autos" required="" style="display: none;">
                                                                                        <option value="">Rate…</option>
                                                                                        <option value="5">Perfect</option>
                                                                                        <option value="4">Good</option>
                                                                                        <option value="3">Average</option>
                                                                                        <option value="2">Not that bad</option>
                                                                                        <option value="1">Very poor</option>
                                                                                    </select>
                                                                                </div>
                                                                                <div class="author_comment_title">
                                                                                    <input id="fl-title" name="tmreviews_review_title" type="text" class="fl-title" placeholder="Title"> </div>
                                                                                <div class="author_comment_pros_cons"> <span class="comment_pros_cons_add"></span>
                                                                                    <div class="author_comment_pros_cons_contain">
                                                                                        <input id="fl-pros" name="tmreviews_review_pros[]" type="text" class="fl-pros" placeholder="Pros if have">
                                                                                        <input id="fl-cons" name="tmreviews_review_cons[]" type="text" class="fl-cons" placeholder="Cons if have"> </div>
                                                                                </div>
                                                                                <input type="hidden" name="check_the_preview" value="7815109491">
                                                                                <div class="author-comment">
                                                                                    <textarea name="review_comment" rows="5" placeholder="Enter your Review *"></textarea>
                                                                                </div>
                                                                                <input type="hidden" id="_wp_unfiltered_html_comment_disabled" name="_wp_unfiltered_html_comment" value="4e9bd5a5c0">
                                                                                <script>
                                                                                    (function () {
                                                                                        if (window === window.parent) {
                                                                                            document.getElementById('_wp_unfiltered_html_comment_disabled').name = '_wp_unfiltered_html_comment';
                                                                                        }
                                                                                    })();
                                                                                </script>
                                                                                <script>
                                                                                    jQuery.noConflict()(function ($) {
                                                                                        jQuery(".comments-list").find("#rating-autos").remove();
                                                                                    });
                                                                                </script>
                                                                            </div>
                                                                            <div class="buttonWrapper-steps">
                                                                                <input name="formBack3" type="button" class="open3 prevbutton" value="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>">
                                                                                <input name="formNext5" type="button" class="open5 nextbutton" value="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>"> </div>
                                                                        </div>
                                                                    </li>
                                                                    <li id="sf7">
                                                                        <a href='#' class="ui-accordion-link"></a>
                                                                        <div class="tmreviewsrow"> <span class="fl-add-place-row-title"><?php echo __('Employers','tm-reviews');?></span>
                                                                            <div class="tmreviews-col" id="respond">
                                                                                <p class="step-info">
                                                                                    <?php echo __('If the company has any employees, you have the option to include them in your rating.','tm-reviews');?>
                                                                                </p>
                                                                                <div class="fl-employer"> <span class="employers_add"></span>
                                                                                    <div class="fl-employer-container">
                                                                                        <div class="tmreviews-col">
                                                                                            <div class="tmreviews-form-field-icon"></div>
                                                                                            <div class="tmreviews-field-lable">
                                                                                                <label>
                                                                                                    <?php _e( 'Avatar Image', 'tm-vendor-dashboard' ); ?>
                                                                                                </label>
                                                                                            </div>
                                                                                            <div class="tmreviews-field-input tmreviews_fileinput">
                                                                                                <div class="tmreviews_repeat">
                                                                                                    <input title="" type="file" class="tmreviews_file " name="empl_img[]" data-filter-placeholder="" />
                                                                                                    <div class="errortext" style="display:none;"></div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                        <input id="fl-empl-name" name="empl_name[]" type="text" class="fl-empl" placeholder="<?php echo __('Employer Name', 'tm-reviews');?>">
                                                                                        <input id="fl-empl-pos" name="empl_pos[]" type="text" class="fl-empl" placeholder="<?php echo __('Employer Position','tm-reviews');?>"> </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="buttonWrapper-steps">
                                                                                <input name="formBack6" type="button" class="open5 prevbutton" value="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>">
                                                                                <input name="formNext7" type="button" class="open7 nextbutton" value="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Next', 'tm-vendor-dashboard' ); ?>"> </div>
                                                                        </div>
                                                                    </li>
                                                                    <li id="sf8">
                                                                        <a href='#' class="ui-accordion-link"></a>
                                                                        <div class="tmreviewsrow"> <span class="fl-add-place-row-title"><?php echo __( 'Category', 'tm-vendor-dashboard' ); ?></span>
                                                                            <div class="tmreviews-col">
                                                                                <?php
                                    $taxomony      = get_terms( tmreviews_get_post_type() . '-category' );
                                    $taxonomy_html = '';
                                    if ( isset( $taxomony ) && ! empty( $taxomony ) ) {
                                        $taxonomy_html .= '<select multiple name="tax[]">';
                                        foreach ( $taxomony as $key => $t ) {
                                            if( $key == 0 ){
                                                $selected = 'selected';
                                            } else {
                                                $selected = '';
                                            }

                                            $taxonomy_html .= '<option value = "' . $t->term_id . '" ' . $selected . ' >' . $t->name . '</option>';
                                        }
                                        $taxonomy_html .= '</select>';
                                    }
                                    echo $taxonomy_html;
                                    ?>
                                                                            </div>
                                                                            <div class="fxdivide"></div>
                                                                            <div class="buttonWrapper-steps">
                                                                                <input name="formBack0" type="button" class="open6 prevbutton" value="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" alt="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>" title="<?php _e( 'Previous', 'tm-vendor-dashboard' ); ?>">
                                                                                <button type="submit" class="fl-custom-btn fl-font-style-bolt-two primary-style"> <span><?php _e( 'Submit', 'tm-vendor-dashboard' ); ?></span></button>
                                                                                <?php wp_nonce_field( 'tmrv_blog_post' ); ?>
                                                                            </div>
                                                                        </div>
                                                                        <div class="all_errors" style="display:none;"></div>
                                                                    </li>
                                                                </ul>
                                                            </form>
                                                            <?php $user = get_user_by( 'ID', get_current_user_ID()); ?>
                                                                <input type="hidden" id="tmrev_logged_user_email" value="<?php echo esc_attr($user->user_email);?>" />
                                                                <script>
                                                                    jQuery.noConflict()(function ($) {
                                                                        // accordion functions
                                                                        var accordion = jQuery("#stepForm").accordion();
                                                                        var current = 0;
                                                                        var logged_user = jQuery('#tmrev_logged_user_email').val();
                                                                        jQuery.validator.addMethod("pageRequired", function (value, element) {
                                                                            var $element = jQuery(element)

                                                                            function match(index) {
                                                                                return current == index && jQuery(element).parents("#sf" + (index + 1)).length;
                                                                            }
                                                                            if (match(0) || match(1) || match(2)) {
                                                                                return !this.optional(element);
                                                                            }
                                                                            return "dependency-mismatch";
                                                                        }, jQuery.validator.messages.required)
                                                                        var v = jQuery("#tmreviews_add_blog_post").validate({
                                                                            errorClass: "warning"
                                                                            , onkeyup: false
                                                                            , onfocusout: false
                                                                        , });
                                                                        // back buttons do not need to run validation
                                                                        jQuery("#sf2 .prevbutton").click(function () {
                                                                            accordion.accordion("option", "active", 0);
                                                                            current = 0;
                                                                        });
                                                                        jQuery("#sf3 .prevbutton").click(function () {
                                                                            accordion.accordion("option", "active", 1);
                                                                            current = 1;
                                                                        });
                                                                        jQuery("#sf4 .prevbutton").click(function () {
                                                                            accordion.accordion("option", "active", 1);
                                                                            current = 2;
                                                                        });
                                                                        jQuery("#sf5 .prevbutton").click(function () {
                                                                            accordion.accordion("option", "active", 1);
                                                                            current = 3;
                                                                        });
                                                                        jQuery("#sf6 .prevbutton").click(function () {
                                                                            accordion.accordion("option", "active", 1);
                                                                            current = 4;
                                                                        });
                                                                        jQuery("#sf7 .prevbutton").click(function () {
                                                                            accordion.accordion("option", "active", 1);
                                                                            current = 6;
                                                                        });
                                                                        jQuery("#sf8 .prevbutton").click(function () {
                                                                            accordion.accordion("option", "active", 1);
                                                                            current = 6;
                                                                        });
                                                                        // these buttons all run the validation, overridden by specific targets above
                                                                        jQuery(".open7").click(function () {
                                                                            if (v.form()) {
                                                                                accordion.accordion("option", "active", 6);
                                                                                current = 6;
                                                                            }
                                                                        });
                                                                        jQuery(".open6").click(function () {
                                                                            if (jQuery(this).hasClass('prevbutton')) {
                                                                                if (v.form()) {
                                                                                    accordion.accordion("option", "active", 5);
                                                                                    current = 5;
                                                                                }
                                                                            }
                                                                            else {
                                                                                if (v.form()) {
                                                                                    accordion.accordion("option", "active", 4);
                                                                                    current = 4;
                                                                                }
                                                                            }
                                                                        });
                                                                        jQuery(".open5").click(function () {
                                                                            if (jQuery(this).hasClass('prevbutton')) {
                                                                                if (v.form()) {
                                                                                    accordion.accordion("option", "active", 4);
                                                                                    current = 4;
                                                                                }
                                                                            }
                                                                            else {
                                                                                if (v.form()) {
                                                                                    accordion.accordion("option", "active", 5);
                                                                                    current = 5;
                                                                                }
                                                                            }
                                                                        });
                                                                        jQuery(".open4").click(function () {
                                                                            if (jQuery(this).hasClass('prevbutton')) {
                                                                                if (v.form()) {
                                                                                    accordion.accordion("option", "active", 4);
                                                                                    current = 4;
                                                                                }
                                                                            }
                                                                            else {
                                                                                if (v.form()) {
                                                                                    accordion.accordion("option", "active", 4);
                                                                                    current = 4;
                                                                                }
                                                                            }
                                                                        });
                                                                        jQuery(".open3").click(function () {
                                                                            if (v.form()) {
                                                                                accordion.accordion("option", "active", 3);
                                                                                current = 3;
                                                                            }
                                                                        });
                                                                        jQuery(".open2").click(function () {
                                                                            if (v.form()) {
                                                                                accordion.accordion("option", "active", 2);
                                                                                current = 2;
                                                                            }
                                                                        });
                                                                        jQuery(".open1").click(function () {
                                                                            if (v.form()) {
                                                                                accordion.accordion("option", "active", 1);
                                                                                current = 1;
                                                                            }
                                                                        });
                                                                        jQuery(".open0").click(function () {
                                                                            if (v.form()) {
                                                                                accordion.accordion("option", "active", 0);
                                                                                current = 0;
                                                                            }
                                                                        });
                                                                    })
                                                                </script>
                                                        </div>
                                                        <?php } ?>
                                                            <?php }
    // Get All This Function Content.
    $content = ob_get_contents();

    // Clean
    ob_end_clean();

    return $content;

}











//Account Settings
add_action( 'wp_enqueue_scripts', 'tm_reviews_ajax_data', 99 );
add_action('admin_enqueue_scripts', 'tm_reviews_ajax_data');
function tm_reviews_ajax_data(){
    wp_enqueue_script   ('tmreviews_ajax',  plugin_dir_url( __FILE__ ) .  '/assets/js/tm-reviews-ajax.js', '', '', true);
    wp_localize_script('tmreviews_ajax', 'tm_reviews_ajax',
        array(
            'url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce( 'file_upload' ),
            'security_dlf' => wp_create_nonce( 'file_upload_dlf' ),
            'security_dlb' => wp_create_nonce( 'file_upload_dlb' ),
            'security_prf' => wp_create_nonce( 'file_upload_prf' ),
        )
    );
}


add_action('wp_ajax_tmreviews_send_to_review', 'tmreviews_send_to_review_callback');
add_action('wp_ajax_nopriv_tmreviews_send_to_review', 'tmreviews_send_to_review_callback');
function tmreviews_send_to_review_callback() {
    $current_user_id = get_current_user_ID();

    require_once(ABSPATH . '/wp-load.php');

    $bloginfo = get_bloginfo();

    $admin_email = get_option('admin_email', true);
    $subject = __('Driver License Verification', 'tm-reviews');
    $headers = 'From: '. $bloginfo . ' <' . $admin_email . '>';


    $message = '';
    $message .= '<div>';
    $message .= __('Driver License Verification sended', 'tm-reviews');
    $message .= '</div>';

    //wp_mail("jk_bratkaman@mail.ru", $subject, $message);
    //wp_mail("tm.kazakhstan@yandex.ru", "Subject", "Message");


    update_user_meta($current_user_id, 'tmreviews_dl_sended', 'sended');
    wp_send_json($admin_email);


    wp_die();
}


function tmreviews_account_settings_shortcode() {
    if(is_user_logged_in()){ ?>
                                                                <?php $current_user_id = get_current_user_ID();
        $current_user = get_currentuserinfo(); ?>
                                                                    <div class="tmreviews_account_wrap">
                                                                        <div class="row">
                                                                            <div class="tmreviews_account_wrap_nav col-sm-3 col-xs-12">
                                                                                <nav class="nav_list_stacked js_nav_list_stacked">
                                                                                    <a class="nav_item_link active" data-show="profile_edit">
                                                                                        <?php echo __('Modify your profile', 'tm-reviews');?>
                                                                                    </a>
                                                                                    <a class="nav_item_link " data-show="profile_verify">
                                                                                        <?php echo __('Verify your profile', 'tm-reviews');?>
                                                                                    </a>
                                                                                    <a class="nav_item_link " data-show="account_settings">
                                                                                        <?php echo __('Account settings', 'tm-reviews');?>
                                                                                    </a>
                                                                                    <a class="nav_item_link " href="<?php echo esc_url(wp_logout_url(home_url()))?>">
                                                                                        <?php echo __('Logout', 'tm-reviews');?>
                                                                                    </a>
                                                                                </nav>
                                                                            </div>
                                                                            <div class="tmreviews_account_wrap_content col-sm-9 col-xs-12">
                                                                                <div class="tab_panes_container active" data-show="profile_edit">
                                                                                    <form class="fl_js_profile_form" id="fl_js_profile_form">
                                                                                        <fieldset class="cobalt-Fieldset">
                                                                                            <legend>
                                                                                                <?php echo __('Your photo', 'tm-reviews');?>
                                                                                            </legend>
                                                                                            <div class="cobalt-FormField">
                                                                                                <label class="cobalt-FormField__Label">
                                                                                                    <?php echo __('Photo', 'tm-reviews');?>
                                                                                                </label>
                                                                                                <div class="js_photo_uploader_wrapper photo_uploader_wrapper form_middle avatar_uploader_wrapper" data-action-url="/users/5238423/avatar" data-uploader="avatar">
                                                                                                    <div class="photo_container js_photo_container" style="">
                                                                                                        <div class="picture_wrapper_bordered_thin" id="tm_reviews_avatar">
                                                                                                            <?php echo get_avatar($current_user_id);?>
                                                                                                        </div>
                                                                                                    </div>
                                                                                                    <input type="file" name="avatar" id="tmreviews_file_upload" />
                                                                                                    <input type="hidden" name="avatar_url" id="tmreviews_hidden_file_url" /> </div>
                                                                                            </div>
                                                                                        </fieldset>
                                                                                        <fieldset class="cobalt-Fieldset">
                                                                                            <legend>
                                                                                                <?php echo __('Personal details', 'tm-reviews');?>
                                                                                            </legend>
                                                                                            <div class="cobalt-FormField" data-form-field-method="last_name">
                                                                                                <label class="cobalt-FormField__Label" for="user_last_name">
                                                                                                    <?php echo __('Last name', 'tm-reviews');?>
                                                                                                </label>
                                                                                                <div class="cobalt-TextField">
                                                                                                    <?php $last_name = get_user_meta($current_user_id, 'last_name', true);?>
                                                                                                        <input name="last_name" id="user_last_name" type="text" value="<?php echo isset($last_name) && $last_name!='' ? $last_name : ''?>"> </div>
                                                                                            </div>
                                                                                            <div class="cobalt-FormField" data-form-field-method="first_name">
                                                                                                <label class="cobalt-FormField__Label" for="user_first_name">
                                                                                                    <?php echo __('First name', 'tm-reviews');?>
                                                                                                </label>
                                                                                                <div class="cobalt-TextField">
                                                                                                    <?php $first_name = get_user_meta($current_user_id, 'first_name', true);?>
                                                                                                        <input name="first_name" id="user_first_name" type="text" value="<?php echo isset($first_name) && $first_name!='' ? $first_name : ''?>"> </div>
                                                                                            </div>
                                                                                            <div class="cobalt-FormField" data-form-field-method="birth_date">
                                                                                                <label class="cobalt-FormField__Label" for="user_birth_date">
                                                                                                    <?php echo __('Date of birth', 'tm-reviews');?>
                                                                                                </label>
                                                                                                <div class="cobalt-SelectField cobalt-flexAlign">
                                                                                                    <?php $date_birth = get_user_meta($current_user_id, 'date_birth', true);?>
                                                                                                        <input name="date_birth" id="user_date_birth" type="date" value="<?php echo isset($date_birth) && $date_birth!='' ? $date_birth : ''?>"> </div>
                                                                                            </div>
                                                                                            <div class="cobalt-FormField" data-form-field-method="birth_place">
                                                                                                <label class="cobalt-FormField__Label" for="user_birth_place">
                                                                                                    <?php echo __('Place of birth', 'tm-reviews');?>
                                                                                                </label>
                                                                                                <div class="cobalt-TextField">
                                                                                                    <?php $birth_place = get_user_meta($current_user_id, 'birth_place', true);?>
                                                                                                        <input type="text" name="birth_place" id="user_birth_place" value="<?php echo isset($birth_place) && $birth_place!='' ? $birth_place : ''?>"> </div>
                                                                                            </div>
                                                                                        </fieldset>
                                                                                        <fieldset class="cobalt-Fieldset">
                                                                                            <legend>
                                                                                                <?php echo __('Address', 'tm-reviews');?>
                                                                                            </legend>
                                                                                            <div class="cobalt-FormField" data-form-field-method="billing_address_1">
                                                                                                <label class="cobalt-FormField__Label" for="billing_address_1">
                                                                                                    <?php echo __('Address line 1', 'tm-reviews');?>
                                                                                                </label>
                                                                                                <div class="cobalt-TextField">
                                                                                                    <?php $billing_address_1 = get_user_meta($current_user_id, 'billing_address_1', true);?>
                                                                                                        <input type="text" name="billing_address_1" id="billing_address_1" value="<?php echo isset($billing_address_1) && $billing_address_1!='' ? $billing_address_1 : ''?>" class="regular-text"> </div>
                                                                                            </div>
                                                                                            <div class="cobalt-FormField" data-form-field-method="billing_address_2">
                                                                                                <label class="cobalt-FormField__Label" for="billing_address_2">
                                                                                                    <?php echo __('Address line 2', 'tm-reviews');?>
                                                                                                </label>
                                                                                                <div class="cobalt-TextField">
                                                                                                    <?php $billing_address_2 = get_user_meta($current_user_id, 'billing_address_2', true);?>
                                                                                                        <input type="text" name="billing_address_2" id="billing_address_2" value="<?php echo isset($billing_address_2) && $billing_address_2!='' ? $billing_address_2 : ''?>" class="regular-text"> </div>
                                                                                            </div>
                                                                                            <div class="cobalt-FormField" data-form-field-method="billing_city">
                                                                                                <label class="cobalt-FormField__Label" for="billing_city">
                                                                                                    <?php echo __('City', 'tm-reviews');?>
                                                                                                </label>
                                                                                                <div class="cobalt-TextField">
                                                                                                    <?php $billing_city = get_user_meta($current_user_id, 'billing_city', true);?>
                                                                                                        <input type="text" name="billing_city" id="billing_city" value="<?php echo isset($billing_city) && $billing_city!='' ? $billing_city : ''?>" class="regular-text"> </div>
                                                                                            </div>
                                                                                            <div class="cobalt-FormField" data-form-field-method="billing_postcode">
                                                                                                <label class="cobalt-FormField__Label" for="billing_postcode">
                                                                                                    <?php echo __('Postcode', 'tm-reviews');?>
                                                                                                </label>
                                                                                                <div class="cobalt-TextField">
                                                                                                    <?php $billing_postcode = get_user_meta($current_user_id, 'billing_postcode', true);?>
                                                                                                        <input type="text" name="billing_postcode" id="billing_postcode" value="<?php echo isset($billing_postcode) && $billing_postcode!='' ? $billing_postcode : ''?>" class="regular-text"> </div>
                                                                                            </div>
                                                                                            <fieldset class="cobalt-Fieldset">
                                                                                                <legend>
                                                                                                    <?php echo __('Additional information', 'tm-reviews');?>
                                                                                                </legend>
                                                                                                <div class="cobalt-FormField cobalt-FormField--withHint" data-form-field-method="about_me" data-form-field-hint-status="hint">
                                                                                                    <label class="cobalt-FormField__Label" for="user_about_me">
                                                                                                        <?php echo __('About me', 'tm-reviews');?>
                                                                                                    </label>
                                                                                                    <div class="cobalt-TextAreaField">
                                                                                                        <?php $description = get_user_meta($current_user_id, 'description', true);?>
                                                                                                            <textarea maxlength="2000" class="cobalt-TextAreaField__Input" name="description" id="user_description">
                                                                                                                <?php echo esc_attr($description)?>
                                                                                                            </textarea>
                                                                                                    </div>
                                                                                                    <div class="cobalt-Hint"> <span class="cobalt-Hint__Icon">
                                                <span class="cobalt-Icon cobalt-Icon--colorSubdued cobalt-Icon--size16">
                                                </span> </span> <span class="cobalt-Hint__Message"><?php echo __('This information will help the owner get to know you better. The more details you provide, the better your chances of hiring a vehicle.', 'tm-reviews');?></span> </div>
                                                                                                </div>
                                                                                            </fieldset>
                                                                                        </fieldset>
                                                                                        <fieldset class="cobalt-Fieldset">
                                                                                            <a id="tmreviews_update_profile">
                                                                                                <?php echo __('Update Profile', 'tm-reviews');?>
                                                                                            </a>
                                                                                        </fieldset>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="tab_panes_container" data-show="profile_verify">
                                                                                    <form class="fl_js_profile_form" id="fl_js_profile_form">
                                                                                        <fieldset class="cobalt-Fieldset">
                                                                                            <legend>
                                                                                                <?php echo __('Verification Center', 'tm-reviews');?>
                                                                                            </legend>
                                                                                            <?php $tmreviews_dl_sended = get_user_meta($current_user_id, 'tmreviews_dl_sended', true);?>
                                                                                                <?php if(isset($tmreviews_dl_sended) && $tmreviews_dl_sended == 'sended'){ ?>
                                                                                                    <div class="alert alert-warning" role="alert">
                                                                                                        <?php echo __('You can upload your verification documents here.', 'tm-reviews');?>
                                                                                                    </div>
                                                                                                    <?php } elseif(isset($tmreviews_dl_sended) && $tmreviews_dl_sended == 'approved') { ?>
                                                                                                        <div class="alert alert-success" role="alert">
                                                                                                            <?php echo __('Your verification has been successfully confirmed. Congratulations!', 'tm-reviews');?>
                                                                                                        </div>
                                                                                                        <?php } else { ?>
                                                                                                            <div class="alert alert-warning" role="alert">
                                                                                                                <?php echo __('You can upload your verification documents here.', 'tm-reviews');?>
                                                                                                            </div>
                                                                                                            <?php } ?>
                                                                                                                <div class="cobalt-FormField">
                                                                                                                    <label class="cobalt-FormField__Label">
                                                                                                                        <?php echo __("Document One", 'tm-reviews');?>
                                                                                                                    </label>
                                                                                                                    <?php $tmreviews_dlf = get_user_meta($current_user_id, 'tmreviews_dlf', true);?>
                                                                                                                        <div class="js_photo_uploader_wrapper photo_uploader_wrapper form_middle avatar_uploader_wrapper" data-uploader="avatar">
                                                                                                                            <?php if(isset($tmreviews_dl_sended) && $tmreviews_dl_sended != 'approved'){ ?>
                                                                                                                                <input type="file" name="driver_license_front" id="tmreviews_hidden_dlf_url" />
                                                                                                                                <?php } ?>
                                                                                                                                    <?php if(isset($tmreviews_dlf) && $tmreviews_dlf != ''){ ?> <img id="tmreviews_dlf" src="<?php echo wp_get_attachment_image_url($tmreviews_dlf, 'full');?>">
                                                                                                                                        <?php } else { ?> <img id="tmreviews_dlf">
                                                                                                                                            <?php } ?>
                                                                                                                        </div>
                                                                                                                </div>
                                                                                                                <div class="cobalt-FormField">
                                                                                                                    <label class="cobalt-FormField__Label">
                                                                                                                        <?php echo __("Document Two", 'tm-reviews');?>
                                                                                                                    </label>
                                                                                                                    <?php $tmreviews_dlb = get_user_meta($current_user_id, 'tmreviews_dlb', true);?>
                                                                                                                        <div class="js_photo_uploader_wrapper photo_uploader_wrapper form_middle avatar_uploader_wrapper" data-uploader="avatar">
                                                                                                                            <?php if(isset($tmreviews_dl_sended) && $tmreviews_dl_sended != 'approved'){ ?>
                                                                                                                                <input type="file" name="driver_license_back" id="tmreviews_hidden_dlb_url" />
                                                                                                                                <?php } ?>
                                                                                                                                    <?php if(isset($tmreviews_dlb) && $tmreviews_dlb != ''){ ?> <img id="tmreviews_dlb" src="<?php echo wp_get_attachment_image_url($tmreviews_dlb, 'full');?>">
                                                                                                                                        <?php } else { ?> <img id="tmreviews_dlb">
                                                                                                                                            <?php } ?>
                                                                                                                        </div>
                                                                                                                </div>
                                                                                                                <div class="cobalt-FormField">
                                                                                                                    <label class="cobalt-FormField__Label">
                                                                                                                        <?php echo __("Document Three", 'tm-reviews');?>
                                                                                                                    </label>
                                                                                                                    <?php $tmreviews_prf = get_user_meta($current_user_id, 'tmreviews_prf', true);?>
                                                                                                                        <div class="js_photo_uploader_wrapper photo_uploader_wrapper form_middle avatar_uploader_wrapper" data-uploader="avatar">
                                                                                                                            <?php if(isset($tmreviews_dl_sended) && $tmreviews_dl_sended != 'approved'){ ?>
                                                                                                                                <input type="file" name="tmreviews_prf" id="tmreviews_hidden_prf_url" />
                                                                                                                                <?php } ?>
                                                                                                                                    <?php if(isset($tmreviews_prf) && $tmreviews_prf != ''){ ?> <img id="tmreviews_prf" src="<?php echo wp_get_attachment_image_url($tmreviews_prf, 'full');?>">
                                                                                                                                        <?php } else { ?> <img id="tmreviews_prf">
                                                                                                                                            <?php } ?>
                                                                                                                        </div>
                                                                                                                </div>
                                                                                        </fieldset>
                                                                                        <?php if(isset($tmreviews_dl_sended) && $tmreviews_dl_sended == 'sended'){ ?>
                                                                                            <fieldset class="cobalt-Fieldset"> <span class="tmreviews_send"><?php echo __('Sended to review', 'tm-reviews');?></span> </fieldset>
                                                                                            <?php } ?>
                                                                                                <?php if(!isset($tmreviews_dl_sended) || $tmreviews_dl_sended == 'rejected' || $tmreviews_dl_sended == ''){?>
                                                                                                    <fieldset class="cobalt-Fieldset">
                                                                                                        <a class="tmreviews_send" id="tmreviews_send_to_review">
                                                                                                            <?php echo __('Send to Review', 'tm-reviews');?>
                                                                                                        </a>
                                                                                                    </fieldset>
                                                                                                    <?php } ?>
                                                                                    </form>
                                                                                </div>
                                                                                <div class="tab_panes_container" data-show="account_settings">
                                                                                    <fieldset class="cobalt-Fieldset">
                                                                                        <legend>
                                                                                            <?php echo __('Account settings', 'tm-reviews');?>
                                                                                        </legend>
                                                                                        <?php echo do_shortcode('[tmreviews_password_form]');?>
                                                                                    </fieldset>
                                                                                </div>
                                                                                <div class="tab_panes_container" data-show="notifications"> notifications </div>
                                                                                <div class="tab_panes_container" data-show="payment_methods"> payment_methods </div>
                                                                                <div class="tab_panes_container" data-show="credit"> credit </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <script>
                                                                        jQuery(document).ready(function ($) {
                                                                            jQuery('.nav_list_stacked .nav_item_link').click(function (e) {
                                                                                jQuery('.nav_item_link').removeClass('active');
                                                                                jQuery(this).addClass('active');
                                                                                jQuery(".tmreviews_account_wrap_content .tab_panes_container").removeClass('active');
                                                                                jQuery(".tmreviews_account_wrap_content").find("[data-show='" + jQuery(this).data('show') + "']").addClass('active');
                                                                            });
                                                                            jQuery('.nav_item_link.modify').click(function (e) {
                                                                                jQuery('.nav_item_link').removeClass('active');
                                                                                jQuery("[data-show='account_settings']").addClass('active');
                                                                                jQuery(".tmreviews_account_wrap_content .tab_panes_container").removeClass('active');
                                                                                jQuery(".tmreviews_account_wrap_content").find("[data-show='" + jQuery(this).data('show') + "']").addClass('active');
                                                                            });
                                                                        });
                                                                    </script>
                                                                    <?php }

}
add_shortcode('tmreviews_account_settings', 'tmreviews_account_settings_shortcode');



function tmreviews_change_password_form() {
    global $post;
    if (is_singular()) :
        $current_url = get_permalink($post->ID);
    else :
        $pageURL = 'http';
        if ($_SERVER["HTTPS"] == "on") $pageURL .= "s";
        $pageURL .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
        else $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
        $current_url = $pageURL;
    endif;
    $redirect = $current_url;

    ob_start();

    $current_user_id = get_current_user_ID();
    $current_user = get_currentuserinfo();

    tmreviews_show_error_messages(); ?>
                                                                        <?php if(isset($_GET['password-reset']) && $_GET['password-reset'] == 'true') { ?>
                                                                            <div class="tmreviews_message success"> <span><?php _e('Password changed successfully', 'rcp'); ?></span> </div>
                                                                            <?php } ?>
                                                                                <?php if(isset($_GET['email-reset']) && $_GET['email-reset'] == 'true') { ?>
                                                                                    <div class="tmreviews_message success"> <span><?php _e('Email changed successfully', 'rcp'); ?></span> </div>
                                                                                    <?php } ?>
                                                                                        <form id="tmreviews_password_form" method="POST" action="<?php echo $current_url; ?>">
                                                                                            <fieldset>
                                                                                                <p>
                                                                                                    <label for="email">
                                                                                                        <?php _e( 'Account Email', 'tm-reviews' ); ?>
                                                                                                    </label>
                                                                                                    <input type="text" name="tmreviews_user_email" id="email" value="<?php echo $current_user->user_email; ?>" class="settings-input" /> </p>
                                                                                                <hr>
                                                                                                <div class="fl-change-pass">
                                                                                                    <label for="pass1">
                                                                                                        <?php _e('Change Password (leave blank for no change)', 'tmreviews'); ?>
                                                                                                    </label>
                                                                                                    <p>
                                                                                                        <input name="tmreviews_user_pass" id="tmreviews_user_pass" class="required" type="password" placeholder="<?php _e('New Password', 'rcp'); ?>" /> </p>
                                                                                                    <p>
                                                                                                        <input name="tmreviews_user_pass_confirm" id="tmreviews_user_pass_confirm" class="required" type="password" placeholder="<?php _e('Password Confirm', 'rcp'); ?>" /> </p>
                                                                                                </div>
                                                                                                <p>
                                                                                                    <input type="hidden" name="tmreviews_action" value="reset-password" />
                                                                                                    <input type="hidden" name="tmreviews_redirect" value="<?php echo $redirect; ?>" />
                                                                                                    <input type="hidden" name="tmreviews_password_nonce" value="<?php echo wp_create_nonce('rcp-password-nonce'); ?>" />
                                                                                                    <input id="tmreviews_password_submit" type="submit" value="<?php _e('Change Password', 'tmreviews'); ?>" /> </p>
                                                                                            </fieldset>
                                                                                        </form>
                                                                                        <?php
    return ob_get_clean();
}
function tmreviews_reset_password_form() {
    if(is_user_logged_in()) {
        return tmreviews_change_password_form();
    }
}
add_shortcode('tmreviews_password_form', 'tmreviews_reset_password_form');
function tmreviews_reset_password() {



    if(isset($_POST['tmreviews_action']) && $_POST['tmreviews_action'] == 'reset-password') {
        global $user_ID;
        $user_id = get_current_user_ID();
        $userdata = get_user_by( 'ID', $user_id );


        if(!is_user_logged_in())
            return;

        if(wp_verify_nonce($_POST['tmreviews_password_nonce'], 'rcp-password-nonce')) {

            if($_POST['tmreviews_user_pass'] == '' || $_POST['tmreviews_user_pass_confirm'] == '') {
                if($_POST['tmreviews_user_email'] == $userdata->user_email){
                    tmreviews_errors()->add('password_empty', __('Please enter a password, and confirm it', 'tmreviews'));
                }
            } else {
                $args = add_query_arg('password-reset', 'true', $_POST['tmreviews_redirect']);
            }

            if($_POST['tmreviews_user_pass'] != $_POST['tmreviews_user_pass_confirm']) {
                if($_POST['tmreviews_user_email'] == $userdata->user_email) {
                    tmreviews_errors()->add('password_mismatch', __('Passwords do not match', 'tmreviews'));
                }
            } else {
                $args = add_query_arg('password-reset', 'true', $_POST['tmreviews_redirect']);
            }

            if(isset($_POST['tmreviews_user_email']) && $_POST['tmreviews_user_email'] != $userdata->user_email){
                $args = add_query_arg('email-reset', 'true', $_POST['tmreviews_redirect']);
            }


            $errors = tmreviews_errors()->get_error_messages();

            if(empty($errors)) {
                $user_data = array(
                    'ID' => $user_ID,
                    'user_pass' => $_POST['tmreviews_user_pass'],
                    'user_email' => esc_attr( $_POST['tmreviews_user_email'] )
                );
                wp_update_user($user_data);
                wp_redirect($args);
                exit;
            }
        }
    }
}
add_action('init', 'tmreviews_reset_password');
if(!function_exists('tmreviews_show_error_messages')) {
    // displays error messages from form submissions
    function tmreviews_show_error_messages() {
        if($codes = tmreviews_errors()->get_error_codes()) {
            echo '<div class="tmreviews_message error">';
            // Loop error codes and display errors
            foreach($codes as $code){
                $message = tmreviews_errors()->get_error_message($code);
                echo '<span class="tmreviews_error"><strong>' . __('Error', 'rcp') . '</strong>: ' . $message . '</span><br/>';
            }
            echo '</div>';
        }
    }
}
if(!function_exists('tmreviews_errors')) {
    function tmreviews_errors(){
        static $wp_error;
        return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
    }
}



//Login
function tmreviews_login_shortcode() {
    if(class_exists('Youzify')){?>
                                                                                            <div class="templines_login_wrap">
                                                                                                <?php echo do_shortcode('[youzify_login]');?>
                                                                                            </div>
                                                                                            <div class="templines_lostpass_wrap close">
                                                                                                <?php echo do_shortcode('[youzify_lost_password]');?>
                                                                                            </div>
                                                                                            <div class="templines_registr_wrap close">
                                                                                                <?php echo do_shortcode('[youzify_register]');?>
                                                                                            </div>
                                                                                            <script>
                                                                                                jQuery.noConflict()(function ($) {
                                                                                                    jQuery('.templines_login_wrap .youzify-membership-forgot-password').click(function (e) {
                                                                                                        e.preventDefault();
                                                                                                        jQuery('.templines_lostpass_wrap').removeClass('close');
                                                                                                        jQuery('.templines_lostpass_wrap').addClass('open');
                                                                                                        jQuery('.templines_login_wrap').removeClass('open');
                                                                                                        jQuery('.templines_login_wrap').addClass('close');
                                                                                                    });
                                                                                                    jQuery('.templines_lostpass_wrap  .youzify-membership-link-button').click(function (e) {
                                                                                                        e.preventDefault();
                                                                                                        jQuery('.templines_login_wrap').removeClass('close');
                                                                                                        jQuery('.templines_login_wrap').addClass('open');
                                                                                                        jQuery('.templines_lostpass_wrap').removeClass('open');
                                                                                                        jQuery('.templines_lostpass_wrap').addClass('close');
                                                                                                    });
                                                                                                    jQuery('.templines_login_wrap .youzify-membership-link-button').click(function (e) {
                                                                                                        e.preventDefault();
                                                                                                        jQuery('.templines_registr_wrap ').removeClass('close');
                                                                                                        jQuery('.templines_registr_wrap ').addClass('open');
                                                                                                        jQuery('.templines_login_wrap').removeClass('open');
                                                                                                        jQuery('.templines_login_wrap').addClass('close');
                                                                                                    });
                                                                                                    jQuery('.templines_registr_wrap .youzify-membership-link-button').click(function (e) {
                                                                                                        e.preventDefault();
                                                                                                        jQuery('.templines_login_wrap').removeClass('close');
                                                                                                        jQuery('.templines_login_wrap').addClass('open');
                                                                                                        jQuery('.templines_registr_wrap').removeClass('open');
                                                                                                        jQuery('.templines_registr_wrap').addClass('close');
                                                                                                    });
                                                                                                });
                                                                                            </script>
                                                                                            <?php }
}
add_shortcode('tmreviews_login', 'tmreviews_login_shortcode');

//Verify Account Fields
add_action('youzify_profile_settings', 'templines_helper_profile_settings', 100);
function templines_helper_profile_settings(){
    ?>
                                                                                                <form id="youzify-profile-picture" method="post" class="youzify-settings-form" enctype="multipart/form-data">
                                                                                                    <?php
        $user = wp_get_current_user();
        $phone = get_user_meta( $user->ID, 'phone', true );
        if(!isset($phone) && $phone == ''){
            $phone = '';
        }

        $instagram = get_user_meta( $user->ID, 'instagram', true );
        if(!isset($instagram) && $instagram == ''){
            $instagram = '';
        }

        $facebook = get_user_meta( $user->ID, 'facebook', true );
        if(!isset($facebook) && $facebook == ''){
            $facebook = '';
        }

        $google = get_user_meta( $user->ID, 'google', true );
        if(!isset($google) && $google == ''){
            $google = '';
        }

        $twitter = get_user_meta( $user->ID, 'twitter', true );
        if(!isset($twitter) && $twitter == ''){
            $twitter = '';
        }

        $behance = get_user_meta( $user->ID, 'behance', true );
        if(!isset($behance) && $behance == ''){
            $behance = '';
        }


        ?>
                                                                                                        <div class="youzify-section-content youzify-no-widgets">
                                                                                                            <input type="hidden" name="action" value="fl_themes_profile_settings_save_meta">
                                                                                                            <div id="youzify_field_user_phone" class="editfield user_phone">
                                                                                                                <fieldset>
                                                                                                                    <legend id="user_phone-1">
                                                                                                                        <?php echo __('Phone', 'fl-themes-helper')?>
                                                                                                                    </legend>
                                                                                                                    <input id="user_phone" name="user_phone" type="text" value="<?php echo esc_attr($phone);?>"> </fieldset>
                                                                                                            </div>
                                                                                                            <div id="youzify_field_user_socials" class="editfield user_socials">
                                                                                                                <fieldset>
                                                                                                                    <legend id="user_instagram-1">
                                                                                                                        <?php echo __('Instagram', 'fl-themes-helper')?>
                                                                                                                    </legend>
                                                                                                                    <input id="user_instagram" name="user_instagram" type="text" value="<?php echo esc_attr($instagram);?>"> </fieldset>
                                                                                                                <fieldset>
                                                                                                                    <legend id="user_facebook-1">
                                                                                                                        <?php echo __('facebook', 'fl-themes-helper')?>
                                                                                                                    </legend>
                                                                                                                    <input id="user_facebook" name="user_facebook" type="text" value="<?php echo esc_attr($facebook);?>"> </fieldset>
                                                                                                                <fieldset>
                                                                                                                    <legend id="user_google-1">
                                                                                                                        <?php echo __('google', 'fl-themes-helper')?>
                                                                                                                    </legend>
                                                                                                                    <input id="user_google" name="user_google" type="text" value="<?php echo esc_attr($google);?>"> </fieldset>
                                                                                                                <fieldset>
                                                                                                                    <legend id="user_twitter-1">
                                                                                                                        <?php echo __('twitter', 'fl-themes-helper')?>
                                                                                                                    </legend>
                                                                                                                    <input id="user_twitter" name="user_twitter" type="text" value="<?php echo esc_attr($twitter);?>"> </fieldset>
                                                                                                                <fieldset>
                                                                                                                    <legend id="user_behance-1">
                                                                                                                        <?php echo __('behance', 'fl-themes-helper')?>
                                                                                                                    </legend>
                                                                                                                    <input id="user_behance" name="user_behance" type="text" value="<?php echo esc_attr($behance);?>"> </fieldset>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="youzify-settings-actions">
                                                                                                            <button name="save" class="youzify-save-options" type="submit">
                                                                                                                <?php echo __('Save', 'fl-themes-helper')?>
                                                                                                            </button>
                                                                                                        </div>
                                                                                                        <?php

        if ( isset( $_POST['action'] ) && 'fl_themes_profile_settings_save_meta' == $_POST['action'] ) {

            update_user_meta( $user->ID, 'phone', $_POST['user_phone'] );
            update_user_meta( $user->ID, 'instagram', $_POST['user_instagram'] );
            update_user_meta( $user->ID, 'facebook', $_POST['user_facebook'] );
            update_user_meta( $user->ID, 'google', $_POST['user_google'] );
            update_user_meta( $user->ID, 'twitter', $_POST['user_twitter'] );
            update_user_meta( $user->ID, 'behance', $_POST['user_behance'] );


            // Get Reidrect page.
            $redirect_to = ! empty( $redirect_to ) ? $redirect_to : youzify_get_current_page_url();

            // Redirect User.
            echo("<script>location.href = '".$redirect_to."'</script>");
        }
        ?>
                                                                                                </form>
                                                                                                <?php
}



function tmreviews_user_fields( $user ) {
    $tmreviews_dl_sended = get_user_meta($user->ID, 'tmreviews_dl_sended', true);
    ?>
                                                                                                    <div id="tmreviews_verification_wrap">
                                                                                                        <?php
        $tmreviews_dlf = get_usermeta($user->ID, 'tmreviews_dlf', true);
        $tmreviews_dlb = get_usermeta($user->ID, 'tmreviews_dlb', true);
        $tmreviews_prf = get_usermeta($user->ID, 'tmreviews_prf', true);

        ?>
                                                                                                            <?php if(isset($tmreviews_dlf) && $tmreviews_dlf != ''){?> <a href="<?php echo wp_get_attachment_image_url($tmreviews_dlf, 'full');?>"><img id="tmreviews_dlf" src="<?php echo wp_get_attachment_image_url($tmreviews_dlf);?>"></a>
                                                                                                                <?php } ?>
                                                                                                                    <?php if(isset($tmreviews_dlb) && $tmreviews_dlb != ''){?> <a href="<?php echo wp_get_attachment_image_url($tmreviews_dlb, 'full');?>"><img id="tmreviews_dlb" src="<?php echo wp_get_attachment_image_url($tmreviews_dlb);?>"></a>
                                                                                                                        <?php } ?>
                                                                                                                            <?php if(isset($tmreviews_prf) && $tmreviews_prf != ''){?> <a href="<?php echo wp_get_attachment_image_url($tmreviews_prf, 'full');?>"><img id="tmreviews_prf" src="<?php echo wp_get_attachment_image_url($tmreviews_prf);?>"></a>
                                                                                                                                <?php } ?>
                                                                                                                                    <select name="tmreviews_dl_sended">
                                                                                                                                        <option value="sended" <?php echo $tmreviews_dl_sended=="sended" ? esc_attr( "selected") : ""?>>
                                                                                                                                            <?php echo __('Sended', 'tm-reviews');?>
                                                                                                                                        </option>
                                                                                                                                        <option value="approved" <?php echo $tmreviews_dl_sended=="approved" ? esc_attr( "selected") : ""?>>
                                                                                                                                            <?php echo __('Approved', 'tm-reviews');?>
                                                                                                                                        </option>
                                                                                                                                        <option value="rejected" <?php echo $tmreviews_dl_sended=="rejected" ? esc_attr( "selected") : ""?>>
                                                                                                                                            <?php echo __('Rejected', 'tm-reviews');?>
                                                                                                                                        </option>
                                                                                                                                    </select>
                                                                                                    </div>
                                                                                                    <?php
}
add_action( 'show_user_profile', 'tmreviews_user_fields', 1, 1 );
add_action( 'edit_user_profile', 'tmreviews_user_fields', 1, 1 );




function tmreviews_dl_sended($userId) {
    if (!current_user_can('edit_user', $userId)) {
        return;
    }
    update_user_meta($userId, 'tmreviews_dl_sended', $_REQUEST['tmreviews_dl_sended']);
}
add_action('personal_options_update', 'tmreviews_dl_sended');
add_action('edit_user_profile_update', 'tmreviews_dl_sended');
add_action('user_register', 'tmreviews_dl_sended');





add_action('wp_ajax_tmreviews_approve_verification', 'tmreviews_approve_verification_callback');
add_action('wp_ajax_nopriv_tmreviews_approve_verification', 'tmreviews_approve_verification_callback');
function tmreviews_approve_verification_callback() {

    $u = new WP_User( $_REQUEST['user_id'] );
    $u->remove_role( 'subscriber' );
    $u->add_role( 'verified' );

    update_user_meta($_REQUEST['user_id'], 'tmreviews_dl_sended', 'approved');

    wp_send_json('changed');
    wp_die();
}

function tmreviews_update_extra_profile_fields($user_id) {
    if ( current_user_can('edit_user', $user_id) ){
        if($_POST['role'] == 'verified'){
            update_user_meta($user_id, 'tmreviews_dl_sended', 'approved');
        } else {
            update_user_meta($user_id, 'tmreviews_dl_sended', 'rejected');
        }
    }
}
add_action('edit_user_profile_update', 'tmreviews_update_extra_profile_fields');

add_action('wp_ajax_file_upload', 'file_upload_callback');
add_action('wp_ajax_nopriv_file_upload', 'file_upload_callback');
function file_upload_callback() {
    check_ajax_referer('file_upload', 'security');
    $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');

    if (in_array($_FILES['file']['type'], $arr_img_ext)) {

        add_filter( 'upload_dir', 'tmreviews_org_logos_upload_dir' );

        tm_reviews_clear_avatars();
        $type = array_pop(explode('.', ($_FILES['file']['name'])));
        $name_full = rand('1000000000',  '9999999999') . '-bpfull.' . $type;
        $name_thumb = rand('1000000000',  '9999999999') . '-bpthumb.' . $type;

        $upload_full = wp_upload_bits($name_full, null, file_get_contents($_FILES["file"]["tmp_name"]));
        $upload_thumb = wp_upload_bits($name_thumb, null, file_get_contents($_FILES["file"]["tmp_name"]));

        wp_send_json($upload_full);
        remove_filter( 'upload_dir', 'tmreviews_org_logos_upload_dir' );

    }

    wp_die();
}

add_action('wp_ajax_file_upload_dlf', 'file_upload_dlf_callback');
add_action('wp_ajax_nopriv_file_upload_dlf', 'file_upload_dlf_callback');
function file_upload_dlf_callback() {
    check_ajax_referer('file_upload_dlf', 'security_dlf');
    $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
    $current_user_id = get_current_user_ID();
    if (in_array($_FILES['file']['type'], $arr_img_ext)) {

        $upload = wp_upload_bits($_FILES['file']['name'], null, file_get_contents($_FILES["file"]["tmp_name"]));
        $attachment_id = tmreviews_rudr_upload_file_by_url($upload['url']);
        update_user_meta($current_user_id, 'tmreviews_dlf', $attachment_id);
        wp_send_json($upload['url']);
    }

    wp_die();
}

add_action('wp_ajax_file_upload_dlb', 'file_upload_dlb_callback');
add_action('wp_ajax_nopriv_file_upload_dlb', 'file_upload_dlb_callback');
function file_upload_dlb_callback() {
    check_ajax_referer('file_upload_dlb', 'security_dlb');
    $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
    $current_user_id = get_current_user_ID();
    if (in_array($_FILES['file']['type'], $arr_img_ext)) {

        $upload = wp_upload_bits($_FILES['file']['name'], null, file_get_contents($_FILES["file"]["tmp_name"]));
        $attachment_id = tmreviews_rudr_upload_file_by_url($upload['url']);
        update_user_meta($current_user_id, 'tmreviews_dlb', $attachment_id);

        wp_send_json($upload['url']);
    }

    wp_die();
}

add_action('wp_ajax_file_upload_prf', 'file_upload_prf_callback');
add_action('wp_ajax_nopriv_file_upload_prf', 'file_upload_prf_callback');
function file_upload_prf_callback() {
    check_ajax_referer('file_upload_prf', 'security_prf');
    $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
    $current_user_id = get_current_user_ID();
    if (in_array($_FILES['file']['type'], $arr_img_ext)) {

        $upload = wp_upload_bits($_FILES['file']['name'], null, file_get_contents($_FILES["file"]["tmp_name"]));
        $attachment_id = tmreviews_rudr_upload_file_by_url($upload['url']);
        update_user_meta($current_user_id, 'tmreviews_prf', $attachment_id);

        wp_send_json($upload['url']);
    }

    wp_die();
}

function tmreviews_rudr_upload_file_by_url( $image_url ) {

    // it allows us to use download_url() and wp_handle_sideload() functions
    require_once( ABSPATH . 'wp-admin/includes/file.php' );

    // download to temp dir
    $temp_file = download_url( $image_url );

    if( is_wp_error( $temp_file ) ) {
        return false;
    }

    // move the temp file into the uploads directory
    $file = array(
        'name'     => basename( $image_url ),
        'type'     => mime_content_type( $temp_file ),
        'tmp_name' => $temp_file,
        'size'     => filesize( $temp_file ),
    );
    $sideload = wp_handle_sideload(
        $file,
        array(
            'test_form'   => false // no needs to check 'action' parameter
        )
    );

    if( ! empty( $sideload[ 'error' ] ) ) {
        // you may return error message if you want
        return false;
    }

    // it is time to add our uploaded image into WordPress media library
    $attachment_id = wp_insert_attachment(
        array(
            'guid'           => $sideload[ 'url' ],
            'post_mime_type' => $sideload[ 'type' ],
            'post_title'     => basename( $sideload[ 'file' ] ),
            'post_content'   => '',
            'post_status'    => 'inherit',
        ),
        $sideload[ 'file' ]
    );

    if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
        return false;
    }

    // update medatata, regenerate image sizes
    require_once( ABSPATH . 'wp-admin/includes/image.php' );

    wp_update_attachment_metadata(
        $attachment_id,
        wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
    );

    return $attachment_id;

}

function tm_reviews_clear_avatars(){
    $uploads = wp_get_upload_dir();
    $dir = $uploads['basedir'] . '/avatars/'. get_current_user_ID() . '/*.*';
    array_map('unlink', glob($dir));
}

function tmreviews_org_logos_upload_dir( $arr ) {

    $folder = '/avatars/' . get_current_user_ID();

    $arr['path'] .= $folder;
    $arr['url'] .= $folder;
    $arr['subdir'] .= $folder;

    $arr['path'] = tmreviews_replace_all_text_between($arr['path'],'/uploads', '/avatars', '');
    $arr['url'] = tmreviews_replace_all_text_between($arr['url'],'/uploads', '/avatars', '');
    $arr['subdir'] = tmreviews_replace_all_text_between($arr['subdir'],'/uploads', '/avatars', '');

    return $arr;
}

function tmreviews_replace_all_text_between($str, $start, $end, $replacement) {

    $replacement = $start . $replacement . $end;

    $start = preg_quote($start, '/');
    $end = preg_quote($end, '/');
    $regex = "/({$start})(.*?)({$end})/";

    return preg_replace($regex,$replacement,$str);
}


add_action('wp_footer', 'tm_reviews_change_query_javascript', 99);
function tm_reviews_change_query_javascript() {
    $tmreviews_place_icon_all_import = get_option('tmreviews_place_icon_all_import', true);
    $gmap_api_key = get_option('tmreviews_google_maps_key', true);
    ?>
                                                                                                        <script>
                                                                                                            jQuery(document).ready(function ($) {
                                                                                                                //Update Profile
                                                                                                                function getFormData($form) {
                                                                                                                    var unindexed_array = $form.serializeArray();
                                                                                                                    var indexed_array = {};
                                                                                                                    var extra_ids_val = "";
                                                                                                                    $.map(unindexed_array, function (n, i) {
                                                                                                                        indexed_array[n['name']] = n['value'];
                                                                                                                        if (n["name"] === "extra[]") {
                                                                                                                            extra_ids_val += n["value"] + ",";
                                                                                                                        }
                                                                                                                        indexed_array["extra_ids"] = extra_ids_val.slice(0, -1);
                                                                                                                    });
                                                                                                                    return indexed_array;
                                                                                                                }
                                                                                                                jQuery('#tmreviews_update_profile').click(function (e) {
                                                                                                                    e.preventDefault();
                                                                                                                    jQuery(this).parents('form.fl_js_profile_form').addClass('ajax-loading');
                                                                                                                    var form_data = getFormData(jQuery(this).parents('form.fl_js_profile_form'));
                                                                                                                    var data = {
                                                                                                                        action: 'tmreviews_profile_update'
                                                                                                                        , form_data: form_data
                                                                                                                    };
                                                                                                                    jQuery.post(tm_reviews_ajax.url, data, function (response) {
                                                                                                                        //console.log(response);
                                                                                                                        jQuery('form.fl_js_profile_form').removeClass('ajax-loading');
                                                                                                                        jQuery('#tmreviews_update_profile').html('<?php echo __("Saved", "tm-reviews");?>');
                                                                                                                    });
                                                                                                                });
                                                                                                                jQuery('#fl_js_profile_form').on('change', function (e) {
                                                                                                                    jQuery('#tmreviews_update_profile').html('<?php echo __("Update Profile", "tm-reviews");?>');
                                                                                                                });
                                                                                                                jQuery('#tmreviews_send_to_review').click(function (e) {
                                                                                                                    e.preventDefault();
                                                                                                                    var data = {
                                                                                                                        action: 'tmreviews_send_to_review'
                                                                                                                    , };
                                                                                                                    jQuery.post(tm_reviews_ajax.url, data, function (response) {
                                                                                                                        location.reload();
                                                                                                                    });
                                                                                                                });
                                                                                                                jQuery('#tmreviews_file_upload').on('change', function () {
                                                                                                                    $this = jQuery(this);
                                                                                                                    file_data = jQuery(this).prop('files')[0];
                                                                                                                    form_data = new FormData();
                                                                                                                    form_data.append('file', file_data);
                                                                                                                    form_data.append('action', 'file_upload');
                                                                                                                    form_data.append('security', tm_reviews_ajax.security);
                                                                                                                    jQuery.ajax({
                                                                                                                        url: tm_reviews_ajax.url
                                                                                                                        , type: 'POST'
                                                                                                                        , contentType: false
                                                                                                                        , processData: false
                                                                                                                        , data: form_data
                                                                                                                        , success: function (response) {
                                                                                                                            jQuery('#tm_reviews_avatar img').attr("src", response['url']);
                                                                                                                            jQuery('#tm_reviews_avatar img').attr("srcset", response['url']);
                                                                                                                            jQuery('.tmreviews_user_avatar').attr("src", response['url']);
                                                                                                                        }
                                                                                                                    });
                                                                                                                });
                                                                                                                jQuery('#tmreviews_hidden_dlf_url').on('change', function () {
                                                                                                                    $this = jQuery(this);
                                                                                                                    file_data = jQuery(this).prop('files')[0];
                                                                                                                    form_data = new FormData();
                                                                                                                    form_data.append('file', file_data);
                                                                                                                    form_data.append('action', 'file_upload_dlf');
                                                                                                                    form_data.append('security_dlf', tm_reviews_ajax.security_dlf);
                                                                                                                    jQuery.ajax({
                                                                                                                        url: tm_reviews_ajax.url
                                                                                                                        , type: 'POST'
                                                                                                                        , contentType: false
                                                                                                                        , processData: false
                                                                                                                        , data: form_data
                                                                                                                        , success: function (response) {
                                                                                                                            jQuery('#tmreviews_dlf').attr("src", response);
                                                                                                                        }
                                                                                                                    });
                                                                                                                });
                                                                                                                jQuery('#tmreviews_hidden_dlb_url').on('change', function () {
                                                                                                                    $this = jQuery(this);
                                                                                                                    file_data = jQuery(this).prop('files')[0];
                                                                                                                    form_data = new FormData();
                                                                                                                    form_data.append('file', file_data);
                                                                                                                    form_data.append('action', 'file_upload_dlb');
                                                                                                                    form_data.append('security_dlb', tm_reviews_ajax.security_dlb);
                                                                                                                    jQuery.ajax({
                                                                                                                        url: tm_reviews_ajax.url
                                                                                                                        , type: 'POST'
                                                                                                                        , contentType: false
                                                                                                                        , processData: false
                                                                                                                        , data: form_data
                                                                                                                        , success: function (response) {
                                                                                                                            jQuery('#tmreviews_dlb').attr("src", response);
                                                                                                                        }
                                                                                                                    });
                                                                                                                });
                                                                                                                jQuery('#tmreviews_hidden_prf_url').on('change', function () {
                                                                                                                    $this = jQuery(this);
                                                                                                                    file_data = jQuery(this).prop('files')[0];
                                                                                                                    form_data = new FormData();
                                                                                                                    form_data.append('file', file_data);
                                                                                                                    form_data.append('action', 'file_upload_prf');
                                                                                                                    form_data.append('security_prf', tm_reviews_ajax.security_prf);
                                                                                                                    jQuery.ajax({
                                                                                                                        url: tm_reviews_ajax.url
                                                                                                                        , type: 'POST'
                                                                                                                        , contentType: false
                                                                                                                        , processData: false
                                                                                                                        , data: form_data
                                                                                                                        , success: function (response) {
                                                                                                                            jQuery('#tmreviews_prf').attr("src", response);
                                                                                                                        }
                                                                                                                    });
                                                                                                                });
                                                                                                            });
                                                                                                        </script>
                                                                                                        <?php
}


add_filter( 'acf/get_valid_field', 'tmreviews_change_post_content_type');
function tmreviews_change_post_content_type( $field ) {
    if($field['type'] == 'wysiwyg') {
        $field['type'] = 'textarea';
    }
    return $field;
}






///Save Profile
add_action('wp_ajax_tmreviews_profile_update', 'tmreviews_profile_update_callback');
add_action('wp_ajax_nopriv_tmreviews_profile_update', 'tmreviews_profile_update_callback');
function tmreviews_profile_update_callback() {
    $current_user_id = get_current_user_ID();
    if(isset($_REQUEST['form_data']) && is_array($_REQUEST['form_data']) && !empty($_REQUEST['form_data']) && $current_user_id != '' && $current_user_id != '0'){
        foreach ($_REQUEST['form_data'] as $key => $data){
            if(isset($data) && $data != ''){
                if($key == 'avatar_url'){

                } else {
                    update_user_meta($current_user_id, $key, $data);
                }
            }
        }
    }

    wp_die();
}











//Update Comment
add_action('wp_ajax_tmcomment_update', 'tmcomment_update_callback');
add_action('wp_ajax_nopriv_tmcomment_update', 'tmcomment_update_callback');
function tmcomment_update_callback() {

    $return_html = '';
    $commentrating = '';
    $commentarr = [
        'comment_ID'      => $_REQUEST['id'],
        'comment_content' => $_REQUEST['text'],
    ];

    //if(isset($_REQUEST['text']) && $_REQUEST['text'] != ''){
        wp_update_comment( $commentarr );
  //  }

   // if(isset($_REQUEST['title']) && $_REQUEST['title'] != ''){
        update_comment_meta($_REQUEST['id'], 'tmreviews_review_title', $_REQUEST['title']);
   // }

    // if(isset($_REQUEST['rate']) && $_REQUEST['rate'] != ''){
        update_comment_meta($_REQUEST['id'], 'rating', $_REQUEST['rate']);


        $return_html .= '<div class="comment-rating-show fl-text-bold-style">';
        if( intval( $_REQUEST['rate'] ) ) {
            $rating_icons = '';
            $i = 1;
            while ($i <= $_REQUEST['rate']){
                $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                $i++;
            }
            if($_REQUEST['rate'] < 5){
                $asd = 5 - $_REQUEST['rate'];
                $k = 1;
                while ($k <= $asd){
                    $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                    $k++;
                }
            }
            $commentrating = '<div class="fl-rate-icons">' . $rating_icons . '</div>';
        }

        $return_html .= $commentrating;

        $return_html .= '<div class="fl-single-places-rating-text">';
        $total = intval($_REQUEST['rate']);
        $return_html .=  __('Rating ', 'tm-reviews').number_format($total, 1, '.', ' '). '/5.0';
        $return_html .= '</div>';
        $return_html .= '</div>';



        $return_html_two = '';
        if ( $rating = intval( $_REQUEST['rate']) ) {
            $rating       = intval($_REQUEST['rate'] );
            $rating_icons = '';
            $i            = 1;
            while ( $i <= $rating ) {
                $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                $i ++;
            }
            if ( $rating < 5 ) {
                $asd = 5 - $rating;
                $k   = 1;
                while ( $k <= $asd ) {
                    $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                    $k ++;
                }
            }
            $commentrating = '<div class="fl-user-reviews-rating"><div class="fl-rate-icons">' .
                $rating_icons .
                '</div></div>';
            $return_html_two .= $commentrating;
        }



    // }
    $array_send = array();
    $array_send['single'] = $return_html;
    $array_send['account'] = $return_html_two;
    wp_send_json($array_send);


    wp_die();
}





add_action('wp_ajax_tmcomment_delete', 'tmcomment_delete_callback');
add_action('wp_ajax_nopriv_tmcomment_delete', 'tmcomment_delete_callback');
function tmcomment_delete_callback() {
    wp_delete_comment( $_REQUEST['id'], true);

    wp_send_json('scs');

    wp_die();
}