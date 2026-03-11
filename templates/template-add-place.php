<?php
/*
* Template name: User Reviews
* */

get_header();

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

    $added_notice = '<span class="tmreviews_added_notice tmreviews_added_notice_visible">' . __('Submitted for moderation') . '</span>';

    //$redirect_url = get_the_permalink();
   //echo ("<script>location.href = '".$redirect_url."'</script>");

}
$form_url = admin_url('admin-post.php');
$redirect_url = get_the_permalink(get_the_ID());
?>
<?php if(isset($added_notice) && $added_notice != ''){?>
<?php echo $added_notice;?>
<?php } ?>
<div class="tmreviews-add-place container">
    <form class="tmreviewsagic-form tmreviews-dbfl" method="post" action="<?php echo esc_url($redirect_url);?>" id="tmreviews_add_blog_post" name="tmreviews_add_blog_post" enctype="multipart/form-data">
        <div class="tmreviewsrow" id="tmreviewsrow1">
            <span class="fl-add-place-row-title"><?php echo __('General','tm-reviews');?></span>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Title','tm-reviews');?><sup class="tmreviews_estric">*</sup></label>
                </div>
                <div class="tmreviews-field-input tmreviews_required">
                    <input title="Enter your title" type="text" class="" value="" id="place_title" name="place_title" placeholder="">
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Sub Title','tm-reviews');?><sup class="tmreviews_estric">*</sup></label>
                </div>
                <div class="tmreviews-field-input tmreviews_required">
                    <input title="Enter your title" type="text" class="" value="" id="place_sub_title" name="place_sub_title" placeholder="">
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Description','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <?php wp_editor('', 'blog_description', $settings);?>
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
        </div>
        <div class="tmreviewsrow" id="tmreviewsrow2">
            <span class="fl-add-place-row-title"><?php echo __('Contacts','tmreviews');?></span>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Phone','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <input type="text" value="" tabindex="5" size="16" name="place_phone" />
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Email','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <input type="text" value="" tabindex="5" size="16" name="place_email" />
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Company Website','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <input type="text" value="" tabindex="5" size="16" name="place_website" />
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
        </div>
        <div class="tmreviewsrow" id="tmreviewsrow3">
            <span class="fl-add-place-row-title"><?php echo __('Socials','tm-reviews');?></span>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Facebook','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <input type="text" value="" tabindex="5" size="16" name="place_facebook" />
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Twitter','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <input type="text" value="" tabindex="5" size="16" name="place_twitter" />
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Dribble','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <input type="text" value="" tabindex="5" size="16" name="place_dribble" />
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('LinkedIn','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <input type="text" value="" tabindex="5" size="16" name="place_linkedin" />
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Behance','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <input type="text" value="" tabindex="5" size="16" name="place_behance" />
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Instagram','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input">
                    <input type="text" value="" tabindex="5" size="16" name="place_instagram" />
                    <div class="errortext" style="display:none;"></div>
                </div>
            </div>
        </div>
        <div class="tmreviewsrow" id="tmreviewsrow4">
            <span class="fl-add-place-row-title"><?php echo __('Images','tm-reviews');?></span>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Thumbnail Image','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input tmreviews_fileinput">
                    <div class="tmreviews_repeat">
                        <input title="" type="file" class="tmreviews_file" name="thumbnail_image" data-filter-placeholder="" />
                        <div class="errortext" style="display:none;"></div>
                    </div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Logo Image','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input tmreviews_fileinput">
                    <div class="tmreviews_repeat">
                        <input title="" type="file" class="tmreviews_file" name="logo_image" data-filter-placeholder="" />
                        <div class="errortext" style="display:none;"></div>
                    </div>
                </div>
            </div>
            <div class="tmreviews-col">
                <div class="tmreviews-form-field-icon"></div>
                <div class="tmreviews-field-lable">
                    <label><?php _e('Background Image','tm-reviews');?></label>
                </div>
                <div class="tmreviews-field-input tmreviews_fileinput">
                    <div class="tmreviews_repeat">
                        <input title="" type="file" class="tmreviews_file" name="bg_image" data-filter-placeholder="" />
                        <div class="errortext" style="display:none;"></div>
                    </div>
                </div>
            </div>
            <?php

                $manage_gallery = __('Manage gallery', 'tm-reviews');
                $clear_gallery  = __('Clear gallery', 'tm-reviews');
                if(is_user_logged_in()){
                    if(isset($values['pixad_auto_gallery'])) {
                       // $ids = json_decode($values['pixad_auto_gallery'][0]);
                    }
                    else {
                        //$ids = array();
                    }
                    //$cs_ids = is_array($ids) ? implode(",", $ids) : '';
                    //$html  = do_shortcode('[gallery ids="'.$cs_ids.'"]');
                    $html .= '<input id="pixad_auto_gallery_ids" type="hidden" name="place_gallery_ids" value="-1" />';
                    $html .= '<input id="manage_gallery" title="'.esc_html($manage_gallery).'" type="button" value="'.esc_html($manage_gallery).'" />';
                    $html .= '<input id="clear_gallery" title="'.esc_html($clear_gallery).'" type="button" value="'.esc_html($clear_gallery).'" />';
                    echo wp_specialchars_decode($html);
                }
                ?>

        </div>
        <div class="tmreviewsrow" id="tmreviewsrow5">
            <span class="fl-add-place-row-title"><?php echo __('Review','tm-reviews');?></span>
            <div class="tmreviews-col">
               Review
            </div>


        </div>

        <div class="tmreviewsrow" id="tmreviewsrow">
            <span class="fl-add-place-row-title"><?php echo __('Category','tm-reviews');?></span>
            <div class="tmreviews-col">
                <?php
                    $taxomony = get_terms(tmreviews_get_post_type() . '-category');
                    $taxonomy_html = '';
                    if(isset($taxomony) && !empty($taxomony)){
                        $taxonomy_html .= '<select multiple name="tax[]">';
                        foreach ($taxomony as $t){
                            $taxonomy_html .= '<option value = "'.$t->term_id.'">'.$t->name.'</option>';
                        }
                        $taxonomy_html .= '</select>';

                    }
                    echo $taxonomy_html;
                    ?>
            </div>
            <div class="buttonarea tmreviews-full-width-container">
                <button type="submit" class="fl-custom-btn fl-font-style-bolt-two primary-style"><span><?php _e('Submit','tm-reviews');?></span></button>
                <?php wp_nonce_field( 'tmrv_blog_post' ); ?>
            </div>
        </div>
        <div class="all_errors" style="display:none;"></div>
    </form>
</div>


<?php get_footer(); ?>
