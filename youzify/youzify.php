<?php
// Places Tab

function tmreviews_profile_tab_places() {
    global $bp;
    bp_core_new_nav_item( array(
        'name' => ucfirst(tmreviews_get_post_type()),
        'slug' => tmreviews_get_post_type(),
        'screen_function' => 'tmreviews_places_screen',
        'position' => 5,
        'parent_url'      => bp_loggedin_user_domain() . '/places/',
        'parent_slug'     => $bp->profile->slug,
        'default_subnav_slug' => tmreviews_get_post_type()
    ) );
}
add_action( 'bp_setup_nav', 'tmreviews_profile_tab_places' );
function tmreviews_places_screen() {
    add_action( 'bp_template_title', 'tmreviews_places_title' );
    add_action( 'bp_template_content', 'tmreviews_places_content' );
    bp_core_load_template( 'buddypress/members/single/plugins' );
}
function tmreviews_places_title() {
    $return_title = '';
    $return_title .= '
                    <div class="item-list-tabs youzify-default-subnav no-ajax" id="subnav" aria-label="Member secondary navigation" role="navigation">
                        <ul>
                            <li id="posts-all-personal-li" class="current selected"><a id="all" href="#"><i class="fas fa-globe"></i>'.__("Catalog", "tm-reviews").'</a></li>
                        </ul>
                    </div>
                    ';
    echo $return_title;
}
function tmreviews_places_content() {
    echo do_shortcode('[tmreviews_your_places]');
}


// Add Places Tab
function tmreviews_profile_tab_add() {
    global $bp;
    if(get_current_user_id() == bp_displayed_user_id()){
        bp_core_new_nav_item( array(
            'name' => __('Add New', 'tm-reviews'),
            'slug' => 'add_place',
            'screen_function' => 'tmreviews_add_screen',
            'position' => 7,
            'parent_url'      => bp_loggedin_user_domain() . '/add_place/',
            'parent_slug'     => $bp->profile->slug,
            'default_subnav_slug' => 'add_place'
        ) );
    }
}
add_action( 'bp_setup_nav', 'tmreviews_profile_tab_add' );
function tmreviews_add_screen() {
    add_action( 'bp_template_title', 'tmreviews_add_title' );
    add_action( 'bp_template_content', 'tmreviews_add_content' );
    bp_core_load_template( 'buddypress/members/single/plugins' );
}
function tmreviews_add_title() {
    $return_title = '';

    if (isset($_GET['id']) && $_GET['id'] != ''){
        $return_title .= '
                    <div class="item-list-tabs youzify-default-subnav no-ajax" id="subnav" aria-label="Member secondary navigation" role="navigation">
                        <ul>
                            <li id="posts-all-personal-li" class="current selected"><a id="all" href="#"><i class="fas fa-edit"></i>'.__("Edit", "tm-reviews").'</a></li>
                        </ul>
                    </div>
                    ';
    } else {
        $return_title .= '
                    <div class="item-list-tabs youzify-default-subnav no-ajax" id="subnav" aria-label="Member secondary navigation" role="navigation">
                        <ul>
                            <li id="posts-all-personal-li" class="current selected"><a id="all" href="#"><i class="fas fa-plus-square"></i>'.__("Add New", "tm-reviews").'</a></li>
                        </ul>
                    </div>
                    ';
    }

    echo $return_title;
}
function tmreviews_add_content() {


    if(class_exists('MemberOrder')) {
        if(isset($_GET['id']) && $_GET['id'] != '') {
            if(is_string( get_post_status( $_GET['id'] ) )){
                echo do_shortcode('[tmreviews_add_place]');
            } else {
                echo tmreviews_user_can_add_return_text(get_current_user_ID());
                if (tmreviews_user_can_add(get_current_user_ID())){
                    echo do_shortcode('[tmreviews_add_place]');
                }
            }

        } else {
            echo tmreviews_user_can_add_return_text(get_current_user_ID());
            if (tmreviews_user_can_add(get_current_user_ID())){
                echo do_shortcode('[tmreviews_add_place]');
            }
        }

    } else {
        echo do_shortcode('[tmreviews_add_place]');
    }



  //  echo do_shortcode('[tmreviews_add_place]');
}


