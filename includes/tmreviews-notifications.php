<?php
/**
 * TM Reviews Notifications
 *
 * Functions for handling notifications about new places
 *
 * @package TM_Reviews
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Send notification when a new place is created
 *
 * @param int     $post_id The ID of the newly created place.
 * @param WP_Post $post The post object.
 * @param bool    $update Whether this is an update or a new post.
 */
function tmreviews_notify_new_place( $post_id, $post, $update ) {
    // Only proceed if this is a new post of the places type.
    if ( $update || $post->post_type !== tmreviews_get_post_type() || $post->post_status !== 'publish' ) {
        return;
    }

    // Get place details.
    $place_title  = get_the_title( $post_id );
    $place_url    = get_permalink( $post_id );
    $place_author = get_the_author_meta( 'display_name', $post->post_author );

    // Prepare notification message.
    $message = sprintf(
        /* translators: %1$s: place title, %2$s: author name, %3$s: place URL */
        esc_html__( 'New place "%1$s" has been created by %2$s. View it here: %3$s', 'tmreviews' ),
        $place_title,
        $place_author,
        $place_url
    );

    // Send email notification if enabled.
    tmreviews_send_email_notification( $message, $place_title );

    // Send Telegram notification if enabled.
    tmreviews_send_telegram_notification( $message );
}
add_action( 'wp_insert_post', 'tmreviews_notify_new_place', 10, 3 );

/**
 * Send email notification about new place
 *
 * @param string $message The notification message.
 * @param string $place_title The title of the new place.
 */
function tmreviews_send_email_notification( $message, $place_title ) {
    // Check if email notifications are enabled.
    $email_notification = get_option( 'tmreviews_email_notification', 'disable' );
    if ( $email_notification !== 'enable' ) {
        return;
    }

    // Get notification email.
    $notification_email = get_option( 'tmreviews_notification_email', get_option( 'admin_email' ) );
    if ( empty( $notification_email ) ) {
        $notification_email = get_option( 'admin_email' );
    }

    // Prepare email.
    $subject = sprintf(
        /* translators: %s: place title */
        esc_html__( 'New Place Created: %s', 'tmreviews' ),
        $place_title
    );
    $headers = array( 'Content-Type: text/html; charset=UTF-8' );

    // Send email.
    wp_mail( $notification_email, $subject, $message, $headers );
}

/**
 * Send Telegram notification about new place
 *
 * @param string $message The notification message.
 */
function tmreviews_send_telegram_notification( $message ) {
    // Check if Telegram notifications are enabled.
    $telegram_notification = get_option( 'tmreviews_telegram_notification', 'disable' );
    if ( $telegram_notification !== 'enable' ) {
        return;
    }

    // Get Telegram settings.
    $token   = get_option( 'tmreviews_telegram_token', '' );
    $chat_id = get_option( 'tmreviews_telegram_chat_id', '' );

    // Check if token and chat ID are set.
    if ( empty( $token ) || empty( $chat_id ) ) {
        return;
    }

    // Prepare API URL.
    $url = "https://api.telegram.org/bot{$token}/sendMessage";

    // Prepare data.
    $data = array(
        'chat_id'    => $chat_id,
        'text'       => $message,
        'parse_mode' => 'HTML',
    );

    // Send request to Telegram API.
    $response = wp_remote_post(
        $url,
        array(
            'body'    => $data,
            'timeout' => 15,
        )
    );

    // Log error if any.
    if ( is_wp_error( $response ) ) {
        error_log( 'Telegram notification error: ' . $response->get_error_message() );
    }
}

/**
 * Test Telegram notification settings
 */
function tmreviews_test_telegram_notification() {
    // Check if user has permission.
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( esc_html__( 'You do not have permission to perform this action', 'tmreviews' ) );
    }

    // Verify nonce.
    if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'tmreviews_test_telegram' ) ) {
        wp_send_json_error( esc_html__( 'Security check failed', 'tmreviews' ) );
    }

    // Get Telegram settings.
    $token   = get_option( 'tmreviews_telegram_token', '' );
    $chat_id = get_option( 'tmreviews_telegram_chat_id', '' );

    // Check if token and chat ID are set.
    if ( empty( $token ) || empty( $chat_id ) ) {
        wp_send_json_error( esc_html__( 'Telegram Bot Token or Chat ID is missing', 'tmreviews' ) );
    }

    // Prepare test message.
    $message = esc_html__( 'This is a test notification from TM Reviews plugin', 'tmreviews' );

    // Prepare API URL.
    $url = "https://api.telegram.org/bot{$token}/sendMessage";

    // Prepare data.
    $data = array(
        'chat_id'    => $chat_id,
        'text'       => $message,
        'parse_mode' => 'HTML',
    );

    // Send request to Telegram API.
    $response = wp_remote_post(
        $url,
        array(
            'body'    => $data,
            'timeout' => 15,
        )
    );

    // Check for errors.
    if ( is_wp_error( $response ) ) {
        wp_send_json_error( $response->get_error_message() );
    }

    $body   = wp_remote_retrieve_body( $response );
    $result = json_decode( $body, true );

    if ( isset( $result['ok'] ) && $result['ok'] ) {
        wp_send_json_success( esc_html__( 'Test notification sent successfully!', 'tmreviews' ) );
    } else {
        $error = isset( $result['description'] ) ? $result['description'] : esc_html__( 'Unknown error', 'tmreviews' );
        wp_send_json_error( $error );
    }
}
add_action( 'wp_ajax_tmreviews_test_telegram', 'tmreviews_test_telegram_notification' );
