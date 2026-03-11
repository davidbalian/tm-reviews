<?php get_header(); ?>
<?php
$title = gazek_get_theme_mod ('places_title');
if(!isset($title) && $title == ''){
    $title = __('All Categories Reviews', 'tm-reviews');
}

$seacrh_title = gazek_get_theme_mod ('places_title_search');
$seacrh_title_js = '"'.$seacrh_title.'"';
if(!isset($seacrh_title) && $seacrh_title == ''){
    $seacrh_title = __('Search reviews...', 'tm-reviews');
}

if(isset($_GET['s'])){
    $search_text = $_GET['s'];
} else{
    $search_text = '';
}

if(isset($_GET[tmreviews_get_post_type() . '-category']) && $_GET[tmreviews_get_post_type() . '-category'] != ''){
    $tax_name = get_term_by('slug', $_GET[tmreviews_get_post_type() . '-category'], tmreviews_get_post_type() . '-category');
}

$post_count = get_option('tmreviews_place_count', true);
if (!isset($post_count) && $post_count == ''){
    $post_count = -1;
}

$taxomony = get_terms(tmreviews_get_post_type() . '-category');



if(isset($_GET['auth_id']) && $_GET['auth_id'] != ''){

    $args = array(
        'post_type' => tmreviews_get_post_type(),
        'author' => $_GET['auth_id'],
        'post_status' => 'publish',
        'posts_per_page'		    => $post_count,
        'paged'         => ( get_query_var('paged') ? get_query_var('paged') : 1 ),
        'tax_query' => array(
            array(
                'taxonomy' => tmreviews_get_post_type() . '-category',
                'field' => 'slug',
                'terms' => $_GET[tmreviews_get_post_type() . '-category']
            )
        ),
    );

} else {
    $args = array(
        'post_type' => tmreviews_get_post_type(),
        'post_status' => 'publish',
        'posts_per_page'		    => $post_count,
        'paged'         => ( get_query_var('paged') ? get_query_var('paged') : 1 ),
        'tax_query' => array(
            array(
                'taxonomy' => tmreviews_get_post_type() . '-category',
                'field' => 'slug',
                'terms' => $_GET[tmreviews_get_post_type() . '-category']
            )
        ),
    );
}
$places = get_posts($args);

$taxonomy_html = '';
if(isset($taxomony) && !empty($taxomony)){
    $taxonomy_html .= '<select class="places-tax tmnice-select" name="tax">';

    foreach ($taxomony as $t){
        if(isset($_GET[tmreviews_get_post_type() . '-category']) && $_GET[tmreviews_get_post_type() . '-category'] == $t->slug){
            $taxonomy_html .= '<option selected value = "'.$t->slug.'">'.$t->name.'</option>';
        } else {
            $taxonomy_html .= '<option value = "'.$t->slug.'">'.$t->name.'</option>';
        }
    }
    $taxonomy_html .= '</select>';
}

$site_url = get_site_url();

$header_bg = gazek_get_theme_mod ('places_archive_page_background_img');
?>
<div class="fl-category-places-header jarallax">

    <?php echo esc_attr($header_bg) ? '<img class="jarallax-img" src="' . $header_bg . '" alt="'.$header_bg.'"/>' : ''?>

    <div class="fl-places-categories-header-meta container">
        <div class="fl-places-header-top">
            <span class="fl-places-header-text"><?php echo esc_attr($title, 'tm-reviews');?></span>
        </div>
        <?php if(isset($tax_name) && $tax_name != ''){?>
            <div class="fl-places-header-bottom">
                <span class="fl-places-in-category"><?php echo __('In Category: ', 'tm-reviews') . $tax_name->name; ?></span>
            </div>
        <?php } ?>
    </div>
