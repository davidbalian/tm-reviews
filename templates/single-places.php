<?php get_header();

/////Header
//Background Image
$bg_img = tmreviews_get_mod('place_bg', true);
if(isset($bg_img) && $bg_img != ''){
    if ( is_array($bg_img) ) {
        $bg_img_url = $bg_img['url'];
    } elseif ( is_numeric($bg_img) ) {
        $bg_img_url = wp_get_attachment_url($bg_img);
    } else {
        $bg_img_url = $bg_img;
    }
    $places_bg_style = 'style=background:url(' . $bg_img_url .')';
} else {
    $places_bg_style = '';
}

//Background Overlay Color
$bg_cl = tmreviews_get_mod('place_bg_cl', true);
if(isset($bg_cl) && $bg_cl != ''){
    $places_bg_cl = 'style=background-color:' . $bg_cl;
} else {
    $places_bg_cl = '';
}

//Logo
$logo = tmreviews_get_mod('place_logo', true);
if(isset($logo) && $logo != ''){
    if ( is_array($logo) ) {
        $logo_title = isset($logo['title']) ? $logo['title'] : '';
        $logo_url   = isset($logo['url'])   ? $logo['url']   : '';
    } elseif ( is_numeric($logo) ) {
        $logo_title = get_the_title($logo);
        $logo_url   = wp_get_attachment_url($logo);
    } else {
        $logo_title = '';
        $logo_url   = $logo;
    }
    $places_logo_img = '<img class="fl-places-logo" alt="'.esc_attr($logo_title).'" src="'.esc_url($logo_url).'">';
} else {
    $places_logo_img = '';
}

//Reviews
$total = 0;
$comments = get_comments(array('post_id' => get_the_ID(), 'status'              => '1'));
$reviews_count = 0;
$average = '';

$sas = get_post_meta(get_the_ID(), 'review_rating', true);


if(isset($comments) && !empty($comments)){
    $i = 0;

    foreach ($comments as $c){
        $total += intval(get_comment_meta($c->comment_ID, 'rating', true));

        $rate = get_comment_meta( $c->comment_ID, 'rating', true );
        if(isset($rate) && $rate != ''){
            $i++;
        }
    }
    if(get_post_meta(get_the_ID(), 'review_rating', true) != '' && get_post_meta(get_the_ID(), 'review_rating', true) != '0' && get_post_meta(get_the_ID(), 'review_rating', true) != 0){
        $total += intval(get_post_meta(get_the_ID(), 'review_rating', true));

    }


    $reviews_count = $i;
    if(get_post_meta(get_the_ID(), 'review_rating', true) != '' && get_post_meta(get_the_ID(), 'review_rating', true) != '0' && get_post_meta(get_the_ID(), 'review_rating', true) != 0){
        $reviews_count++;
    }

    if($total != 0){
        $average = $total / $reviews_count;
        $rating_icons = '';
    }else{
        $average = 0;
        $rating_icons = '';
    }

    $i = 1;
    while ($i <= intval($average)){
        $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
        $i++;
    }
    if(intval($average) < 5){
        $asd = 5 - intval($average);
        $k = 1;
        while ($k <= $asd){
            $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
            $k++;
        }
    }
} else {
    if(get_post_meta(get_the_ID(), 'review_rating', true) != '' && get_post_meta(get_the_ID(), 'review_rating', true) != '0' && get_post_meta(get_the_ID(), 'review_rating', true) != 0){
        $total += intval(get_post_meta(get_the_ID(), 'review_rating', true));
        $reviews_count++;

        if($total != 0){
            $average = $total / $reviews_count;
            $rating_icons = '';
        } else {
            $average = 0;
            $rating_icons = '';
        }

        $as = 1;
        while ($as <= intval($average)){
            $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
            $as++;
        }
        if(intval($average) < 5){
            $asds = 5 - intval($average);
            $ka = 1;
            while ($ka <= $asds){
                $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                $ka++;
            }
        }
    }
}

