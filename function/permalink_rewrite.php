<?php
/**
 * Permalink rewrite logic for /{category-slug}/{post-slug}/ URL structure.
 *
 * Handles URL generation, incoming request resolution, SEO redirects
 * from the legacy /{post-type}/{post-slug}/ format, and rewrite rule
 * flushing when taxonomy terms change.
 */

add_filter( 'post_type_link', 'tmreviews_category_permalink', 10, 2 );

/**
 * Replace the %tmreviews_cat% placeholder in generated permalinks
 * with the first assigned taxonomy term slug.
 */
function tmreviews_category_permalink( $post_link, $post ) {
    $post_type = tmreviews_get_post_type();

    if ( $post->post_type !== $post_type ) {
        return $post_link;
    }

    $taxonomy = $post_type . '-category';
    $terms    = wp_get_post_terms( $post->ID, $taxonomy );

    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
        $category_slug = $terms[0]->slug;
    } else {
        $category_slug = 'uncategorized';
    }

    return str_replace( '%tmreviews_cat%', $category_slug, $post_link );
}


add_action( 'template_redirect', 'tmreviews_redirect_old_permalink' );

/**
 * 301 redirect legacy /{post-type}/{post-slug}/ URLs
 * to the new /{category-slug}/{post-slug}/ structure.
 */
function tmreviews_redirect_old_permalink() {
    $post_type      = tmreviews_get_post_type();
    $requested_path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

    $pattern = '#^' . preg_quote( $post_type, '#' ) . '/([^/]+)$#';

    if ( ! preg_match( $pattern, $requested_path, $matches ) ) {
        return;
    }

    $post_slug = $matches[1];

    $found = get_posts( array(
        'name'        => $post_slug,
        'post_type'   => $post_type,
        'post_status' => 'publish',
        'numberposts' => 1,
    ) );

    if ( empty( $found ) ) {
        return;
    }

    $new_url = get_permalink( $found[0]->ID );

    if ( $new_url && ! is_wp_error( $new_url ) ) {
        wp_redirect( $new_url, 301 );
        exit;
    }
}


add_action( 'template_redirect', 'tmreviews_redirect_old_taxonomy_query_param' );

/**
 * 301 redirect legacy ?places-category=slug query-param URLs
 * to the new /slug/ pretty permalink.
 */
function tmreviews_redirect_old_taxonomy_query_param() {
    $taxonomy = tmreviews_get_post_type() . '-category';

    if ( empty( $_GET[ $taxonomy ] ) ) {
        return;
    }

    if ( is_search() || is_admin() ) {
        return;
    }

    $has_other_params = array_diff( array_keys( $_GET ), array( $taxonomy ) );
    if ( ! empty( $has_other_params ) ) {
        return;
    }

    $term = get_term_by( 'slug', sanitize_text_field( $_GET[ $taxonomy ] ), $taxonomy );
    if ( ! $term || is_wp_error( $term ) ) {
        return;
    }

    $new_url = get_term_link( $term, $taxonomy );
    if ( $new_url && ! is_wp_error( $new_url ) ) {
        wp_redirect( $new_url, 301 );
        exit;
    }
}


add_action( 'created_term', 'tmreviews_flush_on_term_change', 10, 3 );
add_action( 'edited_term',  'tmreviews_flush_on_term_change', 10, 3 );
add_action( 'delete_term',  'tmreviews_flush_on_term_change', 10, 3 );

/**
 * Flush rewrite rules whenever a term in our taxonomy is
 * created, edited, or deleted so the permastruct stays current.
 */
function tmreviews_flush_on_term_change( $term_id, $tt_id, $taxonomy ) {
    $post_type = tmreviews_get_post_type();

    if ( $taxonomy === $post_type . '-category' ) {
        flush_rewrite_rules();
    }
}


add_action( 'init', 'tmreviews_schedule_rewrite_flush' );

/**
 * One-time flush after the rewrite structure changes.
 * Sets a transient so it only runs once, then clears itself.
 * Trigger by deleting the transient or on plugin activation.
 */
function tmreviews_schedule_rewrite_flush() {
    if ( get_transient( 'tmreviews_flush_rewrite' ) ) {
        flush_rewrite_rules();
        delete_transient( 'tmreviews_flush_rewrite' );
    }
}


add_action( 'init', 'tmreviews_flush_taxonomy_rewrite_once', 99 );

/**
 * One-time flush after enabling pretty taxonomy permalinks.
 * Runs once, then sets an option so it never runs again.
 */
function tmreviews_flush_taxonomy_rewrite_once() {
    if ( get_option( 'tmreviews_taxonomy_rewrite_v2' ) ) {
        return;
    }
    flush_rewrite_rules();
    update_option( 'tmreviews_taxonomy_rewrite_v2', true );
}