</div>
<div class="fl-places-categories-search-contain">
    <div class="container">
        <?php $breadcrumbs_content = tmreviews_get_breadcrumbs(); ?>
        <div class="fl-places-search-form">
            <?php $category_template = get_site_url();?>
            <form id="searchform" action="<?php echo esc_url($category_template, 'tm-reviews');?>" method="get">
                <div class="searchform-wrap-fl">
                <input class="inlineSearch" type="text" value="<?php echo $search_text;?>" name="s" placeholder="<?php echo esc_attr($seacrh_title, 'tm-reviews');?>"/>
                <button class="inlineSubmit" type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
                    </div>
                <input type="hidden" name="post_type" value="places" />
                <?php //echo $taxonomy_html;?>
                <input type="hidden" class="places-tax" name="<?php echo tmreviews_get_post_type() . '-category';?>" value="<?php echo $_GET[tmreviews_get_post_type() . '-category'];?>">
                <select class="places-order tmnice-select" name="order">

                    <?php if(isset($_GET['order']) && $_GET['order'] == 'rate_desc'){?>
                        <option selected value = "rate_desc"><?php echo __('Ratings highest', 'tm-reviews');?></option>
                    <?php } else { ?>
                        <option  value = "rate_desc"><?php echo __('Ratings highest', 'tm-reviews');?></option>
                    <?php } ?>

                    <?php if(isset($_GET['order']) && $_GET['order'] == 'rate_asc'){?>
                        <option selected value = "rate_asc"><?php echo __('Ratings lowest', 'tm-reviews');?></option>
                    <?php } else { ?>
                        <option  value = "rate_asc"><?php echo __('Ratings lowest', 'tm-reviews');?></option>
                    <?php } ?>


                    <?php if(isset($_GET['order']) && $_GET['order'] == 'date_desc'){?>
                        <option selected value = "date_desc"><?php echo __('Date(newest first)', 'tm-reviews');?></option>
                    <?php } else { ?>
                        <option  value = "date_desc"><?php echo __('Date(newest first)', 'tm-reviews');?></option>
                    <?php } ?>

                    <?php if(isset($_GET['order']) && $_GET['order'] == 'date_asc'){?>
                        <option selected value = "date_asc"><?php echo __('Date(oldest first)', 'tm-reviews');?></option>
                    <?php } else { ?>
                        <option  value = "date_asc"><?php echo __('Date(oldest first)', 'tm-reviews');?></option>
                    <?php } ?>

                </select>
            </form>
            <script>
                jQuery.noConflict()(function($) {

                    function placesTaxChangeAction(){

                        var search_text = jQuery('.inlineSearch').val();
                        var search_tax = jQuery(".places-tax").val();
                        var search_order = jQuery(".places-order").val();
                        var newURL = location.href.split("?")[0];
                        window.location.href = newURL + "?s=" + search_text + "&post_type=" + "<?php echo tmreviews_get_post_type(); ?>" + "&<?php echo tmreviews_get_post_type() . '-category';?>=" + search_tax + "&order=" + search_order;

                    }

                    jQuery('.places-tax').change(function () {
                        placesTaxChangeAction();
                    })
                    jQuery('.places-order').change(function () {
                        placesTaxChangeAction();
                    })
                });

            </script>
        </div>
    </div>

</div>