$author_id = get_post_field( 'post_author', get_the_ID() );
$author_email = get_the_author_meta('user_email', $author_id);
$tmreviews_off_page = get_option('tmreviews_off_page');
//Subtitle
$subtitle = tmreviews_get_mod('place_subtitle', true);

//Address
$address = tmreviews_get_mod('place_address', true);
if ( is_array($address) ) {
    $long = isset($address['lng'])  ? $address['lng']  : '';
    $lat  = isset($address['lat'])  ? $address['lat']  : '';
    $zoom = isset($address['zoom']) ? $address['zoom'] : '';
} else {
    $long = '';
    $lat  = '';
    $zoom = '';
}

//Phone
$phone = tmreviews_get_mod('place_phone', true);

//Email
$email = tmreviews_get_mod('place_email', true);

//Website
$website = tmreviews_get_mod('place_website', true);

//socials
$socials = tmreviews_get_mod('socials', true);

//gallery
$gallery = tmreviews_get_mod('place_gallery', true);

//New meta fields
$place_founded = get_post_meta(get_the_ID(), 'place_founded', true);
$place_regulation = get_post_meta(get_the_ID(), 'place_regulation', true);
$highlight_1 = get_post_meta(get_the_ID(), 'highlight_1', true);
$highlight_2 = get_post_meta(get_the_ID(), 'highlight_2', true);
$highlight_3 = get_post_meta(get_the_ID(), 'highlight_3', true);
$highlight_4 = get_post_meta(get_the_ID(), 'highlight_4', true);
$highlight_5 = get_post_meta(get_the_ID(), 'highlight_5', true);

if (isset($gallery) && !empty($gallery)){
    $gallery_html = '';
    $gallery_html .= '<div class="fl-place-gallery-slider">';
    foreach ($gallery as $g){
        $gallery_html .= '<img alt="tmreviews-slider-img" src="' . esc_url(wp_get_attachment_image_url($g, 'gazek_size_730x470_crop'), 'tm-reviews') . '">';
    }

    $gallery_html .= '</div>';
}


?>

