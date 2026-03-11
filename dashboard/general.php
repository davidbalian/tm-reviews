<style>
/* Инлайн-стили для страницы настроек places (Inline styles for places settings page) */
.tmreview-contain {
    background-color: #fff;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    padding: 20px;
    max-width: 800px;
    margin-top: 20px;
    border: 1px solid #e5e5e5;
}

.tmreview-contain h2 {
    font-size: 23px;
    margin-bottom: 15px;
    color: #23282d;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
    font-weight: 400;
}

.tmreviews-option-group {
    background-color: #fafafa;
    border-left: 4px solid #0073aa;
    padding: 15px;
    margin-bottom: 15px;
    border-radius: 0 2px 2px 0;
}

.tmreviews-option-group h3 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 14px;
    color: #23282d;
    font-weight: 600;
}

.tmreviews-form .form_options {
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f1f1f1;
}

.tmreviews-form .form_options:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.tmreviews-form label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #23282d;
    font-size: 14px;
}

.tmreviews-form input[type="text"],
.tmreviews-form input[type="number"],
.tmreviews-form select,
.tmreviews-form textarea {
    width: 25em;
    max-width: 100%;
    padding: 5px 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
    background-color: #fff;
    color: #32373c;
    box-shadow: inset 0 1px 2px rgba(0,0,0,.07);
}

.tmreviews-form input[type="text"]:focus,
.tmreviews-form input[type="number"]:focus,
.tmreviews-form select:focus,
.tmreviews-form textarea:focus {
    border-color: #5b9dd9;
    box-shadow: 0 0 2px rgba(30,140,190,.8);
    outline: none;
}

.tmreviews-form button,
.tmreviews-form input[type="submit"] {
    background: #0085ba;
    border: 1px solid #006799;
    color: #fff;
    padding: 0 10px 1px;
    font-size: 13px;
    line-height: 28px;
    height: 30px;
    border-radius: 3px;
    cursor: pointer;
    text-decoration: none;
    white-space: nowrap;
    box-shadow: 0 1px 0 #006799;
    text-shadow: 0 -1px 1px #006799, 1px 0 1px #006799, 0 1px 1px #006799, -1px 0 1px #006799;
}

.tmreviews-form button:hover,
.tmreviews-form input[type="submit"]:hover {
    background: #008ec2;
    border-color: #006799;
    color: #fff;
}

.tmreviews-form select {
    padding-right: 25px;
    background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E');
    background-repeat: no-repeat;
    background-position: right 5px top 55%;
    background-size: 16px 16px;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
}
</style>

<h2><?php echo esc_html__('Settings', 'tmreviews');?></h2>

