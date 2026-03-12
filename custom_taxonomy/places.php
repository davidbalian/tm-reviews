<?php
add_theme_support( 'post-thumbnails', 'places' );


add_action( 'init', 'tmreviews__places_init', 0 );

if( !function_exists('tmreviews__places_init') ){
    function tmreviews__places_init() {

        $post_type = tmreviews_get_post_type();
        $taxonomy  = $post_type . '-category';

        add_rewrite_tag( '%tmreviews_cat%', '([^/]+)', $taxonomy . '=' );

        $labels = array(
            'name'                  => ucfirst($post_type),
            'singular_name'         => esc_html__( 'Item', 'tm-reviews' ),
            'add_new'               => esc_html__( 'Add New Item', 'tm-reviews' ),
            'add_new_item'          => esc_html__( 'Add New Item', 'tm-reviews' ),
            'edit_item'             => esc_html__( 'Edit Item', 'tm-reviews' ),
            'new_item'              => esc_html__( 'Add New Item', 'tm-reviews' ),
            'view_item'             => esc_html__( 'View Item', 'tm-reviews' ),
            'search_items'          => esc_html__( 'Search', 'tm-reviews' ),
            'not_found'             => esc_html__( 'No items found', 'tm-reviews' ),
            'not_found_in_trash'    => esc_html__( 'No items found in trash', 'tm-reviews' )
        );

        $args = array(
            'labels'                => $labels,
            'public'                => true,
            'supports'              => array( 'title', 'editor', 'thumbnail', 'author', 'comments'),
            'capability_type'       => 'post',
            'menu_position'         => 5,
            'has_archive'           => $post_type,
            'menu_icon'             => TMREVIEWS_HELPING_PREVIEW_IMAGE.'/favicon.png',
            'rewrite'               => array(
                'slug'       => '%tmreviews_cat%',
                'with_front' => false,
            ),
        );

        $args = apply_filters('tmreviews__args', $args);

        register_post_type($post_type, $args);


        /**
         * Register a taxonomy for places Categories
         * http://codex.wordpress.org/Function_Reference/register_taxonomy
         */

        $taxonomy_restaurant_category_labels = array(
            'name'                          => esc_html__( 'Categories', 'tm-reviews' ),
            'singular_name'                 => esc_html__( 'Category', 'tm-reviews' ),
            'search_items'                  => esc_html__( 'Search Categories', 'tm-reviews' ),
            'popular_items'                 => esc_html__( 'Popular Categories', 'tm-reviews' ),
            'all_items'                     => esc_html__( 'All Categories', 'tm-reviews' ),
            'parent_item'                   => esc_html__( 'Parent Category', 'tm-reviews' ),
            'parent_item_colon'             => esc_html__( 'Parent Category:', 'tm-reviews' ),
            'edit_item'                     => esc_html__( 'Edit Category', 'tm-reviews' ),
            'update_item'                   => esc_html__( 'Update Category', 'tm-reviews' ),
            'add_new_item'                  => esc_html__( 'Add New Category', 'tm-reviews' ),
            'new_item_name'                 => esc_html__( 'New Category Name', 'tm-reviews' ),
            'separate_items_with_commas'    => esc_html__( 'Separate categories with commas', 'tm-reviews' ),
            'add_or_remove_items'           => esc_html__( 'Add or remove categories', 'tm-reviews' ),
            'choose_from_most_used'         => esc_html__( 'Choose from the most used categories', 'tm-reviews' ),
            'menu_name'                     => esc_html__( 'Categories', 'tm-reviews' ),
        );

        $taxonomy_restaurant_category_args = array(
            'labels'            => $taxonomy_restaurant_category_labels,
            'public'            => true,
            'show_in_nav_menus' => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_tagcloud'     => true,
            'hierarchical'      => true,
            'query_var'         => true,
            // Use a dedicated base slug so this taxonomy
            // does not hijack all top-level URLs like /register or /contact.
            'rewrite'           => array(
                'slug'       => $taxonomy,
                'with_front' => false,
            ),
        );
        register_taxonomy( $taxonomy, array( $post_type ), $taxonomy_restaurant_category_args );
    }
}




add_filter( 'manage_posts_columns', 'tmreviews__add_thumbnail_column', 10, 1 );

if( !function_exists('tmreviews__add_thumbnail_column') ){
    function tmreviews__add_thumbnail_column( $columns ) {

        $column_thumbnail = array( 'thumbnail' => esc_html__('Thumbnail','tm-reviews' ) );
        $columns = array_slice( $columns, 0, 2, true ) + $column_thumbnail + array_slice( $columns, 1, NULL, true );
        return $columns;
    }
}



add_action( 'manage_posts_custom_column', 'tmreviews__display_thumbnail', 10, 1 );

if( !function_exists('tmreviews__display_thumbnail') ){
    function tmreviews__display_thumbnail( $column ) {
        global $post;
        
        // Выходим сразу, если это не колонка thumbnail
        if ($column !== 'thumbnail') {
            return;
        }
        
        // Проверяем, является ли текущий пост типом 'places'
        if (get_post_type($post->ID) === 'places') {
            // Получаем ID миниатюры
            $thumbnail_id = get_post_thumbnail_id($post->ID);
            
            // Если миниатюра существует, выводим ее
            if ($thumbnail_id) {
                // Получаем URL миниатюры
                $thumbnail_url = wp_get_attachment_image_src($thumbnail_id, array(50, 50));
                
                if ($thumbnail_url) {
                    // Выводим изображение с помощью HTML, а не через функцию get_the_post_thumbnail
                    echo '<img src="' . esc_url($thumbnail_url[0]) . '" class="tmreviews-admin-image" width="50" height="50" alt="' . esc_attr(get_the_title($post->ID)) . '" />';
                }
            }
        }
    }
}