// Reviews Tab
function tmreviews_profile_tab_reviews() {
    global $bp;
    bp_core_new_nav_item(array(
        'name' => 'Reviews',
        'slug' => 'reviews',
        'screen_function' => 'tmreviews_reviews_screen',
        'position' => 8,
        'parent_url' => bp_loggedin_user_domain() . '/reviews/',
        'parent_slug' => $bp->profile->slug,
        'default_subnav_slug' => 'reviews'
    ));
}
add_action( 'bp_setup_nav', 'tmreviews_profile_tab_reviews' );
function tmreviews_reviews_screen() {
    add_action( 'bp_template_title', 'tmreviews_reviews_title' );
    add_action( 'bp_template_content', 'tmreviews_reviews_content' );
    bp_core_load_template( 'buddypress/members/single/plugins' );
}
function tmreviews_reviews_title() {
    $return_title = '';
    $return_title .= '
                    <div class="item-list-tabs youzify-default-subnav no-ajax" id="subnav" aria-label="Member secondary navigation" role="navigation">
                        <ul>
                            <li id="posts-all-personal-li" class="current selected"><a id="all" href="#"><i class="fas fa-star"></i>'.__("Reviews", "tm-reviews").'</a></li>
                        </ul>
                    </div>
                    ';
    echo $return_title;
}
function tmreviews_reviews_content() {
    echo do_shortcode('[youzify_your_reviews]');
}





// About Tab
function tmreviews_profile_tab_about() {
    global $bp;
    bp_core_new_nav_item(array(
        'name' => 'About',
        'slug' => 'about',
        'screen_function' => 'tmreviews_about_screen',
        'position' => 1,
        'parent_url' => bp_loggedin_user_domain() . '/about/',
        'parent_slug' => $bp->profile->slug,
        'default_subnav_slug' => 'about'
    ));
}
add_action( 'bp_setup_nav', 'tmreviews_profile_tab_about' );
function tmreviews_about_screen() {
    add_action( 'bp_template_title', 'tmreviews_about_title' );
    add_action( 'bp_template_content', 'tmreviews_about_content' );
    bp_core_load_template( 'buddypress/members/single/plugins' );
}
function tmreviews_about_title() {
    $return_title = '';
    $return_title .= '
                        <div class="item-list-tabs youzify-default-subnav no-ajax" id="subnav" aria-label="Member secondary navigation" role="navigation">
                            <ul>
                                <li id="posts-all-personal-li" class="current selected"><a id="all" href="#"><i class="fas fa-globe"></i>'.__("About", "tm-reviews").'</a></li>
                            </ul>
                        </div>
                        ';
    echo $return_title;
}
function tmreviews_about_content() {
    ?>
    <div class="youzif_user_about_tab_wrap">
        <?php $author_id = bp_displayed_user_id();?>
        <?php $desc = get_the_author_meta('description', $author_id);
        $user = get_user_by('ID', $author_id);


        $phone = get_user_meta( $author_id, 'billing_phone', true );
        $billing_address_1 = get_user_meta( $author_id, 'billing_address_1', true );
        $billing_address_2 = get_user_meta( $author_id, 'billing_address_2', true );
        $billing_city = get_user_meta( $author_id, 'billing_city', true );
        ?>

        <div class="youzif_user_about_name"><?php echo __('I am ', 'tm-reviews') . $user->display_name . '.';?></div>
        <?php if(isset($desc) && $desc != ''){ ?>
            <div class="youzif_user_about_desc"><?php echo tmreviews_wp_kses($desc);?></div>
        <?php } else { ?>
            <div class="alert alert-warning"><?php echo __('The user has not shared any details about their biography.', 'tm-reviews');?></div>
        <?php } ?>
    </div>
    <?php
}



