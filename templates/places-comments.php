<?php
/**
 * The template for displaying comments.
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package gazek
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if (post_password_required()) {
    return;
}
if( !class_exists('Fl_Helping_Addons')){
    $comment_html_class = 'comment-without-back';
} else {
    $comment_html_class = '';
}

add_filter( 'comment_form_defaults', 'gazek_custom_reply_title' );
function gazek_custom_reply_title( $defaults ){
    $defaults['title_reply_before'] = '<h4 id="reply-title" class="comment-reply-title">';
    $defaults['title_reply_after'] = '</h4>';
    return $defaults;
}

//Email
$email = tmreviews_get_mod('place_email', true);

remove_theme_support('html5', 'comment-form');
?>

<div class="comments-container <?php echo esc_attr($comment_html_class);?>" id="comments" data-coment-content="<?php esc_attr(bloginfo('title'));?>">


        <div class="comments-list">

            <?php
            $review_title = get_post_meta( get_the_ID(),'review_tmreviews_review_title', true);
            $review_comment = get_post_meta( get_the_ID(),'review_review_comment', true);

            ?>
            <?php if(isset($review_comment) && $review_comment != ''){ ?>
                <div class="tmreviews_author_review_wrap comment even thread-odd thread-alt depth-1  fl-comment">
                    <div class="comment-container">
                        <div class="comment-avatar">
                            <?php
                            $author_id = get_post_field( 'post_author', get_the_ID() );
                            $page_id = get_option('tmreviews_user_reviews_page_id', true);
                            if(class_exists('BuddyPress') && class_exists('Youzify')){
                                $user = get_user_by('ID', $author_id);
                                $user_page_link = get_site_url() . '/members/' . $user->user_login;
                            } else {
                                $author = get_user_by( 'ID', get_the_author_meta( 'ID' ) );
                                if(isset($page_id) && !empty($page_id)){
                                    $user_page_link = get_permalink($page_id) . '?author=' . $author->user_nicename;
                                }
                            }
                            ?>
                            <?php if(isset($page_id) && !empty($page_id)){ ?>
                                <?php if(get_the_author_meta( 'ID' ) == 0){?>
                                    <?php echo(wp_kses_post($args['avatar_size'] != 0 ? get_avatar(get_the_author_meta( 'ID' ), $args['avatar_size']) : '')); ?>
                                <?php } else { ?>
                                    <a href="<?php echo esc_url($user_page_link); ?>">
                                        <?php echo get_avatar( get_the_author_meta( 'ID' )); ?>
                                    </a>
                                <?php } ?>
                            <?php } else { ?>
                                <?php echo get_avatar( get_the_author_meta( 'ID' )); ?>
                            <?php } ?>

                            <span class="comment-author-name sas fl-font-style-regular-two">
                                <?php // echo wp_kses_post( get_the_author_meta( 'display_name' ));?>
                                <?php  echo __('Author Review', 'tm-reviews');?>
                            </span>

                        </div>

                        <div class="comment-meta cf">
                            <div class="comments--rating-wrapper">
                                <div class="comment-rating-show fl-text-bold-style">
                                    <?php echo tmreviews_extend_comment_rating_single_by_postid(get_the_ID()); ?>
                                    <div class="fl-single-places-rating-text">
                                        <?php $total = intval(get_post_meta(get_the_ID(), 'review_rating', true)); ?>
                                        <?php echo __('Rating ', 'tm-reviews').number_format($total, 1, '.', ' '). '/5.0'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="comment-moderation">
                                <span class="fl-reply-from"><?php echo __('Reply from ', 'tm-reviews');?><span><?php echo '"'.get_the_title() . '"';?></span></span>

                                <?php if(isset($review_title) && $review_title != ''){ ?>
                                    <span class="fl-review-title">"<?php echo esc_html( $review_title)?>"</span>
                                <?php } ?>

                                <?php if(isset($review_comment) && $review_comment != ''){ ?>
                                    <p><?php echo esc_html( $review_comment)?></p>
                                <?php } ?>

                                <?php
                                $tmreviews_notify_comp = get_option('tmreviews_notify_comp');

                                if($tmreviews_notify_comp == 'enable'){ ?>
                                    <a target="_blank" class="notify-company" href="mailto:<?php echo $email;?>?subject=Comment%20regarding%20your%20company&body=Hello.%20A%20comment%20regarding%20your%20company%20was%20found%20on%20this%20website.%20<?php echo get_permalink()?>">
                                         <i class="fa fa-envelope" aria-hidden="true"></i> <?php  echo __('Notify company regarding this comment', 'tm-reviews');?>
                                    </a>
                                <?php } ?>
                                
                                
                             
                                
                            </div>
                        </div>

                    </div>
                </div>
            <?php } ?>
            <?php if (have_comments()) : ?>
                <?php
                    wp_list_comments(array(
                        'walker' => new tmreviews_reviews_walker_comment(),
                        'short_ping' => true,
                        'avatar_size' => 60
                    ));
                ?>
            <?php endif; ?>
         </div>
        <!-- .comment-list -->
        <?php if (have_comments()) : ?>
            <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : // Are there comments to navigate through? ?>
                <nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
                    <h2 class="sr-only"><?php esc_html_e('Comment navigation', 'tm-reviews'); ?></h2>
                    <?php
                    $page = get_query_var('cpage');
                    ?>
                    <?php if (isset($page)): ?>
                        <div class="fl-comment-pagination cf">
                            <?php paginate_comments_links( array(
                                'prev_text'  => '<i class="fa fa-angle-left" aria-hidden="true"></i>',
                                'next_text' => '<i class="fa fa-angle-right" aria-hidden="true"></i>'
                            ) );?>
                        </div><!-- .nav-links -->
                    <?php endif; ?>
                </nav><!-- #comment-nav-below -->
                <?php
            endif;
        endif;




    // If comments are closed and there are comments, let's leave a little note, shall we?
    if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>

        <p class="no-comments"><?php echo __('Comments are closed', 'tm-reviews'); ?></p>
        <?php
    endif;
    $commenter = wp_get_current_commenter();
    $req      = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true'" : '' );
    $html_req = ( $req ? " required='required'" : '' );
    $required_text = sprintf( ' ' . wp_kses_post(__('Required fields are marked %s', 'tm-reviews' )), '<span class="required">*</span>' );
    ?>
        <div class="fl-form-review-reply">
            <?php
            comment_form(array(
                'title_reply' => '<span class="reply-title">' . esc_html__('Add Review', 'tm-reviews') . '</span>',
                'comment_notes_before' => '',
                'fields' => array('<div class="comment-field-wrapper">',
                    'author' => '<div class="author-name"> 
                                <input type="text" class="required" name="author" value="' . esc_attr($commenter['comment_author']) .'" placeholder="'.__('Your Name *', 'tm-reviews').'">
				             </div>',
                    'email' => '<div class="author-email">
                                <input type="email" class="required" name="email" value="' . esc_attr($commenter['comment_author_email']) .'" placeholder="'.__('Email Address *', 'tm-reviews').'">
                            </div>',
                    '</div>'),
                'class_submit'  => 'hidden button',
                'class_form'    => 'fl-comment-form',
                'comment_field' => '<div class="author-comment">       
                                    <textarea class="required" name="comment" rows="5" aria-required="true" placeholder="'.__('Enter your Review *', 'tm-reviews').'"></textarea>
                                </div>',
                'comment_notes_after' => '<div class="submit-btn-container">
                                            <button type="submit" class="fl-custom-btn fl-font-style-bolt-two primary-style"><span>' . __('Submit Review', 'tm-reviews') . '</span></button>
                                       </div>'
            ));
            ?>
        </div>


</div><!-- #comments -->

