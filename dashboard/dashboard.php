<?php


class TMREVIEWS_Dashboard
{

    const DASHBOARD_DIRECTORY_URI = '/dashboard/';
    const DASHBOARD_DIRECTORY = '/dashboard/';


    public function __construct()
    {

        $this->dashboard_init_data();
        $this->dashboard_init_action();
        $this->dashboard_init_menu_action();
        add_action('admin_init', array($this, 'dashboard_install_plugin_init'));
    }

    public $plugin_path;
    public $plugin_url;
    public $plugin_name;

    public function dashboard_init_data()
    {

        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->plugin_url = plugin_dir_url(__FILE__);
        $this->dashboard_dir = (dirname(__FILE__)) . self::DASHBOARD_DIRECTORY;
        $theme_info = wp_get_theme();
        $theme_parent = $theme_info->parent();
        if (!empty($theme_parent)) {
            $theme_info = $theme_parent;
        }

        $this->theme_name = $theme_info['Name'];
        $this->theme_version = $theme_info['Version'];
        $this->theme_slug = $theme_info['Slug'];
        $this->theme_is_child = !empty($theme_parent);
        $this->theme_slug = $theme_info->get_stylesheet();
        $this->dashboard_slug = 'theme-dashboard';
        $this->tgmslug = 'theme-plugin-install';

    }

    public function dashboard_init_action()
    {
        if (is_admin()) {
            add_action('admin_print_styles', array($this, 'dashboard_print_styles'));
        }
    }

    public function dashboard_print_styles()
    {
        wp_enqueue_style('fl_dashboard_css', plugin_dir_url(__FILE__) . 'css/style.css', array(), $this->theme_version);
        
        // Подключаем специальные стили для страницы настроек (Add special styles for settings page)
        global $pagenow, $typenow;
        if ($pagenow === 'edit.php' && isset($_GET['page']) && $_GET['page'] === 'settings' && $typenow === tmreviews_get_post_type()) {
            wp_enqueue_style('tmreviews_settings_css', plugin_dir_url(__FILE__) . 'css/settings.css', array(), $this->theme_version);
        }
    }


    public function dashboard_init_menu_action()
    {
        add_action('admin_menu', array($this, 'dashboard_admin_menu'));
    }

    public function dashboard_admin_menu()
    {

        add_submenu_page(
            'edit.php?post_type=' . tmreviews_get_post_type(),
            __( 'Settings', 'tmreviews' ),
            __( 'Settings', 'tmreviews' ),
            'manage_options',
            'settings',
            array($this, 'dashboard_print_welcome')
        );
    }


    public function dashboard_print_welcome()
    {
        require_once(dirname(__FILE__) . '/general.php');
    }


    public function dashboard_install_plugin_init()
    {
        if (isset($_GET['fl-plugin-deactivate']) && 'deactivate-plugin' == $_GET['fl-plugin-deactivate']) {
            check_admin_referer('fl-plugin-deactivate', 'fl-plugin-deactivate-nonce');

            $plugins = TGM_Plugin_Activation::$instance->plugins;

            foreach ($plugins as $plugin) {
                if ($plugin['slug'] == $_GET['plugin']) {
                    deactivate_plugins($plugin['file_path']);
                }
            }
        }
        if (isset($_GET['fl-plugin-activate']) && 'activate-plugin' == $_GET['fl-plugin-activate']) {
            check_admin_referer('fl-plugin-activate', 'fl-plugin-activate-nonce');

            $plugins = TGM_Plugin_Activation::$instance->plugins;

            foreach ($plugins as $plugin) {
                if (isset($_GET['plugin']) && $plugin['slug'] == $_GET['plugin']) {
                    activate_plugin($plugin['file_path']);

                    wp_redirect(admin_url('admin.php?page=fl_plugin--install'));
                    exit;
                }
            }
        }
    }
}



function fl_dashboard()
{
    return new TMREVIEWS_Dashboard();
}


fl_dashboard();
