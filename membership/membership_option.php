<?php
if(class_exists('MemberOrder')) {
    add_action('pmpro_membership_level_after_other_settings', 'tmreviews_membership_meta');
    function tmreviews_membership_meta($level){
        if (class_exists('MemberOrder')) {
            $max_items = get_pmpro_membership_level_meta($level->id, 'max_items', true);
            $max_feat_items = get_pmpro_membership_level_meta($level->id, 'max_feat_items', true);
            if (!isset($max_items) or $max_items == '') {
                $max_items = 0;
            }
            if (!isset($max_feat_items) or $max_feat_items == '') {
                $max_feat_items = 0;
            }
            ?>

            <h1 class="tmreviews_pmpro"><?php echo __('Theme Settings', 'tm-reviews') ?></h1>
            <div class="tmreviews_pmpro_level_meta_wrap">
                <label for="max_items">
                    <?php echo __('Maximum Items', 'tm-reviews'); ?>
                </label>
                <input type="number" name="max_items" class="regular-text" value="<?php echo esc_attr($max_items); ?>">
            </div>

            <div class="tmreviews_pmpro_level_meta_wrap">
                <label for="number">
                    <?php echo __('Maximum Featured Items', 'tm-reviews'); ?>
                </label>
                <input type="number" name="max_feat_items" class="regular-text"
                       value="<?php echo esc_attr($max_feat_items); ?>">
            </div>
            <?php
        }
    }
    function tmreviews_membership_meta_new($level){
        if (class_exists('MemberOrder')) {
            if (isset($_POST['max_items']) && $_POST['max_items'] != '') {
                if (!get_pmpro_membership_level_meta($level, 'max_items', true)) {
                    add_pmpro_membership_level_meta($level, 'max_items', $_POST['max_items'], true);
                } else {
                    update_pmpro_membership_level_meta($level, 'max_items', $_POST['max_items']);
                }
            }
            if (isset($_POST['max_feat_items']) && $_POST['max_feat_items'] != '') {
                if (!get_pmpro_membership_level_meta($level, 'max_feat_items', true)) {
                    add_pmpro_membership_level_meta($level, 'max_feat_items', $_POST['max_feat_items'], true);
                } else {
                    update_pmpro_membership_level_meta($level, 'max_feat_items', $_POST['max_feat_items']);
                }
            }
        }
    }
    add_action('pmpro_save_membership_level', 'tmreviews_membership_meta_new', 10, 2);
}