<div class="fl-places-header jarallax" <?php echo esc_attr($places_bg_style, 'tm-reviews');?> >
    <div class="fl-places-header-overlay" <?php echo esc_attr($places_bg_cl, 'tm-reviews');?>></div>
    <div class="container">
        <div class="fl-places-left">
            <div class="fl-places-logo-title-wrapper">
                <div class="fl-places-logo-contain">
                    <span class="fl-places-logo-helper"></span>
                    <?php echo $places_logo_img;?>
                </div>
                <div class="fl-places-title-contain">
                    <?php if(isset($subtitle) && $subtitle != ''){ ?>
                        <span class="fl-places-subtitle"><?php echo $subtitle;?></span>
                    <?php } ?>

                    <h1 class="fl-places-title"><?php the_title();?></h1>

                    <?php if(isset($address['address']) && $address['address'] != ''){ ?>
                        <span class="fl-places-show"><i class="fa fa-map-marker" aria-hidden="true"></i><?php echo $address['address'];?></span>
                    <?php } ?>

                    <?php
                    $official = tmreviews_get_mod('official', true);
                    if(isset($tmreviews_off_page) && $tmreviews_off_page == 'enable'){
                        if($official == 'yes'){ ?>
                            <span class="fl-places-verified"><i class="fa fa-check-circle" aria-hidden="true"></i><?php echo __('Official Page', 'tm-reviews')?></span>
                        <?php }
                    } ?>

                </div>
            </div>
            
            <div class="additional-info">
                <?php if(isset($place_founded) && $place_founded != ''){ ?>
                    <div class="fl-places-meta-field">
                        <span class="fl-places-meta-label"><?php echo __('Founded: ', 'tm-reviews');?></span>
                        <span class="fl-places-meta-value"><?php echo esc_html($place_founded);?></span>
                    </div>
                <?php } ?>
                
                <?php if(isset($place_regulation) && $place_regulation != ''){ ?>
                    <div class="fl-places-meta-field">
                        <span class="fl-places-meta-label"><?php echo __('Regulation: ', 'tm-reviews');?></span>
                        <span class="fl-places-meta-value"><?php echo esc_html($place_regulation);?></span>
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="fl-places-right">
            <div class="fl-places-average-meta">
                <?php if(isset($rating_icons) && $rating_icons != '' && !empty($rating_icons)){?>
                    <?php echo $rating_icons; ?>
                <?php } ?>
                <?php
                if(isset($reviews_count) && $reviews_count != '' && !empty($reviews_count)){?>
                    <span class="fl-places-average"><?php echo __('Based on ', 'tm-reviews') . $reviews_count. __(' reviews', 'tm-reviews');?></span>
                <?php } ?>
            </div>
            <?php if(isset($average) && $average != '' && !empty($average)){?>
                <span class="fl-average"><?php echo esc_attr(number_format($average, 1, '.', ' '), 'tm-reviews');?></span>
            <?php } ?>
            
            <?php
            // Sharing block - displayed only if AddToAny plugin is activated
            // (Блок шаринга - отображается только если плагин AddToAny активирован)
            $addtoany_active = false;
            
            // Check through the list of active plugins
            // (Проверка через список активных плагинов)
            if (!function_exists('is_plugin_active')) {
                include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }
            
            if (function_exists('is_plugin_active')) {
                $addtoany_active = is_plugin_active('add-to-any/add-to-any.php');
            }
            
            // If the plugin is active, show the sharing block
            // (Если плагин активен, показываем блок шаринга)
            if ($addtoany_active): 
            ?>
            <div class="tmr-share-contain tmr-share-contain-top-single">
                <?php
                // Direct output of AddToAny shortcode
                // (Прямой вывод шорткода AddToAny)
                echo do_shortcode('[addtoany]'); 
                ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>



<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <?php
    $status = get_post_status();
    if($status == 'draft'){ ?>
        <div class="fl-single-place-notice container">
            <?php echo '<span>'. __('Your place is in moderation.', 'tm-reviews') . '</span>'; ?>
        </div>
    <?php } ?>

    <?php $author_id = get_the_author_meta('ID');
    $add = get_option('tmreviews_add_place', true);
    if(isset($add) && $add != '' && is_string($add) && $author_id == get_current_user_ID() && $author_id != 0 && get_current_user_ID() != 0){ ?>
        <div class="fl-single-place-notice container">
            <?php echo '<span>'. __('This is your page, you can edit it ', 'tm-reviews') . '<a href="' . get_the_permalink($add) . '?id=' . get_the_ID() . '">' . __('here', 'tm-reviews') .'</a></span>'; ?>
        </div>
    <?php } ?>

    <div class="fl-places-content container">
        <div class="fl-places-reviews-contain col-md-8">
            <div class="fl-places-gallery">

                <?php if(isset($gallery_html) && $gallery_html != ''){
                    echo $gallery_html;
                }?>

                <script>
                    jQuery.noConflict()(function($) {
                        jQuery(".fl-place-gallery-slider").slick({
                            dots: false,
                            speed: 300,
                            slidesToShow: 1,
                            //centerMode: true,
                            arrows: true,
                            nextArrow: '<i class="fa fa-angle-right" aria-hidden="true"></i>',
                            prevArrow: '<i class="fa fa-angle-left" aria-hidden="true"></i>',
                        });
                    });
                </script>
            </div>
            <?php comments_template(); ?>
        </div>
        <div class="fl-places-sidebar-contain col-md-4 sticky">

            <div class="fl-places-map-contain">
                <?php
                $api_key = gazek_get_theme_mod('google_api_key');
                if(isset($api_key) && $api_key != ''){
                    tmreviews_gmap_print();
                    $result_map = '';
                    if(isset($address['address']) && $address['address'] !='' && isset($zoom) && $zoom != ''){
                        $result_map .= '<script>
                            jQuery.noConflict()(function ($) {
                                // Проверяем загрузку Google Maps
                                if (typeof google === "undefined" || typeof google.maps === "undefined") {
                                    console.error("Google Maps не загружен. Проверьте API ключ.");
                                    $(".fl-places-map-contain").hide();
                                    return;
                                }

                                $("#fl-places-map").gmap3({
                                    marker: {
                                        address: "'.$address['address'].'"
                                    },
                                    map: {
                                        options: {
                                            zoom:'.$zoom.',
                                            draggable: true,
                                            mapTypeControl: true
                                        }
                                    }
                                });

                                // Проверяем ошибки загрузки карты
                                window.gm_authFailure = function() {
                                    console.error("Ошибка авторизации Google Maps API. Проверьте API ключ.");
                                    $(".fl-places-map-contain").hide();
                                };
                            });
                            </script>';?>
                        <div id="fl-places-map">
                            <?php echo $result_map;?>
                        </div>
                    <?php }
                }
                ?>
            </div>
            <?php
            $affiliate_link = tmreviews_get_mod('affiliate_link', true);
            $affiliate_link_text = tmreviews_get_mod('affiliate_link_txt', true);
            if(!isset($affiliate_link_text)){
                $affiliate_link_text = __('Affiliate Link', 'tm-reviews');
            }

            if(isset($affiliate_link_text) && $affiliate_link_text == ''){
                $affiliate_link_text = __('Affiliate Link', 'tm-reviews');
            }
            $video = tmreviews_get_mod('video_link', true); ?>
            <div class="fl-additional-info">

                <?php if(isset($video) && $video != ''){
                    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $video, $match);
                    $youtube_id = $match[1];
                    ?>
                    <iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo $youtube_id;?>" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen>
                    </iframe>
                <?php } ?>
                <?php if(isset($affiliate_link) && $affiliate_link != ''){ ?>
                    <span class="fl-affilate-link" data-hrf="<?php echo esc_url($affiliate_link)?>"><?php echo $affiliate_link_text;?></span>
                <?php } ?>

            </div>

            <div class="fl-places-meta">
                <h2 class="fl-places-meta-title"><?php the_title();?></h2>
                <div class="fl-places-meta-content"><?php the_content();?></div>
                <?php
                $reviews = get_comments(
                    array(
                        'post_id' => get_the_ID(),
                        'status' => 'approve',
                        'meta_query' => array(
                            'AND',
                            array(
                                'key' => 'tmreviews_review_pros',
                                'compare' => 'EXISTS'
                            ),
                            array(
                                'key' => 'tmreviews_review_cons',
                                'compare' => 'EXISTS'
                            ),
                        )
                    )
                );
                ?>
                <?php $areview = tmreviews_get_theme_mod('review', true);


                $review_review_pros = intval(get_post_meta(get_the_ID(), 'review_review_pros', true));
                $review_review_cons = intval(get_post_meta(get_the_ID(), 'review_review_cons', true));


                ?>
                <?php if(!empty($reviews)):?>
                    <div class="fl-places-pros-cons-content">
                        <div class="tmreviews_review_pros_contain">
                            <?php $f = 0; while ($f <= $review_review_pros) {
                                if (get_post_meta(get_the_ID(), 'review_review_pros_' . $f .'_tmreviews_review_pros', true) != '') { ?>
                                    <div class="tmreviews_review_pros">
                                        <?php echo '<span class="fl-pros-content">'. get_post_meta(get_the_ID(), 'review_review_pros_' . $f .'_tmreviews_review_pros', true).'</span>';?>
                                    </div>
                                <?php } ?>
                                <?php $f++; }  ?>

                            <?php foreach ($reviews as $r) {
                                $pros =  get_comment_meta($r->comment_ID, 'tmreviews_review_pros', true);
                                if(isset($pros) && !empty($pros)){ ?>
                                    <?php foreach ($pros as $p){
                                        if ($p != '') { ?>
                                            <div class="tmreviews_review_pros">
                                                <?php echo '<span class="fl-pros-content">'.$p.'</span>';?>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                <?php }
                            } ?>
                        </div>
                        <div class="tmreviews_review_cons_contain">

                            <?php $sc = 0; while ($sc <= $review_review_cons) {
                                if (get_post_meta(get_the_ID(), 'review_review_cons_' . $sc .'_tmreviews_review_cons', true) != '') { ?>
                                    <div class="tmreviews_review_cons">
                                        <?php echo '<span class="fl-cons-content">'. get_post_meta(get_the_ID(), 'review_review_cons_' . $sc .'_tmreviews_review_cons', true).'</span>';?>
                                    </div>
                                <?php } ?>
                                <?php $sc++; }  ?>

                            <?php foreach ($reviews as $r) {
                                $cons =  get_comment_meta($r->comment_ID, 'tmreviews_review_cons', true);
                                if(isset($cons) && !empty($cons)){ ?>
                                    <?php foreach ($cons as $c){ ?>
                                        <?php if ($c != ''){ ?>
                                            <div class="tmreviews_review_cons">
                                                <?php echo '<span class="fl-cons-content">'.$c.'</span>';?>
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                <?php }
                            } ?>
                        </div>
                    </div>
                <?php else:?>
                    <div class="fl-places-pros-cons-content">
                        <div class="tmreviews_review_pros_contain">
                            <?php
                            $f = 0; while ($f <= $review_review_pros) {
                                if (get_post_meta(get_the_ID(), 'review_review_pros_' . $f .'_tmreviews_review_pros', true) != '') { ?>
                                    <div class="tmreviews_review_pros">
                                        <?php echo '<span class="fl-pros-content">'. get_post_meta(get_the_ID(), 'review_review_pros_' . $f .'_tmreviews_review_pros', true).'</span>';?>
                                    </div>
                                <?php } ?>
                                <?php $f++; }  ?>
                        </div>
                        <div class="tmreviews_review_cons_contain">
                            <?php $sc = 0; while ($sc <= $review_review_cons) {
                                if (get_post_meta(get_the_ID(), 'review_review_cons_' . $sc .'_tmreviews_review_cons', true) != '') { ?>
                                    <div class="tmreviews_review_cons">
                                        <?php echo '<span class="fl-cons-content">'. get_post_meta(get_the_ID(), 'review_review_cons_' . $sc .'_tmreviews_review_cons', true).'</span>';?>
                                    </div>
                                <?php } ?>
                                <?php $sc++; }  ?>
                        </div>
                    </div>
                <?php endif;?>


                <?php $empl = tmreviews_get_mod('employers', true);?>

                <?php if(isset($empl) && !empty($empl)){ ?>
                    <div class="fl-place-employers-wrap">
                        <h3 class="place_meta_empl_title"><?php echo __('Employees Rating', 'tm-reviews');?></h3>

                        <?php foreach($empl as $e){ ?>

                            <div class="fl-empl">



                                <div class="fl-emplbox1">

                                    <?php if(isset($e['empl_img']['url']) && $e['empl_img']['url'] != ''){ ?>
                                        <img src="<?php echo esc_url($e['empl_img']['url']);?>"/>
                                    <?php } ?>

                                </div>


                                <div class="fl-emplbox1">

                                    <?php if(isset($e['empl_name']) && $e['empl_name'] != ''){ ?>
                                        <span class="fl-empl-name"><?php echo $e['empl_name'];?></span>

                                        <?php
                                        $comments_check = get_comments(array(
                                            'post_id' => get_the_ID(),
                                            'status' => 'approve',
                                            'type' => 'comment',
                                            'meta_key' => 'employer',
                                            'meta_value' => $e['empl_name'],
                                        ));
                                        if(isset($comments_check) && !empty($comments_check)){
                                            $r = 0;
                                            $total    = 0;
                                            $average = 0;
                                            foreach ($comments_check as $com){
                                                $rating = get_comment_meta($com->comment_ID, 'rating', true);
                                                $total += intval($rating);
                                                $r++;
                                            }
                                            $reviews_count = $r;


                                            if ( $total != 0 ) {
                                                $average      = $total / $reviews_count;
                                                $rating_icons = '';
                                            } else {
                                                $average      = 0;
                                                $rating_icons = '';
                                            }
                                            $s = 1;
                                            while ( $s <= intval( $average ) ) {
                                                $rating_icons .= '<i class="fa fa-star" aria-hidden="true"></i>';
                                                $s ++;
                                            }
                                            if ( intval( $average ) < 5 ) {
                                                $asd = 5 - intval( $average );
                                                $j   = 1;
                                                while ( $j <= $asd ) {
                                                    $rating_icons .= '<i class="fa fa-star-o" aria-hidden="true"></i>';
                                                    $j ++;
                                                }
                                            }
                                        }
                                        ?>
                                    <?php } ?>
                                    <?php if(isset($e['empl_position']) && $e['empl_position'] != ''){ ?>
                                        <span class="fl-empl-pos"><?php echo $e['empl_position'];?></span>
                                    <?php } ?>
                                    <?php if(isset($rating_icons) && $rating_icons != ''){ ?>
                                        <span class="fl-empl-rating">
                                    <span class="fl-empl-average">
                                        <?php $total = intval(get_post_meta(get_the_ID(), 'review_rating', true)); ?>
                                        <?php echo __('Rating ', 'tm-reviews').number_format($average, 1, '.', ' '). '/5.0'; ?>
                                    </span>
                                    <span class="fl-empl-stars">
                                        <?php echo $rating_icons; $rating_icons = '';?>
                                    </span>
                                </span>
                                    <?php } ?>

                                </div>
                            </div>


                        <?php } ?>

                    </div>
                <?php } ?>

                <?php if(class_exists('WPCF7')){
                    $tmreviews_empl_form = get_option('tmreviews_empl_form');
                    $tmreviews_claim_form = get_option('tmreviews_claim_form');
                    ?>
                    <?php if(isset($tmreviews_empl_form) && $tmreviews_empl_form != '' && $tmreviews_empl_form != 'disable'){
                        ?>
                        <div class="fl-places-employer-form">
                            <a class="fl-empl-title"  href="#fl-empl-form" data-uk-toggle><?php echo __('Also work here? ', 'tm-reviews');?></a>
                            <div class="fl-empl-form" id="fl-empl-form" data-uk-modal>
                                <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
                                    <button class="uk-modal-close-default" type="button" data-uk-close></button>
                                    <?php echo do_shortcode('[contact-form-7 id="' . $tmreviews_empl_form .'"]');?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>



                <?php if(isset($address['address']) && $address['address'] != ''){ ?>
                    <span class="fl-places-meta-address-title"><?php echo __('Address: ', 'tm-reviews');?></span>
                    <span class="fl-places-meta-address"><?php echo $address['address'];?></span>
                <?php } ?>
                <?php if(isset($phone) && $phone != ''){ ?>
                    <span class="fl-places-meta-phone-title"><?php echo __('Phone: ', 'tm-reviews');?></span>
                    <span class="fl-places-meta-phone"><?php echo $phone;?></span>
                <?php } ?>
                <?php if(isset($email) && $email != ''){ ?>
                    <span class="fl-places-meta-email-title"><?php echo __('Email: ', 'tm-reviews');?></span>
                    <span class="fl-places-meta-email"><?php echo $email;?></span>
                <?php } ?>
                <?php if(isset($website) && $website != ''){ ?>
                    <span class="fl-places-meta-website-title"><?php echo __('Website: ', 'tm-reviews');?></span>
                    <span class="fl-places-meta-website"><?php echo $website;?></span>
                <?php } ?>

                <?php 
                $highlights = array();
                if(isset($highlight_1) && $highlight_1 != ''){ $highlights[] = $highlight_1; }
                if(isset($highlight_2) && $highlight_2 != ''){ $highlights[] = $highlight_2; }
                if(isset($highlight_3) && $highlight_3 != ''){ $highlights[] = $highlight_3; }
                if(isset($highlight_4) && $highlight_4 != ''){ $highlights[] = $highlight_4; }
                if(isset($highlight_5) && $highlight_5 != ''){ $highlights[] = $highlight_5; }
                
                if(!empty($highlights)){ ?>
                    <div class="fl-places-highlights-section">
                        <h6 class="fl-places-highlights-title"><?php echo __('Highlights', 'tm-reviews');?></h6>
                        <div class="fl-places-highlights">
                            <?php foreach($highlights as $highlight){ ?>
                                <div class="fl-places-highlight-item">
                                    <?php echo wpautop(esc_html($highlight));?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                <?php } ?>

                <?php if(isset($socials) && !empty($socials)){ ?>
                    <div class="fl-places-socials-conianer">
                        <?php if(isset($socials['facebook']) && $socials['facebook'] != ''){ ?>
                            <span class="fl-places-social"><a href="<?php echo esc_url($socials['facebook'], 'tm-reviews')?>"><i class="fa fa-facebook" aria-hidden="true"></i></a></span>
                        <?php } ?>
                        <?php if(isset($socials['twitter']) && $socials['twitter'] != ''){ ?>
                            <span class="fl-places-social"><a href="<?php echo esc_url($socials['twitter'], 'tm-reviews')?>"><i class="fa fa-twitter" aria-hidden="true"></i></a></span>
                        <?php } ?>
                        <?php if(isset($socials['dribble']) && $socials['dribble'] != ''){ ?>
                            <span class="fl-places-social"><a href="<?php echo esc_url($socials['dribble'], 'tm-reviews')?>"><i class="fa fa-dribbble" aria-hidden="true"></i></a></span>
                        <?php } ?>
                        <?php if(isset($socials['instagram']) && $socials['instagram'] != ''){ ?>
                            <span class="fl-places-social"><a href="<?php echo esc_url($socials['instagram'], 'tm-reviews')?>"><i class="fa fa-instagram" aria-hidden="true"></i></a></span>
                        <?php } ?>
                        <?php if(isset($socials['linkedin']) && $socials['linkedin'] != ''){ ?>
                            <span class="fl-places-social"><a href="<?php echo esc_url($socials['linkedin'], 'tm-reviews')?>"><i class="fa fa-linkedin" aria-hidden="true"></i></a></span>
                        <?php } ?>
                        <?php if(isset($socials['behance']) && $socials['behance'] != ''){ ?>
                            <span class="fl-places-social"><a href="<?php echo esc_url($socials['behance'], 'tm-reviews')?>"><i class="fa fa-behance" aria-hidden="true"></i></a></span>
                        <?php } ?>
                    </div>
                <?php } ?>



                <?php if(class_exists('WPCF7')){
                    $tmreviews_empl_form = get_option('tmreviews_empl_form');
                    $tmreviews_claim_form = get_option('tmreviews_claim_form');
                    ?>

                    <?php if(isset($tmreviews_claim_form) && $tmreviews_claim_form != '' && $tmreviews_claim_form != 'disable'){ ?>
                        <div class="fl-places-claim-form">
                            <a class="fl-claim-title" href="#fl-claim-form" data-uk-toggle><?php echo __('Claim Request', 'tm-reviews');?></a>
                            <div class="fl-claim-form" id="fl-claim-form" data-uk-modal>
                                <div class="uk-modal-dialog uk-modal-body uk-margin-auto-vertical">
                                    <button class="uk-modal-close-default" type="button" data-uk-close></button>
                                    <?php echo do_shortcode('[contact-form-7 id="' . $tmreviews_claim_form .'"]');?>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                <?php } ?>

            </div>


            <script>
                jQuery(document).ready(function() {
                    jQuery('.sticky')
                        .theiaStickySidebar({
                            additionalMarginTop: 30
                        });
                });
            </script>

        </div></div>
<?php endwhile; else: ?>
    <?php get_template_part('template-parts/content', 'none')?>
<?php endif; ?>

<!--Footer Start-->
<?php get_footer(); ?>
<!--Footer End-->