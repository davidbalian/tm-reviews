<?php
/**
 * Виджет для отображения последних мест
 */
class TMReviews_Recent_Places_Widget extends WP_Widget {

    /**
     * Конструктор
     */
    public function __construct() {
        parent::__construct(
            'tmreviews_recent_places',
            esc_html__('TM Reviews - Recent Places', 'tm-reviews'),
            array(
                'description' => esc_html__('Shows recent places with thumbnails and ratings', 'tm-reviews')
            )
        );
    }

    /**
     * Вывод виджета во фронтенде
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $number = (!empty($instance['number'])) ? absint($instance['number']) : 5;

        $query = new WP_Query(array(
            'post_type' => tmreviews_get_post_type(),
            'posts_per_page' => $number,
            'orderby' => 'date',
            'order' => 'DESC'
        ));

        if ($query->have_posts()) {
            echo '<ul class="tmreviews-recent-places">';
            
            while ($query->have_posts()) {
                $query->the_post();
                
                // Получаем рейтинг
                $comments = get_comments(array('post_id' => get_the_ID()));
                $total = 0;
                $reviews_count = 0;
                
                if (!empty($comments)) {
                    foreach ($comments as $comment) {
                        $rate = get_comment_meta($comment->comment_ID, 'rating', true);
                        if (!empty($rate)) {
                            $total += intval($rate);
                            $reviews_count++;
                        }
                    }
                }
                
                $average = ($reviews_count > 0) ? number_format($total / $reviews_count, 1) : 0;
                
                echo '<li class="tmreviews-recent-place-item">';
                
                // Миниатюра
                if (has_post_thumbnail()) {
                    echo '<div class="tmreviews-recent-place-thumb">';
                    echo '<a href="' . esc_url(get_permalink()) . '">';
                    the_post_thumbnail('thumbnail');
                    echo '</a>';
                    echo '</div>';
                }
                
                echo '<div class="tmreviews-recent-place-content">';
                
                // Название
                echo '<h4 class="tmreviews-recent-place-title">';
                echo '<a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a>';
                echo '</h4>';
                
                // Рейтинг
                if ($reviews_count > 0) {
                    echo '<div class="tmreviews-recent-place-rating">';
                    // Звезды
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $average) {
                            echo '<i class="fa fa-star" aria-hidden="true"></i>';
                        } else {
                            echo '<i class="fa fa-star-o" aria-hidden="true"></i>';
                        }
                    }
                    echo ' <span class="tmreviews-recent-place-rating-value">';
                    echo esc_html($average . '/5.0');
                    echo '</span>';
                    echo '</div>';
                }
                
                echo '</div>';
                echo '</li>';
            }
            
            echo '</ul>';
            wp_reset_postdata();
        }

        echo $args['after_widget'];
    }

    /**
     * Форма настроек в админке
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : esc_html__('Recent Places', 'tm-reviews');
        $number = !empty($instance['number']) ? absint($instance['number']) : 5;
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'tm-reviews'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php esc_html_e('Number of places to show:', 'tm-reviews'); ?></label>
            <input class="tiny-text" id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php echo esc_attr($this->get_field_name('number')); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($number); ?>" size="3">
        </p>
        <?php
    }

    /**
     * Сохранение настроек виджета
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['number'] = (!empty($new_instance['number'])) ? absint($new_instance['number']) : 5;
        return $instance;
    }
}

// Регистрируем виджет
function tmreviews_register_recent_places_widget() {
    register_widget('TMReviews_Recent_Places_Widget');
}
add_action('widgets_init', 'tmreviews_register_recent_places_widget');
