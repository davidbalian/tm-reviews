<?php

get_header();

if(isset($_GET['author']) && $_GET['author']!=''){
    $user = get_user_by('slug',$_GET['author']);
    $author_id = $user->ID;

    $avatar = get_avatar($author_id);
    $author_name = get_the_author_meta( 'display_name', $author_id );

    $args = array(
        'user_id' => $author_id
    );
    $comments = get_comments( $args );
    $r = 0;
    foreach ($comments as $c){
        $rate = get_comment_meta( $c->comment_ID, 'rating', true );
        if(isset($rate) && $rate != ''){
            $r++;
        }
    }

    $user_reviews_count = $r;
    //$user_reviews_count = get_custom_user_comments_count($author_id);

    $user_likes_count = get_custom_user_likes_count($author_id);
    $user_dislikes_count = get_custom_user_dislikes_count($author_id);

    $reviews = get_comments(array('post_type' => tmreviews_get_post_type(), 'author__in' => $author_id));

    $background_image = get_the_post_thumbnail_url();
    if(isset($background_image) && $background_image != ''){
        $background_css = 'style="background-image:url(' . $background_image . ')"';
    } else {
        $background_css = '';
    }
}

?>

<?php if(isset($author_id) && $author_id !='') {?>

    <div class="fl-user-reviews-header container">
        <div class="fl-user-reviews-background-image" <?php echo $background_css;?>></div>
        <div class="fl-user-reviews-background-color"></div>
        <div class="fl-user-reviews-header-contain">
            <div class="fl-user-reviews-header-left">
                <div class="fl-user-reviews-avatar">
                    <?php echo $avatar;?>
                </div>
                <div class="fl-user-reviews-meta">
                    <span class="fl-reviews-name"><?php echo esc_attr($author_name, 'tm-reviews');?></span>
                </div>
            </div>
            <div class="fl-user-reviews-header-right">
                <div class="fl-user-reviews-counts">
                    <div class="fl-user-reviews-circle">
                        <i class="fa fa-star" aria-hidden="true"></i>
                        <span class="fl-count-number"><?php echo $user_reviews_count;?></span>
                    </div>
                    <span class="fl-count-bottom-text"><?php echo __('Reviews', 'tm-reviews');?></span>
                </div>

                <div class="fl-user-reviews-counts">
                    <div class="fl-user-reviews-circle">
                        <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                        <span class="fl-count-number"><?php echo $user_likes_count;?></span>
                    </div>
                    <span class="fl-count-bottom-text"><?php echo __('User Likes', 'tm-reviews');?></span>
                </div>

                <div class="fl-user-reviews-counts">
                    <div class="fl-user-reviews-circle">
                        <i class="fa fa-thumbs-down" aria-hidden="true"></i>
                        <span class="fl-count-number"><?php echo $user_dislikes_count;?></span>
                    </div>
                    <span class="fl-count-bottom-text"><?php echo __('User Dislikes', 'tm-reviews');?></span>
                </div>
            </div>
        </div>
    </div>
        <div class="fl-user-reviews-content container">
            <?php
            $reviews_count = count($reviews);
            $total = 0;
            ?>
            <?php foreach($reviews as $rev){ ?>
                <?php
                $total += intval(get_comment_meta($rev->comment_ID, 'rating', true));
                $average = $total / $reviews_count;
                ?>
            <?php } ?>
                <?php foreach($reviews as $rev){
                $rate = get_comment_meta( $rev->comment_ID, 'rating', true );
                ?>
                <?php if(isset($rate) && $rate != ''){?>
                    <div class="fl-user-reviews-contain">
                    <div class="fl-user-reviews-left">
                        <div class="fl-user-reviews-avatar">
                            <?php echo $avatar;?>
                        </div>
                    </div>
                    <div class="fl-user-reviews-right">
                        <div class="fl-user-reviews-top">
                            <div class="fl-user-review-top-post-title">
                                <span class="fl-user-review-text"><?php echo __('Review:', 'tm-reviews')?></span>

                                <?php $post_link = get_post_permalink($rev->comment_post_ID);?>
                                <?php $post_title = get_the_title($rev->comment_post_ID);?>
                                <a class="fl-user-review-place" href="<?php echo esc_url($post_link, 'tm-reviews');?>"><?php echo esc_attr($post_title, 'tm-reviews');?></a>
                            </div>

                            <div class="fl-user-reviews-rating">
                                <?php
                                if( $rating = intval( get_comment_meta( $rev->comment_ID, 'rating', true ) ) ) {
                                    $rating = intval( get_comment_meta( $rev->comment_ID, 'rating', true ));
                                    $rating_icons = '';
                                    $i = 1;
                                    while ($i <= $rating){
                                        $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                                        $i++;
                                    }
                                    if($rating < 5){
                                        $asd = 5 - $rating;
                                        $k = 1;
                                        while ($k <= $asd){
                                            $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                                            $k++;
                                        }
                                    }
                                    $commentrating = '<div class="fl-rate-icons">'.
                                        $rating_icons .
                                        '</div>';
                                    echo $commentrating;
                                }
                                ?>
                                <div class="fl-user-reviews-rating-text">
                                    <?php echo __('Rating '.number_format($rating, 1, '.', ' '). '/5.0', 'tm-reviews'); ?>
                                </div>
                            </div>

                            <div class="fl-user-reviews-date-contain">
                                <span class="fl-user-reviews-date-text"><?php echo __('Review Published:', 'tm-reviews');?></span>
                                <span class="fl-user-reviews-date"><?php echo esc_attr(get_comment_date('F j, Y', $rev->comment_ID), 'tm-reviews');?></span>
                            </div>
                        </div>
                        <div class="fl-user-review-bottom">
                            <?php
                                $title = '"'.get_comment_meta($rev->comment_ID, 'tmreviews_review_title', true).'"';
                                $post_link = get_the_permalink($rev->comment_post_ID);
                            ?>
                            <span class="fl-review-title"><?php echo esc_attr($title, 'tm-reviews'); ?></span>
                            <div class="fl-user-reviews-content">
                                <?php //echo esc_attr(get_the_content(null, false, $rev->comment_post_ID), 'gazek');?>
                                <?php echo esc_attr($rev->comment_content, 'tm-reviews');?>
                            </div>

                            <div class="fl-user-reviews-edit">

                            </div>
                        </div>
                    </div>
                </div>
                    <?php $no_reviews = '';?>
                <?php } else { ?>
                    <?php $no_reviews = '<span class="no_reviews">' . __('There are no reviews yet', 'tm-reviews').'</span>';?>
                <?php } ?>

            <?php } ?>
        <?php echo $no_reviews;?>
        </div>

    <?php } ?>


<?php get_footer(); ?>