// Settings Tab
function tmreviews_profile_tab_set() {
    global $bp;
    if(get_current_user_id() == bp_displayed_user_id()) {
        bp_core_new_nav_item(array(
            'name' => 'Settings',
            'slug' => 'account_settings',
            'screen_function' => 'tmreviews_set_screen',
            'position' => 2,
            'parent_url' => bp_loggedin_user_domain() . '/account_settings/',
            'parent_slug' => $bp->profile->slug,
            'default_subnav_slug' => 'account_settings'
        ));
    }
}
add_action( 'bp_setup_nav', 'tmreviews_profile_tab_set' );
function tmreviews_set_screen() {
    add_action( 'bp_template_title', 'tmreviews_set_title' );
    add_action( 'bp_template_content', 'tmreviews_set_content' );
    bp_core_load_template( 'buddypress/members/single/plugins' );
}
function tmreviews_set_title() {
    $return_title = '';
    $return_title .= '
                        <div class="item-list-tabs youzify-default-subnav no-ajax" id="subnav" aria-label="Member secondary navigation" role="navigation">
                            <ul>
                                <li id="posts-all-personal-li" class="current selected"><a id="all" href="#"><i class="fas fa-globe"></i>'.__("Settings", "tm-reviews").'</a></li>
                            </ul>
                        </div>
                        ';
    echo $return_title;
}
function tmreviews_set_content() {
    echo do_shortcode('[tmreviews_account_settings]');
}






// Messages Tab
function tmreviews_profile_tab_messages() {
    global $bp;
    if (class_exists('Better_Messages') && bp_is_active( 'messages' )){
        if(get_current_user_id() == bp_displayed_user_id()) {
            bp_core_new_nav_item(array(
                'name' => 'Messages',
                'slug' => 'messages',
                'screen_function' => 'tmreviews_messages_screen',
                'position' => 40,
                'parent_url' => bp_loggedin_user_domain() . '/messages/',
                'parent_slug' => $bp->profile->slug,
                'default_subnav_slug' => 'messages'
            ));
        }
    }
}
add_action( 'bp_setup_nav', 'tmreviews_profile_tab_messages' );
function tmreviews_messages_screen() {
    add_action( 'bp_template_title', 'tmreviews_messages_title' );
    add_action( 'bp_template_content', 'tmreviews_messages_content' );
    bp_core_load_template( 'buddypress/members/single/plugins' );
}
function tmreviews_messages_title() {
    $return_title = '';
    $return_title .= '
                        <div class="item-list-tabs youzify-default-subnav no-ajax" id="subnav" aria-label="Member secondary navigation" role="navigation">
                            <ul>
                                <li id="posts-all-personal-li" class="current selected"><a id="all" href="#"><i class="fas fa-globe"></i>'.__("Favourite", "tm-reviews").'</a></li>
                            </ul>
                        </div>
                        ';
    echo $return_title;
}
function tmreviews_messages_content() {
    echo do_shortcode('[better_messages]');
}







