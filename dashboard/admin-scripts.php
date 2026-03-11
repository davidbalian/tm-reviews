<?php
/**
 * Admin scripts for TM Reviews
 *
 * @package TM_Reviews
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Подключение скриптов для страницы настроек
 */
function tmreviews_admin_scripts( $hook ) {
    global $post_type;
    
    // Проверяем, что мы на странице настроек places
    if ( $hook === 'places_page_settings' || ( $post_type === tmreviews_get_post_type() && $hook === tmreviews_get_post_type() . '_page_settings' ) ) {
        wp_enqueue_script( 'tmreviews-admin-notifications', plugin_dir_url( dirname( __FILE__ ) ) . 'assets/js/admin-notifications.js', array( 'jquery' ), '1.0.0', true );
        
        wp_localize_script( 'tmreviews-admin-notifications', 'tmreviews_notifications', array(
            'nonce'   => wp_create_nonce( 'tmreviews_test_telegram' ),
            'test'    => esc_html__( 'Test Notification', 'tmreviews' ),
            'testing' => esc_html__( 'Testing...', 'tmreviews' ),
            'error'   => esc_html__( 'An error occurred while testing the notification', 'tmreviews' )
        ) );
    }
}
add_action( 'admin_enqueue_scripts', 'tmreviews_admin_scripts' );