<div class="fl-category-container container">
    <?php
    $k = 0;
    foreach ($places as $p){
        $k++;
    }
    $post_count = $k;

    ?>
    <?php $i = 0; ?>
    <?php $k = 1; ?>
    <?php foreach ($places as $p) {  ?>
        <?php
        //Reviews
        $total = 0;
        $comments = get_comments(array('post_id' => $p->ID));

        if(isset($comments) && !empty($comments)){
            $r = 0;
            foreach ($comments as $c){
                $total += intval(get_comment_meta($c->comment_ID, 'rating', true));

                $rate = get_comment_meta( $c->comment_ID, 'rating', true );
                if(isset($rate) && $rate != ''){
                    $r++;
                }
            }
            $reviews_count = $r;

            // Removed additional rating from post meta to fix average rating calculation

            if($total != 0){
                $average = $total / $reviews_count;
                $rating_icons = '';
            }else{
                $average = 0;
                $rating_icons = '';
            }

            $s = 1;
            while ($s <= intval($average)){
                $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                $s++;
            }
            if(intval($average) < 5){
                $asd = 5 - intval($average);
                $j = 1;
                while ($j <= $asd){
                    $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                    $j++;
                }
            }
        }
        //Category
        //$categories = get_the_terms( get_the_ID(), tmreviews_get_post_type() . '-category' );
        if(isset($tax_name)){
            $categories_html = '<a href="' . esc_url(get_term_link($tax_name->slug, tmreviews_get_post_type() . '-category'), 'tm-reviews') . '">' . $tax_name->name. '</a>';
        }


        //$categories_html = '';
        //foreach ($categories as $cat){
        //    if (!next( $categories )){
        //        $categories_html .= '<a href="' . esc_url(get_term_link($cat, 'places-taxonomy'), 'tm-reviews') . '">' . $cat->name . '</a>';
        //    } else {
         //       $categories_html .= '<a href="' . esc_url(get_term_link($cat, 'places-taxonomy'), 'tm-reviews') . '">' . $cat->name . ',</a>';
        //    }
        //}
        ?>
        <?php $i++;?>

        <?php if($i == 1){ ?>
            <div class="fl-cat-row">
        <?php } ?>

        <div class="fl-category-single col-md-4">
            <?php if(has_post_thumbnail($p->ID)){ ?>
                <a href="<?php echo esc_url(get_the_permalink($p->ID), 'tm-reviews');?>"><?php echo get_the_post_thumbnail($p->ID, 'gazek_size_360x250_crop');  ?></a>
            <?php } else { ?>
                <a href="<?php echo esc_url(get_the_permalink($p->ID), 'tm-reviews');?>"><img src="<?php echo esc_url(TMREVIEWS_HELPING_PREVIEW_IMAGE.'/no-image.jpg', 'tm-reviews');?>"></a>
            <?php } ?>
            <?php if(isset($rating_icons) && $rating_icons != '' && !empty($rating_icons)){?>
                <div class="fl-category-single-top">
                        <div class="fl-category-single-rating">
                                <?php echo $rating_icons; ?>
                        </div>
                    <?php if(isset($reviews_count) && $reviews_count != '' && !empty($reviews_count)){?>
                        <span class="fl-places-average"></span>
                    <?php } ?>
                    <?php if(isset($average) && $average != '' && !empty($average)){?>
                        <span class="fl-average"><?php echo __('User Rating ', 'tm-reviews');?><?php echo esc_attr(number_format($average, 1, '.', ' '), 'tm-reviews') .'/' . '5.0';?></span>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="fl-category-single-middle">
                <a class="fl-place-title" href="<?php echo esc_url(get_the_permalink($p->ID), 'tm-reviews');?>"><?php echo $p->post_title; ?></a>
                <div class="fl-places-average-cat">
                    <?php echo $categories_html;?>
                </div>
                <?php echo tmreviews_limit_excerpt_search(20, $p->post_content); //$p->post_content; ?>
            </div>
            <div class="fl-category-single-bottom">
                <?php
                //Author
                $author_id = get_post_field( 'post_author', $p->ID );
                $avatar = get_avatar($author_id, 'gazek_size_size_50x50_crop');
                $author_name = get_the_author_meta( 'display_name', $author_id );
                $author_nickname = get_the_author_meta( 'ID', $author_id );
                $page_id = get_option('tmreviews_user_reviews_page_id', true);


                if(isset($page_id) && !empty($page_id)){
                    $user_page_link = get_permalink($page_id);
                }
                $user_page_link = get_permalink($page_id);

                $author = get_user_by( 'ID', $author_id );


                $author_id = get_post_field( 'post_author', get_the_ID() );
                $page_id = get_option('tmreviews_user_reviews_page_id', true);
                if(class_exists('BuddyPress') && class_exists('Youzify')){
                    $user = get_user_by('ID', $author_id);
                    $user_page_link = get_site_url() . '/members/' . $user->user_login;
                } else {
                    $author = get_user_by( 'ID', $author_id );
                    if(isset($page_id) && !empty($page_id)){
                        $user_page_link = get_permalink($page_id) . '?author=' . $author->user_nicename;
                    }
                }
                ?>
                <a href="<?php echo esc_url($user_page_link) ?>"><?php echo $avatar;?></a>
                <span class="fl-review-author-name"><a href="<?php echo esc_url(get_term_link($tax_name->slug, tmreviews_get_post_type() . '-category') . '&auth_id=' . $author_nickname)?>"><?php echo $author_name; ?></a></span>

            </div>
        </div>
        <?php if($i == 3 || $post_count == $k){ $i=0;?>
            </div>
        <?php } ?>
        <?php $k++;?>

        <?php  } ?>
        <?php the_posts_pagination(array(
                'prev_text'    => '<i class="fa fa-angle-left" aria-hidden="true"></i>',
                'next_text'    => '<i class="fa fa-angle-right" aria-hidden="true"></i>',
            )
        ); ?>
</div>

<!--Footer Start-->
<?php get_footer(); ?>
<!--Footer End-->
