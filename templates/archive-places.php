<?php get_header(); ?>
<?php

    $title = gazek_get_theme_mod ('places_title');
    if(!isset($title) && $title == ''){
        $title = 'All Categories Reviews';
    }

    $seacrh_title = gazek_get_theme_mod ('places_title_search');
    $seacrh_title_js = '"'.$seacrh_title.'"';
    if(!isset($seacrh_title) && $seacrh_title == ''){
        $seacrh_title = esc_html__('Search reviews...', 'tm-reviews');
    }

    $header_bg = gazek_get_theme_mod ('places_archive_page_background_img');

    $taxomony = get_terms(tmreviews_get_post_type() . '-category');
    $taxonomy_html = '';
    if(isset($taxomony) && !empty($taxomony)){
       // $taxonomy_html .= '<select class="places-tax tmnice-select" name="tax">';
        $taxonomy_html .= '<select class="places-tax tmnice-select" name="' . tmreviews_get_post_type() . '-category' . '">';
        $taxonomy_html .= '<option value="all" selected>'.__('All Categories', 'tm-reviews').'</option>';

        foreach ($taxomony as $t){
            $taxonomy_html .= '<option value = "'.$t->slug.'">'.$t->name.'</option>';
        }
        $taxonomy_html .= '</select>';
    }

?>
<div class="fl-places-categories-header jarallax">
    <?php echo esc_attr($header_bg) ? '<img class="jarallax-img" src="' . $header_bg . '" alt="'.$header_bg.'"/>' : ''?>


    <div class="fl-places-categories-header-meta container">
        <div class="fl-places-header-left">
            <span class="fl-places-header-text"><?php echo esc_attr($title, 'tm-reviews');?></span>
        </div>
        <div class="fl-places-header-right">
            <?php $category_template = get_site_url();?>
            <form id="searchform" action="<?php echo esc_url($category_template, 'tm-reviews');?>" method="get">
                <input class="inlineSearch" type="text" name="s" placeholder="<?php echo esc_attr($seacrh_title, 'tm-reviews');?>"/>
                <?php echo $taxonomy_html;?>
                <input type="hidden" name="post_type" value="places" />
                <button class="inlineSubmit" type="submit"><i class="fa fa-search" aria-hidden="true"></i></button>
            </form>
        </div>
    </div>
</div>
<div class="fl-places-categories-breadcrumbs">
    <div class="container">
        <?php $breadcrumbs_content = tmreviews_get_breadcrumbs(); ?>
    </div>
</div>



<div class="fl-places-categories-header-meta-mobile container">
        
        <div class="fl-places-header-right">
            <?php $category_template = get_site_url();?>
            <form id="searchform" action="<?php echo esc_url($category_template, 'tm-reviews');?>" method="get">
                <input class="inlineSearch" type="text" name="s" placeholder="<?php echo esc_attr($seacrh_title, 'tm-reviews');?>"/>
                <?php echo $taxonomy_html;?>
                <input type="hidden" name="post_type" value="places" />
                <button class="inlineSubmit" type="submit"></button>
            </form>
        </div>
    </div>         



<div class="fl-places-categories container">
    <?php
    $args = array(
        'taxonomy' => tmreviews_get_post_type() . '-category',
    );
    $i = 1;
    $k = 1;
    $cat_par_count = 0;
    $cats = get_categories($args);
    foreach ($cats as $c){
        if(isset($c->category_parent) && $c->category_parent == 0){
            $cat_par_count++;
        }
    }

    if(isset($cats) && !empty($cats)){
        foreach ($cats as $cat){ ?>
            <?php if(isset($cat->category_parent) && $cat->category_parent == 0){ ?>
                <?php if($i == 1){ ?>
                    <div class="fl-cat-row ">
                <?php } ?>
                <div class="fl-category-single col-4">
                    <?php $cat_parent_id = $cat->term_id;?>
                    <?php
                    $icon_css = get_field('cat_icon', $cat);

                    $url = $icon_css["url"];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
                    $svg = curl_exec($ch);
                    curl_close($ch);

                    if (isset($icon_css["url"] ) && $icon_css["url"]  != ''){
                        if($icon_css['mime_type'] == 'image/svg+xml'){
                            $icon = '<a class="place_cat_link" href="'. get_category_link( $cat->term_id ) . '"><span class="fl-icon-contain">' . $svg . '</span></a>';
                        } elseif ($icon_css['mime_type'] == 'image/png' or $icon_css['mime_type'] == 'image/jpg' or $icon_css['mime_type'] == 'image/jpeg'){
                            $icon = '<a  class="place_cat_link" href="' . get_category_link( $cat->term_id ) .'"><span class="fl-icon-contain"><img src="' . esc_url( $icon_css["url"], "tm-reviews" ). '"/></span></a>';
                        }
                    } else {
                        $icon = '';
                    }
                    if(isset($icon) && $icon != ''){
                        echo $icon;
                    }
                    ?>

                    <a href="<?php echo get_category_link( $cat->term_id ) ?>"><span class="fl-places-categories-title"><?php echo $cat->name; ?></span></a>
                    <?php
                    $args_sub = array('taxonomy' => tmreviews_get_post_type() . '-category', 'parent' => $cat_parent_id);
                    $sub_cats = get_categories( $args_sub );
                    foreach ($sub_cats as $sub_cat){
                        ?>
                        <a href="<?php echo get_category_link( $sub_cat->term_id ) ?>">
                            <span class="fl-cat-post-name"><?php echo $sub_cat->name; ?></span>
                            <span class="fl-cat-post-count"><?php echo '('.$sub_cat->count.')'; ?></span>
                        </a>
                    <?php } ?>
                </div>
                <?php if($i == 3 || $cat_par_count == $k ){ ?>
                    <?php $i = 0;?>
                    </div>
                <?php } ?>
                <?php $i++; $k++; ?>
            <?php } ?>
        <?php } ?>
    <?php } else { ?>
        <?php echo __('Please, add some categories for Places post type', 'tm-reviews'); ?>
    <?php } ?>

</div>
<!--Footer Start-->
<?php get_footer(); ?>
<!--Footer End-->