// Woo Orders Tab
function tmreviews_profile_tab_orders() {
    if(class_exists('WooCommerce') && get_current_user_id() == bp_displayed_user_id()){
        global $bp;
        bp_core_new_nav_item( array(
            'name' => __('Orders', 'tm-reviews'),
            'slug' => 'my_orders',
            'screen_function' => 'tmreviews_orders_screen',
            'position' => 4,
            'parent_url'      => bp_loggedin_user_domain() . '/my_orders/',
            'parent_slug'     => $bp->profile->slug,
            'default_subnav_slug' => 'my_orders',
        ));
    }
}
add_action( 'bp_setup_nav', 'tmreviews_profile_tab_orders' );
function tmreviews_orders_screen() {
    add_action( 'bp_template_title', 'tmreviews_orders_title' );
    add_action( 'bp_template_content', 'tmreviews_orders_content' );
    bp_core_load_template( 'buddypress/members/single/plugins' );
}
function tmreviews_orders_title() {
    $return_title = '';
    $return_title .= '
                        <div class="item-list-tabs youzify-default-subnav no-ajax" id="subnav" aria-label="Member secondary navigation" role="navigation">
                            <ul>
                                <li id="posts-all-personal-li" class="current selected"><a id="all" href="#"><i class="fas fa-globe"></i>'.__("Orders", "tm-reviews").'</a></li>
                            </ul>
                        </div>
                        ';
    echo $return_title;
}
function tmreviews_orders_content() {
    ?>
    <?php

    defined( 'ABSPATH' ) || exit;

    do_action( 'woocommerce_before_account_orders', $has_orders );


    $customer_orders = get_posts(
        apply_filters(
            'woocommerce_my_account_my_orders_query',
            array(
                'numberposts' => 5,
                'meta_key'    => '_customer_user',
                'meta_value'  => get_current_user_id(),
                'post_type'   => wc_get_order_types( 'view-orders' ),
                'post_status' => array_keys( wc_get_order_statuses() ),
            )
        )
    );
    ?>

    <?php if ( isset($customer_orders) && !empty($customer_orders) ) : ?>

        <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
            <thead>
                <tr>
                    <?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
                        <th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ( $customer_orders as $customer_order ) {
                    $order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                    $item_count = $order->get_item_count() - $order->get_item_count_refunded();
                    ?>
                    <tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
                        <?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
                            <td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
                                <?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
                                    <?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

                                <?php elseif ( 'order-number' === $column_id ) : ?>
                                    <span href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
                                        <?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
                                    </span>

                                <?php elseif ( 'order-date' === $column_id ) : ?>
                                    <time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>

                                <?php elseif ( 'order-status' === $column_id ) : ?>
                                    <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>

                                <?php elseif ( 'order-total' === $column_id ) : ?>
                                    <?php
                                    /* translators: 1: formatted order total 2: total order items */
                                    echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
                                    ?>

                                <?php elseif ( 'order-actions' === $column_id ) : ?>
                                    <?php
                                    $actions = wc_get_account_orders_actions( $order );

                                    if ( ! empty( $actions ) ) {
                                        foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                                            echo '<span data-orderid="'. $order->get_order_number() .'" class="tmreviews_woo_view_btn woocommerce-button button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</span>';
                                        }
                                    }
                                    ?>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <tr data-orderid="<?php echo $order->get_order_number();?>" class="woo_table_details_show">
                        <td></td>
                        <td class="woocommerce-table__product-name product-name">
                            <?php
                            $order_items           = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );

                            foreach ( $order_items as $item_id => $item ) {
                                $product = $item->get_product();?>

                                <?php
                                $is_visible        = $product && $product->is_visible();
                                $product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

                                echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ) );

                                $qty          = $item->get_quantity();
                                $refunded_qty = $order->get_qty_refunded_for_item( $item_id );

                                if ( $refunded_qty ) {
                                    $qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
                                } else {
                                    $qty_display = esc_html( $qty );
                                }

                                echo apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times;&nbsp;%s', $qty_display ) . '</strong>', $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                                do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

                                wc_display_item_meta( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

                                do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
                                ?>
                            <?php } ?>
                        </td>
                        <td class="woocommerce-table__product-total product-total">
                            <?php echo $order->get_formatted_line_subtotal( $item ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </td>
                        <td>
                            <table>
                                <tfoot>
                                <?php
                                foreach ( $order->get_order_item_totals() as $key => $total ) {
                                    ?>
                                    <tr>
                                        <th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
                                        <td><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                                <?php if ( $order->get_customer_note() ) : ?>
                                    <tr>
                                        <th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
                                        <td><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
                                    </tr>
                                <?php endif; ?>
                                </tfoot>
                            </table>
                        </td>
                        <td></td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>

        <?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

        <?php if ( 1 < $customer_orders->max_num_pages ) : ?>
            <div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
                <?php if ( 1 !== $current_page ) : ?>
                    <a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
                <?php endif; ?>

                <?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
                    <a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else : ?>
        <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
            <?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?>
        </div>
    <?php endif; ?>

    <?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>

    <?php
}


