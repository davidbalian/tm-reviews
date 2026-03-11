<?php

if ( ! function_exists( 'gazek_review_text' ) ) :
    function gazek_review_text($text = null) {
        if($text == null) {
            $text = comment_text();
        }
        return wp_kses_post( $text );
    }
endif;


if ( ! function_exists( 'tmreviews_review_title' ) ) :
    function tmreviews_review_title($title = null) {
        if($title == null) {
            $title = '"'.get_comment_meta(get_comment_ID(), 'tmreviews_review_title', true).'"';
        }
        return wp_kses_post( $title );
    }
endif;


add_filter( 'comment_form_fields', 'gazek_move_comment_field_to_bottom' );
if (!function_exists('gazek_move_comment_field_to_bottom')):
    function gazek_move_comment_field_to_bottom( $fields ) {
        $comment_field = $fields['comment'];
        unset( $fields['comment'] );
        $fields['comment'] = $comment_field;
        return $fields;
    }
endif;








/** COMMENTS WALKER */
class tmreviews_reviews_walker_comment extends Walker_Comment
{

    // init classwide variables
    var $tree_type = 'comment';
    var $db_fields = array('parent' => 'comment_parent', 'id' => 'comment_ID');

    /** CONSTRUCTOR
     * You'll have to use this if you plan to get to the top of the comments list, as
     * start_lvl() only goes as high as 1 deep nested comments */
    function __construct()
    { ?>

        <!--<ul class="comments-list">-->

    <?php }

    /** START_LVL
     * Starts the list before the CHILD elements are added. */
    function start_lvl(&$output, $depth = 1, $args = array())
    {
        $GLOBALS['comment_depth'] = $depth; ?>

        <!--<ul class="child-comment">-->

    <?php }

    /** END_LVL
     * Ends the children list of after the elements are added. */
    function end_lvl(&$output, $depth = 0, $args = array())
    {
        $GLOBALS['comment_depth'] = $depth; ?>

        <!-- /.children -->

    <?php }

