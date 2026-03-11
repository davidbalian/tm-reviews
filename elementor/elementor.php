<?php

defined( 'ABSPATH' ) or die();

class TMReviews_Helper_Core_Elementor {

    private static $_instance = null;

    private $_version;
    private $file;
    private $dir;
    private $widgets_dir;
    private $assets_dir;
    private $assets_url;
    private $_token;

    function __construct( $file, $version = '1.0.0' ) {

        $this->_version    = $version;
        $this->file        = $file;
        $this->dir         = dirname( $this->file );
        $this->widgets_dir = trailingslashit( $this->dir ) . 'elementor';
        $this->assets_dir  = trailingslashit( $this->dir ) . 'assets';
        $this->assets_url  = esc_url( trailingslashit( plugins_url( '/elementor/assets/', $this->file ) ) );
        $this->_token      = 'tm-reviews-core-elementor';

        $this->load_elementor_plugin_files();

        add_action( 'elementor/init', [ $this, 'load_elementor_widgets' ] );
        add_action( 'elementor/elements/categories_registered', [ $this, 'add_widget_category' ], 1 );
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'elementor_widgets_init' ] );
        add_action( 'elementor/frontend/after_register_scripts', [ $this, 'elementor_add_js' ],99 );
       // add_action( 'elementor/frontend/after_register_styles', [ $this, 'elementor_add_css' ] );
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'elementor_add_css_editor' ] );


        // Custom Standart Option
        TMReviews_Helper_Core_Elements_Section()->init();
        TMReviews_Helper_Core_Default_Item_Option()->init();
    }

    public function add_widget_category( $elements_manager ) {

        $elements_manager->add_category(
            'tm-reviews-helper-core-elements',
            [
                'title' => esc_html__( 'TM Reviews Custom Elements', 'tm-reviews' ),
                'icon'  => 'tm-reviews-logo',
            ]
        );

    }

    public function load_elementor_widgets() {
        require_once $this->widgets_dir . '/widgets/places_category.php';
        require_once $this->widgets_dir . '/widgets/reviews.php';


        //  require_once $this->widgets_dir . '/widgets/places.php';
       // require_once $this->widgets_dir . '/widgets/places_no_map.php';

      //  require_once $this->widgets_dir . '/widgets/places.php';
       // require_once $this->widgets_dir . '/widgets/places_cities.php';
        //require_once $this->widgets_dir . '/widgets/places_categories.php';
      //  require_once $this->widgets_dir . '/widgets/places_search.php';



       // require_once $this->widgets_dir . '/widgets/places_map.php';




        // require_once $this->widgets_dir . '/widgets/places_image_slider.php';
      //  require_once $this->widgets_dir . '/widgets/places_icon_box.php';
     //   require_once $this->widgets_dir . '/widgets/places_text_editor.php';
     //   require_once $this->widgets_dir . '/widgets/places_video.php';
     //   require_once $this->widgets_dir . '/widgets/places_new_add.php';
    }

    public function elementor_widgets_init() {
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Category() );
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Reviews() );


        //    \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Slider_Map() );
     //   \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Slider_No_Map() );


      //  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places() );
      //  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Cities() );
       // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Categories() );
     //   \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Search() );



       // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Map_Routing() );


      //  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Image_Slider() );
      //  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Icon_Box() );
      //  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Custom_Text_Editor() );
      //  \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Custom_Videor() );
       // \Elementor\Plugin::instance()->widgets_manager->register_widget_type( new TMReviews_Places_Add() );
    }

    /** Add Custom Widgets*/
    public function load_elementor_plugin_files() {
        require_once $this->widgets_dir . '/custom_default_element/tmreviews-helping-core-change-default-section.php';
        require_once $this->widgets_dir . '/custom_default_element/tmreviews-helping-core-default-option.php';

    }


    public function elementor_add_js() {
        $is_preview_mode = class_exists( 'Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode();
        if ( $is_preview_mode ) {
            wp_register_script( $this->_token . '-elementor', esc_url( $this->assets_url ) . 'js/elementor.js', ['jquery'], $this->_version );
            wp_enqueue_script( $this->_token . '-elementor' );
            wp_register_script( $this->_token . '-custom-animation-elementor', esc_url( $this->assets_url ) . 'js/elementor_custom_animation.js', ['jquery'], $this->_version );
            wp_enqueue_script( $this->_token . '-custom-animation-elementor' );
        }
    }


    public function elementor_add_css_editor() {
        wp_register_style( $this->_token . '-elementor', esc_url( $this->assets_url ) . 'css/elementor.css', [], $this->_version );
        wp_enqueue_style( $this->_token . '-elementor' );
    }

    public static function instance( $file = '', $version = '1.0.0' ) {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self( $file, $version );
        }

        return self::$_instance;
    } // End instance ()


    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'tm-reviews' ), $this->_version );
    } // End __clone ()


    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'tm-reviews' ), $this->_version );
    } // End __wakeup ()


}

// Custom Controle
require_once(TMREVIEWS_THEME_HELPER_PLUGIN_PATH. '/elementor/custom-controle/image-selector/custom-control-init.php' );