<div class="tmreview-contain">
    <form method="post" class="tmreviews-form">

        <div class="tmreviews-option-group">
            <h3><?php echo esc_html__('General Settings', 'tmreviews'); ?></h3>
            
            <div class="form_options">
                <label><?php echo esc_html__('Change post type name /places/ on your own (after change it need resave permalink)', 'tmreviews'); ?></label>
                <?php $tmreviews_post_type = get_option('tmreviews_post_type', true);?>
                <input type="text" placeholder="<?php echo esc_attr__('Post type name', 'tmreviews'); ?>" name="tmreviews_post_type" value="<?php echo esc_attr($tmreviews_post_type['new']);?>"/>
            </div>
        </div>

        <div class="tmreviews-option-group">
            <h3><?php echo esc_html__('Page Settings', 'tmreviews'); ?></h3>

            <div class="form_options">
                <label><?php echo esc_html__('Choose the page that displays customer reviews.', 'tmreviews'); ?></label>
                <select name="tmreviews_user_reviews_page_id">
                    <?php
                    $user_reviews_option = get_option('tmreviews_user_reviews_page_id', true);
                    $add_place_option = get_option('tmreviews_add_place_page_id', true);
                    $place_count = get_option('tmreviews_place_count', true);
                    $tmreviews_place_membership_on = get_option('tmreviews_place_membership_on', true);
                    $tmreviews_place_membership_link = get_option('tmreviews_place_membership_link', true);

                    if (!isset($tmreviews_place_membership_link)){
                        $tmreviews_place_membership_link = '';
                    }

                    $pages = get_pages(
                        array(
                            'numberposts' => -1,
                        )
                    );

                    if ( $pages ) {
                        foreach ( $pages as $page ) {

                            if($user_reviews_option == $page->ID){
                                ?>
                                <option value="<?php echo esc_attr($page->ID);?>" selected><?php echo esc_attr($page->post_title);?></option>
                                <?php
                            } else {
                                ?>
                                <option value="<?php echo esc_attr($page->ID);?>"><?php echo esc_attr($page->post_title);?></option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
            </div>


            <div class="form_options">
                <label><?php echo esc_html__('Choose the page that displays plans.', 'tmreviews'); ?></label>
                <select name="tmreviews_plans_page_id">
                    <?php
                    $tmreviews_plans_page_id= get_option('tmreviews_plans_page_id', true);
                    $pages = get_pages(
                        array(
                            'numberposts' => -1,
                        )
                    );

                    if ( $pages ) {
                        foreach ( $pages as $page ) {
                            if($tmreviews_plans_page_id == $page->ID){
                                ?>
                                <option value="<?php echo esc_attr($page->ID);?>" selected><?php echo esc_attr($page->post_title);?></option>
                                <?php
                            } else { ?>
                                <option value="<?php echo esc_attr($page->ID);?>"><?php echo esc_attr($page->post_title);?></option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
            </div>


            <div class="form_options">
                <label><?php echo esc_html__('Add Items Page', 'tmreviews'); ?></label>
                <select name="tmreviews_add_place">
                    <?php
                    $tmreviews_add_place= get_option('tmreviews_add_place', true);
                    $pages = get_pages(
                        array(
                            'numberposts' => -1,
                        )
                    );

                    if ( $pages ) {
                        foreach ( $pages as $page ) {
                            if($tmreviews_add_place == $page->ID){
                                ?>
                                <option value="<?php echo esc_attr($page->ID);?>" selected><?php echo esc_attr($page->post_title);?></option>
                                <?php
                            } else { ?>
                                <option value="<?php echo esc_attr($page->ID);?>"><?php echo esc_attr($page->post_title);?></option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('My Items Page', 'tmreviews'); ?></label>
                <select name="tmreviews_my_place">
                    <?php
                    $tmreviews_my_place= get_option('tmreviews_my_place', true);
                    $pages = get_pages(
                        array(
                            'numberposts' => -1,
                        )
                    );

                    if ( $pages ) {
                        foreach ( $pages as $page ) {
                            if($tmreviews_my_place == $page->ID){
                                ?>
                                <option value="<?php echo esc_attr($page->ID);?>" selected><?php echo esc_attr($page->post_title);?></option>
                                <?php
                            } else { ?>
                                <option value="<?php echo esc_attr($page->ID);?>"><?php echo esc_attr($page->post_title);?></option>
                                <?php
                            }
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="tmreviews-option-group">
            <h3><?php echo esc_html__('Membership Settings', 'tmreviews'); ?></h3>
            
            <div class="form_options">
                <label><?php echo esc_html__('Please indicate the highest count of that can be displayed on a page.', 'tmreviews'); ?></label>
                <input type="number" placeholder="<?php echo esc_attr__('Place Count', 'tmreviews'); ?>" name="tmreviews_place_count" value="<?php echo esc_attr($place_count);?>"/>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Membership On/Off', 'tmreviews'); ?></label>
                <select name="tmreviews_place_membership_on">
                    <?php
                    if($tmreviews_place_membership_on == 'on'){
                        ?>
                        <option value="on" selected><?php echo esc_html__('On', 'tmreviews');?></option>
                        <option value="off"><?php echo esc_html__('Off', 'tmreviews');?></option>
                        <?php
                    } else { ?>
                        <option value="on"><?php echo esc_html__('On', 'tmreviews');?></option>
                        <option value="off" selected><?php echo esc_html__('Off', 'tmreviews');?></option>
                        <?php
                    }
                    ?>
                </select>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Membership Link', 'tmreviews'); ?></label>
                <input type="text" placeholder="<?php echo esc_attr__('Membership Link', 'tmreviews'); ?>" name="tmreviews_place_membership_link" value="<?php echo esc_attr($tmreviews_place_membership_link);?>"/>
            </div>
        </div>

        <?php if(class_exists('WPCF7')){ ?>
        <div class="tmreviews-option-group">
            <h3><?php echo esc_html__('Form Settings', 'tmreviews'); ?></h3>
            
            <div class="form_options">
                <label><?php echo esc_html__('Employee request form.', 'tmreviews'); ?></label>
                <?php
                $tmreviews_empl_form = get_option('tmreviews_empl_form', true);
                $args = array(
                    'numberposts' => -1,
                    'post_type'   => 'wpcf7_contact_form'
                );
                $contact_forms = get_posts($args);
                ?>
                <select name="tmreviews_empl_form">
                    <option value="disable" <?php echo $tmreviews_empl_form == 'disable' ? esc_attr("selected") : ""?>><?php echo esc_html__('Select a Form', 'tm-reviews');?></option>
                    <?php
                    if(isset($contact_forms) && !empty($contact_forms)){
                        foreach ($contact_forms as $pl){ ?>
                            <option value="<?php echo esc_attr($pl->ID)?>" <?php echo $tmreviews_empl_form == $pl->ID ? esc_attr("selected") : ""?>><?php echo esc_html(get_the_title($pl->ID));?></option>
                        <?php }
                    } ?>
                </select>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Claim Request form.', 'tmreviews'); ?></label>
                <?php
                $tmreviews_claim_form = get_option('tmreviews_claim_form', true);
                $args = array(
                    'numberposts' => -1,
                    'post_type'   => 'wpcf7_contact_form'
                );
                $contact_formss = get_posts($args);
                ?>
                <select name="tmreviews_claim_form">
                    <option value="disable" <?php echo $tmreviews_claim_form == 'disable' ? esc_attr("selected") : ""?>><?php echo esc_html__('Select a Form', 'tm-reviews');?></option>
                    <?php
                    if(isset($contact_formss) && !empty($contact_formss)){
                        foreach ($contact_formss as $pls){ ?>
                            <option value="<?php echo esc_attr($pls->ID)?>" <?php echo $tmreviews_claim_form == $pls->ID ? esc_attr("selected") : ""?>><?php echo esc_html(get_the_title($pls->ID));?></option>
                        <?php }
                    } ?>
                </select>
            </div>
        </div>
        <?php } ?>

        <div class="tmreviews-option-group">
            <h3><?php echo esc_html__('Notification Settings', 'tmreviews'); ?></h3>
            
            <div class="form_options">
                <label><?php echo esc_html__('Email Notifications for New Places', 'tmreviews'); ?></label>
                <?php $tmreviews_email_notification = get_option('tmreviews_email_notification', 'disable'); ?>
                <select name="tmreviews_email_notification">
                    <option value="enable" <?php echo $tmreviews_email_notification == "enable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Enable', 'tm-reviews');?></option>
                    <option value="disable" <?php echo $tmreviews_email_notification == "disable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Disable', 'tm-reviews');?></option>
                </select>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Email for Notifications', 'tmreviews'); ?></label>
                <?php $tmreviews_notification_email = get_option('tmreviews_notification_email', get_option('admin_email')); ?>
                <input type="text" placeholder="<?php echo esc_attr__('Email Address', 'tmreviews'); ?>" name="tmreviews_notification_email" value="<?php echo esc_attr($tmreviews_notification_email);?>"/>
                <p class="description"><?php echo esc_html__('Email address to receive notifications about new places. Leave empty to use admin email.', 'tmreviews'); ?></p>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Telegram Notifications for New Places', 'tmreviews'); ?></label>
                <?php $tmreviews_telegram_notification = get_option('tmreviews_telegram_notification', 'disable'); ?>
                <select name="tmreviews_telegram_notification">
                    <option value="enable" <?php echo $tmreviews_telegram_notification == "enable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Enable', 'tm-reviews');?></option>
                    <option value="disable" <?php echo $tmreviews_telegram_notification == "disable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Disable', 'tm-reviews');?></option>
                </select>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Telegram Bot Token', 'tmreviews'); ?></label>
                <?php $tmreviews_telegram_token = get_option('tmreviews_telegram_token', ''); ?>
                <input type="text" placeholder="<?php echo esc_attr__('Bot Token', 'tmreviews'); ?>" name="tmreviews_telegram_token" value="<?php echo esc_attr($tmreviews_telegram_token);?>"/>
                <p class="description"><?php echo esc_html__('Token from BotFather for your Telegram bot.', 'tmreviews'); ?></p>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Telegram Chat ID', 'tmreviews'); ?></label>
                <?php $tmreviews_telegram_chat_id = get_option('tmreviews_telegram_chat_id', ''); ?>
                <input type="text" placeholder="<?php echo esc_attr__('Chat ID', 'tmreviews'); ?>" name="tmreviews_telegram_chat_id" value="<?php echo esc_attr($tmreviews_telegram_chat_id);?>"/>
                <p class="description"><?php echo esc_html__('Chat ID where notifications will be sent.', 'tmreviews'); ?></p>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Test Telegram Notification', 'tmreviews'); ?></label>
                <button type="button" id="tmreviews-test-telegram" class="button"><?php echo esc_html__('Test Notification', 'tmreviews'); ?></button>
                <div id="tmreviews-telegram-test-result" class="notice" style="display:none; margin-top: 10px;"></div>
                <p class="description"><?php echo esc_html__('Click to test your Telegram notification settings. Make sure to save your settings first.', 'tmreviews'); ?></p>
            </div>
        </div>

        <div class="tmreviews-option-group">
            <h3><?php echo esc_html__('Additional Settings', 'tmreviews'); ?></h3>
            
            <div class="form_options">
                <label><?php echo esc_html__('Enhance search engine optimization using Google Snippets', 'tmreviews'); ?></label>
                <?php $tmreviews_google_snippets = get_option('tmreviews_google_snippets', true);?>
                <select name="tmreviews_google_snippets">
                    <option value="enable" <?php echo $tmreviews_google_snippets == "enable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Enable', 'tm-reviews');?></option>
                    <option value="disable" <?php echo $tmreviews_google_snippets == "disable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Disable', 'tm-reviews');?></option>
                </select>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Notify the company about comments', 'tmreviews'); ?></label>
                <?php $tmreviews_notify_comp = get_option('tmreviews_notify_comp', true);?>
                <select name="tmreviews_notify_comp">
                    <option value="enable" <?php echo $tmreviews_notify_comp == "enable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Enable', 'tm-reviews');?></option>
                    <option value="disable" <?php echo $tmreviews_notify_comp == "disable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Disable', 'tm-reviews');?></option>
                </select>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Enable/Disable "Official page" label', 'tmreviews'); ?></label>
                <?php $tmreviews_off_page = get_option('tmreviews_off_page', true);?>
                <select name="tmreviews_off_page">
                    <option value="enable" <?php echo $tmreviews_off_page == "enable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Enable', 'tm-reviews');?></option>
                    <option value="disable" <?php echo $tmreviews_off_page == "disable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Disable', 'tm-reviews');?></option>
                </select>
            </div>

            <div class="form_options">
                <label><?php echo esc_html__('Enable/Disable Pros/Cons', 'tmreviews'); ?></label>
                <?php $proscons_enable = get_option('tmreviews_proscons_enable', true);?>
                <select name="tmreviews_proscons_enable">
                    <option value="enable" <?php echo $proscons_enable == "enable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Enable', 'tm-reviews');?></option>
                    <option value="disable" <?php echo $proscons_enable == "disable" ? esc_attr("selected") : ""?>><?php echo esc_html__('Disable', 'tm-reviews');?></option>
                </select>
            </div>
        </div>

        <button type="submit" class="button button-primary"><?php echo esc_html__('Save', 'tm-reviews');?></button>
    </form>
</div>

<?php
if(isset($_POST['tmreviews_post_type']) && $_POST['tmreviews_post_type'] != ''){
    $tmreviews_post_type['new'] = strtolower($_POST['tmreviews_post_type']);
    $tmreviews_post_type['old'] = tmreviews_get_post_type();
    if(isset($tmreviews_post_type['new']) && $tmreviews_post_type['new'] != ''){
        /*
        global $wpdb;
        $update_data = get_posts(array('post_type' => $tmreviews_post_type['old'], 'numberposts' => -1, 'post_status' => array('publish', 'draft', 'auto-draft', 'pending')));
        if(isset($update_data) && !empty($update_data)){
            foreach ($update_data as $ud){
                set_post_type( $ud->ID, $tmreviews_post_type['new']);
            }
        }
        $wpdb->update( $wpdb->prefix . 'term_taxonomy',
            [ 'taxonomy' => $tmreviews_post_type['new'] . '-category'],
            [ 'taxonomy' => $tmreviews_post_type['new'] . '-category' ]
        );
        update_option('tmreviews_post_type', $tmreviews_post_type);
        */

        tmreviews_cahenge_post_type($tmreviews_post_type['new'], $tmreviews_post_type['old']);
    }

    $perm = get_admin_url() . 'edit.php?post_type=' . $tmreviews_post_type['new'] . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';

}



if(isset($_POST['tmreviews_plans_page_id']) && $_POST['tmreviews_plans_page_id'] != ''){
    if(isset($user_reviews_option) && $user_reviews_option != ''){
        update_option('tmreviews_plans_page_id', $_POST['tmreviews_plans_page_id']);
    } else {
        add_option( 'tmreviews_plans_page_id', $_POST['tmreviews_plans_page_id'] );
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}


if(isset($_POST['tmreviews_add_place']) && $_POST['tmreviews_add_place'] != ''){
    if(isset($tmreviews_add_place) && $tmreviews_add_place != ''){
        update_option('tmreviews_add_place', $_POST['tmreviews_add_place']);
    } else {
        add_option( 'tmreviews_add_place', $_POST['tmreviews_add_place'] );
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}


if(isset($_POST['tmreviews_my_place']) && $_POST['tmreviews_my_place'] != ''){
    if(isset($tmreviews_my_place) && $tmreviews_my_place != ''){
        update_option('tmreviews_my_place', $_POST['tmreviews_my_place']);
    } else {
        add_option( 'tmreviews_my_place', $_POST['tmreviews_my_place'] );
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}



if(isset($_POST['tmreviews_user_reviews_page_id']) && $_POST['tmreviews_user_reviews_page_id'] != ''){
    if(isset($user_reviews_option) && $user_reviews_option != ''){
        update_option('tmreviews_user_reviews_page_id', $_POST['tmreviews_user_reviews_page_id']);
    } else {
        add_option( 'tmreviews_user_reviews_page_id', $_POST['tmreviews_user_reviews_page_id'] );
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}


if(isset($_POST['tmreviews_add_place_page_id']) && $_POST['tmreviews_add_place_page_id'] != '') {
    if (isset($add_place_option) && $add_place_option != '') {
        update_option('tmreviews_add_place_page_id', $_POST['tmreviews_add_place_page_id']);
    } else {
        add_option('tmreviews_add_place_page_id', $_POST['tmreviews_add_place_page_id']);
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}


if(isset($_POST['tmreviews_place_count']) && $_POST['tmreviews_place_count'] != '') {
    if (isset($place_count) && $place_count != '') {
        update_option('tmreviews_place_count', $_POST['tmreviews_place_count']);
    } else {
        add_option('tmreviews_place_count', $_POST['tmreviews_place_count']);
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}


if(isset($_POST['tmreviews_place_membership_on']) && $_POST['tmreviews_place_membership_on'] != ''){
    if(isset($place_membership_on) && $place_membership_on != ''){
        update_option('tmreviews_place_membership_on', $_POST['tmreviews_place_membership_on']);
    } else {
        add_option( 'tmreviews_place_membership_on', $_POST['tmreviews_place_membership_on'] );
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}

if(isset($_POST['tmreviews_place_membership_link']) && $_POST['tmreviews_place_membership_link'] != ''){
    if(isset($tmreviews_place_membership_link) && $tmreviews_place_membership_link != ''){
        update_option('tmreviews_place_membership_link', $_POST['tmreviews_place_membership_link']);
    } else {
        add_option( 'tmreviews_place_membership_link', $_POST['tmreviews_place_membership_link'] );
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}

if(isset($_POST['tmreviews_empl_form']) && $_POST['tmreviews_empl_form'] != ''){
    if(isset($tmreviews_empl_form) && $tmreviews_empl_form != ''){
        update_option('tmreviews_empl_form', $_POST['tmreviews_empl_form']);
    } else {
        add_option( 'tmreviews_empl_form', '');
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}


if(isset($_POST['tmreviews_claim_form']) && $_POST['tmreviews_claim_form'] != ''){
    if(isset($tmreviews_claim_form) && $tmreviews_claim_form != ''){
        update_option('tmreviews_claim_form', $_POST['tmreviews_claim_form']);
    } else {
        add_option( 'tmreviews_claim_form', '' );
    }
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}

if(isset($_POST['tmreviews_google_snippets']) && $_POST['tmreviews_google_snippets'] != ''){
    update_option('tmreviews_google_snippets', $_POST['tmreviews_google_snippets']);
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}

if(isset($_POST['tmreviews_off_page']) && $_POST['tmreviews_off_page'] != ''){
    update_option('tmreviews_off_page', $_POST['tmreviews_off_page']);
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}

if(isset($_POST['tmreviews_notify_comp']) && $_POST['tmreviews_notify_comp'] != ''){
    update_option('tmreviews_notify_comp', $_POST['tmreviews_notify_comp']);
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}

if(isset($_POST['tmreviews_proscons_enable']) && $_POST['tmreviews_proscons_enable'] != ''){
    update_option('tmreviews_proscons_enable', $_POST['tmreviews_proscons_enable']);
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . $perm . '"</script>';
}

if(isset($_POST['tmreviews_email_notification']) && $_POST['tmreviews_email_notification'] != ''){
    update_option('tmreviews_email_notification', sanitize_text_field($_POST['tmreviews_email_notification']));
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . esc_url($perm) . '"</script>';
}

if(isset($_POST['tmreviews_notification_email'])){
    update_option('tmreviews_notification_email', sanitize_email($_POST['tmreviews_notification_email']));
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . esc_url($perm) . '"</script>';
}

if(isset($_POST['tmreviews_telegram_notification']) && $_POST['tmreviews_telegram_notification'] != ''){
    update_option('tmreviews_telegram_notification', sanitize_text_field($_POST['tmreviews_telegram_notification']));
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . esc_url($perm) . '"</script>';
}

if(isset($_POST['tmreviews_telegram_token'])){
    update_option('tmreviews_telegram_token', sanitize_text_field($_POST['tmreviews_telegram_token']));
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . esc_url($perm) . '"</script>';
}

if(isset($_POST['tmreviews_telegram_chat_id'])){
    update_option('tmreviews_telegram_chat_id', sanitize_text_field($_POST['tmreviews_telegram_chat_id']));
    $perm = get_admin_url() . 'edit.php?post_type=' . tmreviews_get_post_type() . '&page=settings';
    echo '<script>window.location.href = "' . esc_url($perm) . '"</script>';
}
?>