    /** START_EL */
    function start_el(&$output, $comment, $depth = 1, $args = Array(), $id = 0)
    {
        $depth++;
        $GLOBALS['comment_depth'] = $depth;
        $GLOBALS['comment'] = $comment;
        $parent_class = (empty($args['has_children']) ? '' : 'parent'); ?>

        <div <?php comment_class($parent_class . ' fl-comment'); ?> id="comment-<?php comment_ID() ?>">

        <?php
        $author_id = get_post_field( 'post_author', get_the_ID() );
        $author_name = get_the_author_meta('display_name', $author_id);
        $total = intval(get_comment_meta($comment->comment_ID, 'rating', true));
        $address = tmreviews_get_mod('place_address', true);

        ?>
        <?php if(get_option('tmreviews_google_snippets', true) === 'enable'){ ?>
            <!-- review schema generator -->
            <div class="google-review-snippets">
                <div itemscope itemtype="http://schema.org/Review">
                    <meta itemprop="description" content="<?php echo get_the_excerpt($comment->comment_post_ID);?>">
                    <meta itemprop="datePublished" content="<?php echo get_comment_date();?>">
                    <meta itemprop="worstRating" content="1">
                    <link itemprop="url" href="<?php echo get_permalink($comment->comment_post_ID);?>" rel="author"/>
                    
                    <div itemprop="itemReviewed" itemscope itemtype="https://schema.org/Organization">
                        
        <img itemprop="image" src="<?php echo get_the_post_thumbnail_url($comment->comment_post_ID, 'gazek_size_360x250_crop');  ?>" alt="Legal Seafood"/>
        <span itemprop="name"><?php echo esc_html(get_the_title())?></span>
            <?php if(isset($address['address']) && $address['address'] != ''){ ?>
                <span itemprop="address"><?php echo $address['address'];?></span>
            <?php } ?>
      </div>
                    
                    
                    <span itemprop="reviewBody">
                                <?php if (!$comment->comment_approved) : ?>
                                <?php else:
                                    echo gazek_review_text(); ?>
                                <?php endif; ?>
                            </span>
                    <span itemprop="author" itemscope itemtype="http://schema.org/Person">
                                <span itemprop="name">
                                    <?php echo esc_html($author_name);?>
                                </span>
                             </span>

                    <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating">
                                <span itemprop="ratingValue">
                                   <?php echo esc_html($total);?>
                                </span>  
                        <span itemprop="bestRating">5</span>
                    </div>
                </div>
            </div>
        <?php } ?>



            <div class="comment-container">
                <div class="comment-avatar">
                    <?php
                    $author_id = get_post_field( 'post_author', get_the_ID() );
                    $page_id = get_option('tmreviews_user_reviews_page_id', true);
                    if(class_exists('BuddyPress') && class_exists('Youzify')){
                        $user = get_user_by('ID', $author_id);
                        $user_page_link = get_site_url() . '/members/' . $user->user_login;
                    } else {
                        $author = get_user_by( 'ID', $comment->user_id );
                        if(isset($page_id) && !empty($page_id)){
                            $user_page_link = get_permalink($page_id) . '?author=' . $author->user_nicename;
                        }
                    }
                    ?>
                    <?php if(isset($page_id) && !empty($page_id)){ ?>
                        <?php if($comment->user_id == 0){?>
                            <?php echo(wp_kses_post($args['avatar_size'] != 0 ? get_avatar($comment, $args['avatar_size']) : '')); ?>
                        <?php } else { ?>
                            <a href="<?php echo esc_url($user_page_link); ?>">
                                <?php echo(wp_kses_post($args['avatar_size'] != 0 ? get_avatar($comment, $args['avatar_size']) : '')); ?>
                            </a>
                        <?php } ?>

                    <?php } else { ?>
                        <?php echo(wp_kses_post($args['avatar_size'] != 0 ? get_avatar($comment, $args['avatar_size']) : '')); ?>
                    <?php } ?>
                    <span class="comment-author-name sas fl-font-style-regular-two">
                        <?php echo wp_kses_post(get_comment_author());?>
                    </span>
                </div>
                <div class="comment-meta cf">
                    <div class="comments--rating-wrapper">
                        <div class="comment-rating-show fl-text-bold-style">
                            <?php
                                echo isnaider_extend_comment_rating_single();
                            ?>
                            <div class="fl-single-places-rating-text">
                                <?php
                                $reviews = get_comments(array('post_type' => tmreviews_get_post_type(), 'post_id' => $comment->comment_post_ID));
                                ?>
                                <?php //foreach($reviews as $rev){ ?>
                                    <?php
                                    //$total += intval(get_comment_meta($rev->comment_ID, 'rating', true));
                                    //$average = $total / $reviews_count;
                                    ?>
                                <?php //} ?>
                                <?php echo __('Rating ', 'tm-reviews').number_format($total, 1, '.', ' '). '/5.0'; ?>
                            </div>
                        </div>
                        <span class="comment--time fl-text-regular-style">
                            <span class="fl-link-comment">
                                <span class="fl-comment-date-text"><?php echo __('Review Published: ', 'tm-reviews');?></span>
                                <span class="fl-comment-date"><?php echo get_comment_date('F jS, Y'); ?></span>
                            </span>
                            <?php //edit_comment_link( esc_html__( '(Edit)', 'gazek' ), '  ', '' );
                            ?>
                       
                        
                         
                            
                            
                             </span>
                        
                    </div>
                    <?php $employer = get_comment_meta($comment->comment_ID, 'employer', true); ?>
                    <?php if(isset($employer) && $employer != '' && $employer != 'all'){ ?>
                        <div class="fl-review-to-empl">
                            <span><?php echo __('Review to Employer: ', 'tm-reviews') . $employer;?></span>
                        </div>
                    <?php } ?>

                    <div class="comment-moderation">
                        <span class="fl-reply-from"><?php
                            $parent_author = get_comment_author($comment->comment_parent);


                            echo __('Reply to ', 'tm-reviews');?><span><?php echo '"'. $parent_author . '"';?></span></span>
                        <?php if (!$comment->comment_approved) : ?>
                            <em class="comment-awaiting-moderation"><?php echo __("Your comment is awaiting moderation.", 'tm-reviews');?></em>
                        <?php else: ?>


                            <?php echo '<span class="fl-review-title">'.tmreviews_review_title().'</span>';
                            echo '<span class="fl-review-content">'.gazek_review_text().'</span>'; ?>

                            <?php
                            $comment_author_id = $comment->user_id;

                            if($comment_author_id == get_current_user_ID() || current_user_can('administrator')){
                                if( $comment_author_id != '0'){ ?>
                                    <a id="tmreviews_edit_btn_<?php comment_ID() ?>" class="tmreviews_btn tm_edit_btn"><?php echo __('Edit', 'tm-reviews');?></a>
                                    <a id="tmreviews_update_btn_<?php comment_ID() ?>" class="tmreviews_btn tm_save_btn"><?php echo __("Save", "tm-reviews");?></a>
                                    <a id="tmreviews_del_btn_<?php comment_ID() ?>" class="tmreviews_btn tm_del_btn"><?php echo __("Delete", "tm-reviews");?></a>

                                    <script>
                                        jQuery.noConflict()(function($) {
                                            var rating_htmls = jQuery('#comment-<?php comment_ID() ?>').find('.comment-rating-show');
                                            var cont_htmls = jQuery('#comment-<?php comment_ID() ?>').find('p');
                                            var title_htmls = jQuery('#comment-<?php comment_ID() ?>').find('span.fl-review-title');


                                            jQuery('#tmreviews_edit_btn_<?php comment_ID() ?>').click(function (e) {
                                                if(jQuery(this).hasClass('tm_edit_cancel')){
                                                    var cont = jQuery('#tmreviews_update_btn_<?php comment_ID() ?>').siblings('textarea[name="comment"]');
                                                    var title = jQuery('#tmreviews_update_btn_<?php comment_ID() ?>').siblings('input[name="tmreviews_review_title"]');
                                                    var rating = jQuery('#comment-<?php comment_ID() ?>').find('.comment-form-rating.tmeditcomment');

                                                    cont.replaceWith('<p>' + cont_htmls.html() + '</p>');
                                                    title.replaceWith('<span class="fl-review-title">' + title_htmls.html() + '</span>');

                                                    rating.replaceWith(rating_htmls);

                                                    jQuery(this).removeClass('tm_edit_cancel');
                                                    jQuery('#comment-<?php comment_ID() ?> .comment-moderation').removeClass('tmreviews_btns_active');
                                                    jQuery(this).html("<?php echo __('Edit', 'tm-reviews')?>");

                                                } else {

                                                    var cont = jQuery(this).siblings('p');
                                                    var title = jQuery(this).siblings('span.fl-review-title');
                                                    var rating = jQuery('#comment-<?php comment_ID() ?>').find('.comment-rating-show');
                                                    var rating_two = rating;
                                                    if(cont.length !== 0){
                                                        cont.replaceWith('<textarea name="comment">' + cont.html() +'</textarea>');
                                                    } else {
                                                        if(jQuery(this).siblings('textarea[name="comment"]').length === 0){
                                                            jQuery(this).parent().append('<textarea name="comment"></textarea>');
                                                        }
                                                    }

                                                    if(title.length !== 0){
                                                        title.replaceWith('<input type="text" name="tmreviews_review_title" value=' + title.html() + '/>');
                                                    } else {
                                                        if(jQuery(this).siblings('input[name="tmreviews_review_title"]').length === 0){
                                                            jQuery(this).parent().append('<input type="text" name="tmreviews_review_title"/>');
                                                        }
                                                    }

                                                    if(rating.length !== 0){
                                                        var rating_html = '<div class="comment-form-rating tmeditcomment">' +
                                                            '        <label><?php echo __("Your rating", "tm-reviews");?></label>' +
                                                            '        <p class="stars selected"><span>' +
                                                            '<a class="star-1 <?php echo $total == 1 ? 'active':'';?>">1</a>' +
                                                            '<a class="star-2 <?php echo $total == 2 ? 'active':'';?>">2</a>' +
                                                            '<a class="star-3 <?php echo $total == 3 ? 'active':'';?>">3</a>' +
                                                            '<a class="star-4 <?php echo $total == 4 ? 'active':'';?>">4</a>' +
                                                            '<a class="star-5 <?php echo $total == 5 ? 'active':'';?>">5</a>' +
                                                            '</span></p><select name="rating" id="rating-autos-edit" required="" style="display: none;">' +
                                                            '                            <option value="">Rate…</option>' +
                                                            '                            <option value="5" <?php echo $total == 5 ? 'selected':'';?>>Perfect</option>' +
                                                            '                            <option value="4" <?php echo $total == 4 ? 'selected':'';?>>Good</option>' +
                                                            '                            <option value="3" <?php echo $total == 3 ? 'selected':'';?>>Average</option>' +
                                                            '                            <option value="2" <?php echo $total == 2 ? 'selected':'';?>>Not that bad</option>' +
                                                            '                            <option value="1" <?php echo $total == 1 ? 'selected':'';?>>Very poor</option>' +
                                                            '                        </select></div>' +
                                                            '<script>jQuery.noConflict()(function($) {' +
                                                            'jQuery(".comment-form-rating.tmeditcomment a").click(function (e) {'+
                                                            'e.preventDefault();'+

                                                            'jQuery(".comment-form-rating.tmeditcomment a").removeClass("active");'+
                                                            'jQuery(this).addClass("active");'+
                                                            'jQuery("#rating-autos-edit option[value=\"+jQuery(this).html()+\"]").prop("selected", true);'+
                                                            '});' +
                                                            '});';
                                                        rating.replaceWith(rating_html);
                                                    } else {
                                                        if(jQuery(this).siblings('input[name="tmreviews_review_title"]').length === 0){
                                                            jQuery(this).parent().append('<input type="text" name="tmreviews_review_title"/>');
                                                        }
                                                    }

                                                    jQuery(this).addClass('tm_edit_cancel');
                                                    jQuery('#comment-<?php comment_ID() ?> .comment-moderation').addClass('tmreviews_btns_active');
                                                    jQuery(this).html("<?php echo __('Cancel', 'tm-reviews')?>");
                                                }

                                            });

                                            jQuery('.comment-form-rating.tmeditcomment a').click(function (e) {
                                                e.preventDefault();

                                            });

                                            jQuery('#tmreviews_update_btn_<?php comment_ID() ?>').click(function (e) {
                                                jQuery('#comment-<?php comment_ID()?>').addClass('ajax-loading');


                                                var new_text = jQuery(this).siblings('textarea[name="comment"]');
                                                var new_title = jQuery(this).siblings('input[name="tmreviews_review_title"]');
                                                var new_rate = jQuery(this).parents('.comment-meta').find('#rating-autos-edit');
                                                var form_data = {};

                                                form_data['id'] = <?php echo $comment->comment_ID;?>;
                                                form_data['action'] = 'tmcomment_update';
                                                form_data['text'] = new_text.val();
                                                form_data['title'] = new_title.val();
                                                form_data['rate'] = new_rate.val();



                                                jQuery.post( tm_reviews_ajax.url, form_data, function(response) {
                                                    // console.log(response);
                                                    var cont = jQuery('#tmreviews_update_btn_<?php comment_ID() ?>').siblings('textarea[name="comment"]');
                                                    var title = jQuery('#tmreviews_update_btn_<?php comment_ID() ?>').siblings('input[name="tmreviews_review_title"]');
                                                    var rating = jQuery('#comment-<?php comment_ID() ?>').find('.comment-form-rating.tmeditcomment');

                                                    cont.replaceWith('<p>' + cont.val() + '</p>');
                                                    title.replaceWith('<span class="fl-review-title">"' + title.val() + '"</span>');

                                                    var rating_html = response;

                                                    rating.replaceWith(rating_html);
                                                    jQuery('#tmreviews_edit_btn_<?php comment_ID() ?>').html("<?php echo __('Edit', 'tm-reviews')?>");
                                                    jQuery('#comment-<?php comment_ID()?>').removeClass('ajax-loading');
                                                    jQuery('#comment-<?php comment_ID() ?> .comment-moderation').removeClass('tmreviews_btns_active');


                                                });


                                            });


                                            jQuery('#tmreviews_del_btn_<?php comment_ID() ?>').click(function (e) {
                                                var form_data = {};
                                                form_data['id'] = <?php echo $comment->comment_ID;?>;
                                                form_data['action'] = 'tmcomment_delete';
                                                jQuery.post( tm_reviews_ajax.url, form_data, function(response) {
                                                    // console.log(response);
                                                    jQuery('#comment-<?php comment_ID() ?>').remove();
                                                });
                                            });

                                        });
                                    </script>
                                <?php } ?>
                            <?php } ?>

                            
                        <?php endif; ?>
                        <?php
                        $user = wp_get_current_user();
                        $user_role = $user->roles;
                        $post_id = $comment->comment_post_ID;
                        $author_id = get_post_field ('post_author', $post_id);
                       ?>
                    </div>
                </div>
            </div>
            
            
           
            
            
            <?php
            $author_email = get_the_author_meta('user_email', $author_id);
            $user = get_user_by( 'id', get_current_user_ID() );
            $user_email = $user->user_email;
            ?>
            <?php if($user_email == $author_email) { ?>
                <div class="comment--reply-wrap fl-text-medium-style">
                    <div class="reply-link-wrap">
                        <?php
                        comment_reply_link(array_merge($args, array(
                            'add_below' => isset($args['add_below']) ? $args['add_below'] : 'comment',
                            'depth' => $depth,
                            'max_depth' => $args['max_depth'],
                            'reply_text' => ''.sprintf(__('%s Reply', 'tm-reviews'), '')
                        )), $comment->comment_ID);?>
                    </div>
                </div>
            <?php } else { ?>
                <?php if(is_user_logged_in()) {
                    if(class_exists('WeDevs_Dokan')){
                        $vendor = dokan()->vendor->get(  get_current_user_ID() );
                        $permalink = get_site_url() . '/members/' .$vendor->data->user_nicename . '/account_settings/';
                    } else {
                        $permalink = get_site_url() . '/members/' . $user->user_login . '/account_settings/';
                    }
                    ?>
                    <?php if(class_exists('MemberOrder')){
                        $permalink_plans_url = '#';
                        $permalink_plans = get_option('tmreviews_plans_page_id', true);
                        if(isset($permalink_plans) && $permalink_plans != ''){
                            $permalink_plans_url = get_permalink($permalink_plans);
                        }
                        $author_level = pmpro_getMembershipLevelForUser(get_current_user_ID());
                        ?>
                        <?php if($author_level){ ?>
                            <div class="comment--reply-wrap fl-text-medium-style">
                                <div class="reply-link-wrap">
                                    <?php
                                    comment_reply_link(array_merge($args, array(
                                        'add_below' => isset($args['add_below']) ? $args['add_below'] : 'comment',
                                        'depth' => $depth,
                                        'max_depth' => $args['max_depth'],
                                        'reply_text' => ''.sprintf(__('%s Reply', 'tm-reviews'), '')
                                    )), $comment->comment_ID);?>
                                </div>
                            </div>
                        <?php } else { ?>
                            <div class="no-vrf-block">
                                <a class="no-vrf-reply" href="<?php echo esc_url($permalink_plans_url)?>"><?php echo __('To Reply', 'tm-reviews')?></a>
                            </div>
                        <?php } ?>

                    <?php } else { ?>
                        <div class="no-vrf-block">
                            <a class="no-vrf-reply" href="#no-vrf-reply-form" data-uk-toggle="" aria-expanded="false"><?php echo __('To Reply', 'tm-reviews')?></a>
                            <div class="fl-empl-form uk-modal" id="no-vrf-reply-form" data-uk-modal="" tabindex="0">
                                <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
                                    <?php echo __('To  reply, it is necessary for you to confirm that you are the proprietor of a business. Please proceed with the registration and verification process in the account panel by providing us with evidence for confirmation in our ', 'tm-reviews')?>
                                    <a href="<?php echo esc_url($permalink)?>"><?php echo __('Center verification', 'tm-reviews')?></a>.
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                <?php } ?>
            <?php } ?>
        <?php }

    function end_el(&$output, $comment, $depth = 1, $args = array())
    { ?>
            
            
            

        </div>

    <?php }

    /** DESTRUCTOR
     * I'm just using this since we needed to use the constructor to reach the top
     * of the comments list, just seems to balance out nicely:) */
    function __destruct()
    { ?>

        <!-- /#comment-list -->

    <?php }
}