function tmreviews_user_can_add($user_id){
    if(class_exists('MemberOrder')){
        $author_level= pmpro_getMembershipLevelForUser($user_id);
        if($author_level){
            $max_items = get_pmpro_membership_level_meta( $author_level->ID, 'max_items', true );
            $user_posts_count = count_user_posts( $user_id , tmreviews_get_post_type(), true);
            if (intval($user_posts_count) < intval($max_items)){
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    } else {
        return true;
    }
}
function tmreviews_user_can_add_return_text($user_id){
    if(class_exists('MemberOrder')){
        $author_level = pmpro_getMembershipLevelForUser($user_id);
        if($author_level){
            $max_items = get_pmpro_membership_level_meta( $author_level->ID, 'max_items', true );
            $user_posts_count = count_user_posts( $user_id , tmreviews_get_post_type(), true);

            if (intval($user_posts_count) >= intval($max_items)){
                $text = '<div class="alert alert-warning" role="alert">'. __('Within your subscription, the quantity is included: ' . $max_items, 'tm-reviews') . '</div>';
                return $text;
            }
        } else {
            $dokan_dash = get_option('dokan_pages')['dashboard'];
            $tmreviews_plans_page_id= get_option('tmreviews_plans_page_id', true);

            $text = '<div class="alert alert-warning" role="alert">'. __('Please choose a subscription package. ', 'tm-reviews') . '<a href="'.get_permalink($tmreviews_plans_page_id).'">'. __('Packages page.', 'tm-reviews') . '</a></div>';
            return $text;
        }
    }
}


function tmreviews_get_edit_buttons($ID, $author_ID){
   if($author_ID == get_current_user_ID()){

        $dokan_dash = get_option('dokan_pages')['dashboard'];
        $header_btn_link = get_permalink($dokan_dash). '/add/' . '?id=' . $ID;
        $status = get_post_status($ID);

        $result_html = '';
        $user = get_user_by('ID', get_the_author_ID());

       if(class_exists('MemberOrder')) {
           $author_level= pmpro_getMembershipLevelForUser($author_ID);
           if($author_level){
               $max_items = get_pmpro_membership_level_meta( $author_level->ID, 'max_items', true );
               $user_posts_count = count_user_posts( $author_ID , tmreviews_get_post_type(), true);

               if ($status != 'pending') {
                   if ($status == 'draft') {
                       if (intval($user_posts_count) < intval($max_items)) {
                           $result_html .= '<a class="tm-top-draft-button" href="' . esc_url(get_site_url() . '/members/') . $user->user_login . '/' . tmreviews_get_post_type() . '?hide=false&id=' . get_the_ID() . '"><span>' . __('Show',
                               'tm-reviews') . '</span></a>';
                       }
                   } else {
                       $result_html .= '<a class="tm-top-draft-button" href="' . esc_url(get_site_url() . '/members/') . $user->user_login . '/' . tmreviews_get_post_type() . '?hide=true&id=' . get_the_ID() . '"><span>' . __('Hide',
                               'tm-reviews') . '</span></a>';
                   }
               }

                //Max featured
               $args = array(
                   'fields' => 'ids',
                   'numberposts' => -1,
                   'post_type'   => tmreviews_get_post_type(),
                   'author'      =>  get_current_user_ID(),
                   'meta_query' => array(
                       array(
                           'key'       => 'place_featured',
                           'value'     => 'enable',
                       )
                   )
               );
               $max_feat_items = get_pmpro_membership_level_meta( $author_level->ID, 'max_feat_items', true );
               $user_feat_posts_count = count(get_posts($args));


               $featured = get_post_meta($ID, 'place_featured', true);
               if(isset($featured) && $featured == 'disable'){
                   if (intval($user_feat_posts_count) < intval($max_feat_items)) {
                       $result_html .= '<a class="tm-top-draft-button" href="' . esc_url(get_site_url() . '/members/') . $user->user_login . '/' . tmreviews_get_post_type() . '?feat=true&id=' . get_the_ID() . '"><span>' . __('Choose for Featured',
                               'tm-reviews') . '</span></a>';
                   }
               } elseif (isset($featured) && $featured == 'enable'){
                   $result_html .= '<a class="tm-top-draft-button" href="' . esc_url(get_site_url() . '/members/') . $user->user_login . '/' . tmreviews_get_post_type() . '?feat=false&id=' . get_the_ID() . '"><span>' . __('Delete fro Featured',
                           'tm-reviews') . '</span></a>';
               }



           }
           ?>
           <a class="edit_btn" href="<?php echo esc_url($header_btn_link) ?>">
               <?php echo __('Edit', 'tm-reviews'); ?>
           </a>

           <?php
           $result_html .= '<span class="card__wrap-label templines-label-status-' . get_post_status() . '">';
           $result_html .= '<span class="card__label">';
           $status_show = get_post_status() == 'publish' ? 'published' : get_post_status();
           $result_html .= esc_html($status_show);
           $result_html .= '</span>';
           $result_html .= '</span>';

           echo $result_html;
       } else { ?>
           <a class="edit_btn" href="<?php echo esc_url($header_btn_link) ?>">
               <?php echo __('Edit', 'tm-reviews'); ?>
           </a>

           <?php if ($status != 'pending') {
               $result_html .= '<a class="tm-top-draft-button" href="' . esc_url(get_site_url() . '/members/') . $user->user_login . '/' . tmreviews_get_post_type() . '?hide=true&id=' . get_the_ID() . '"><span>' . __('Hide',
                       'tm-reviews') . '</span></a>';
               if ($status == 'draft') {
                   $result_html .= '<a class="tm-top-draft-button" href="' . esc_url(get_site_url() . '/members/') . $user->user_login . '/' . tmreviews_get_post_type() . '?hide=false&id=' . get_the_ID() . '"><span>' . __('Show',
                           'tm-reviews') . '</span></a>';
               }
           }
           $result_html .= '<span class="card__wrap-label templines-label-status-' . get_post_status() . '">';
           $result_html .= '<span class="card__label">';
           $status_show = get_post_status() == 'publish' ? 'published' : get_post_status();
           $result_html .= esc_html($status_show);
           $result_html .= '</span>';
           $result_html .= '</span>';

           echo $result_html;
       }
    }
}


if(class_exists('WeDevs_Dokan')) {
    add_action('admin_init', 'tmreviews_add_custom_capability_to_vendor_role');
    function tmreviews_add_custom_capability_to_vendor_role() {
        $new_role = get_role('seller');
        $new_role->add_cap('upload_files', true);
    }
}









if(class_exists('TMBooking__Helping_Addonssss')){
    function tmbooking_sim_product_box() {
        add_meta_box(
            'tmbooking_sim_product_id',
            __( 'Similar product(for Booking)', 'tm-reviews' ),
            'tmbooking_sim_product_box_callback',
            tmreviews_get_post_type()
        );
    }
    function tmbooking_sim_product_box_callback(){ ?>
        <div class="form_options">
            <?php
            global $post;
            $args = array(
                'fields' => 'id',
                'post_type'      => 'product',
                'posts_per_page' => -1,
            );
            $loop = get_posts( $args );
            $catalog_item_id = get_post_meta($post->ID, 'tmbooking_sim_product_id', true);
            ?>
            <select name="tmbooking_sim_product_id">
                <?php foreach ($loop as $item) {

                    ?>
                    <?php if(isset($catalog_item_id) && $catalog_item_id == $item->ID){?>
                        <option value="<?php echo esc_attr($item->ID);?>" selected><?php echo esc_html($item->post_title);?></option>
                    <?php } else {?>
                        <option value="<?php echo esc_attr($item->ID);?>"><?php echo esc_html($item->post_title);?></option>
                    <?php } ?>
                <?php } ?>
            </select>
        </div>
        <?php
    }
    add_action( 'add_meta_boxes', 'tmbooking_sim_product_box' );
    function tmreviews_save_meta_boxes( $post_id ) {
        if (isset($_POST['tmbooking_sim_product_id']) && $_POST['tmbooking_sim_product_id'] != '') {
            update_post_meta($post_id, 'tmbooking_sim_product_id', $_POST['tmbooking_sim_product_id']);
        } else {
            $new_array = '';
            update_post_meta($post_id, 'tmbooking_sim_product_id', $new_array);
        }
    }
    add_action( 'save_post', 'tmreviews_save_meta_boxes' );
}

