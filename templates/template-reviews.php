<?php
/*
* Template name:  Reviews
* */

 get_header();


 ?>

<div class="fl-reviews-archive">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
<?php

    $pages = get_pages(array(
        'meta_key' => '_wp_page_template',
        'meta_value' => 'template-user-reviews.php',
    ));
    foreach($pages as $page){
        $page_id =  $page->ID;
    }
    $user_page_link = get_permalink($page_id);

    $post_id = get_the_ID();

    //Category
    $categories = get_the_terms( $post_id, tmreviews_get_post_type() . '-category' );
    $categories_html = '';


    foreach ($categories as $cat){
        if (!next( $categories )){
            $categories_html .= '<a href="' . esc_url(get_term_link($cat, 'places-taxonomy'), 'tm-reviews') . '">' . $cat->name . '</a>';
        } else {
            $categories_html .= '<a href="' . esc_url(get_term_link($cat, 'places-taxonomy'), 'tm-reviews') . ', ">' . $cat->name . '</a>';
        }
    }

    $comments = get_comments( array('post_id' => $post_id));

    $i = 1;
    $k = 1;
    $len = count($comments);

    foreach($comments as $c){

        //Comment
        $title_comment = get_comment_meta($c->comment_ID, 'tmreviews_review_title', true);
        $text_comment = $c->comment_content;
        $comment_date = get_comment_date('F, j, Y', $c->comment_ID);

        //Author
        $author_id = get_post_field( 'post_author', $post_id );
        $avatar = get_avatar($author_id);
        $author_name = get_the_author_meta( 'display_name', $author_id );

?>


    <?php if($i == 1){ ?>
    <div class="fl-row">
        <?php } ?>
        <div class="fl-places-grid col-4">
            <div class="fl-places-slide-top">
                <div class="fl-places-author-avatar">
                    <?php echo $avatar; ?>
                    </div>
                <div class="fl-places-average-meta">
                    <?php echo tmreviews_average_rating($post_id); ?>
                    <div class="fl-places-average-cat">
                        <?php echo $categories_html; ?>
                    </div>
                </div>
            </div>

            <div class="fl-places-slide-main">

                <span class="fl-review-title"><?php echo $title_comment; ?></span>
                <div class="fl-review-author-contain">
                    <span class="fl-review-author-name"><a href="<?php echo esc_url($user_page_link.'?author=' . $author_id, 'gazek'); ?>"><?php echo $author_name; ?></a></span>
                    <span class="fl-reviewed"><?php echo __('reviewed ', 'gazek'); ?></span>
                    <a href="<?php echo esc_url(get_post_permalink($post_id))?>"><?php echo get_the_title($post_id); ?></a>
                    </div>
                <span class="fl-review-text"><?php echo $text_comment; ?></span>

                </div>

            <div class="fl-places-slide-bottom">

                <a class="fl-review-button" href="<?php echo esc_url(get_post_permalink($post_id)); ?>"><?php echo __('Read review', 'tm-reviews'); ?></a>
                <span class="fl-review-date-contain"><span class="fl-review-date"><?php echo __('Review Published: ', 'tm-reviews'); ?></span><span class="fl-review-date"><?php echo $comment_date; ?> </span></span>

                </div>

            </div>
        <?php $i++;
        if($i == 4 || $k == $len){ ?>
            </div>
        <?php $i = 1;
        }
        $k++;
    }
?>
<?php endwhile; else: ?>
    <?php get_template_part('template-parts/content', 'none')?>
<?php endif; ?>
</div>

<!--Footer Start-->
<?php get_footer(); ?>
<!--Footer End-->
