<?php
/**
 * Widget for displaying popular place categories
 * (Виджет для отображения популярных категорий мест)
 */
class TMReviews_Popular_Categories_Widget extends WP_Widget {

    /**
     * Constructor
     * (Конструктор)
     */
    public function __construct() {
        parent::__construct(
            'tmreviews_popular_categories',
            esc_html__('TM Reviews - Popular Categories', 'tm-reviews'),
            array(
                'description' => esc_html__('Shows selected place categories with post counts', 'tm-reviews')
            )
        );
    }

    /**
     * Frontend widget output
     * (Вывод виджета во фронтенде)
     */
    public function widget($args, $instance) {
        echo $args['before_widget'];

        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $selected_categories = !empty($instance['categories']) ? $instance['categories'] : array();

        if (!empty($selected_categories)) {
            $terms = get_terms(array(
                'taxonomy' => tmreviews_get_post_type() . '-category',
                'include' => $selected_categories,
                'hide_empty' => true
            ));

            if (!empty($terms) && !is_wp_error($terms)) {
                echo '<ul class="tmreviews-popular-categories">';
                
                foreach ($terms as $term) {
                    echo '<li class="tmreviews-category-item">';
                    echo '<a href="' . esc_url(get_term_link($term)) . '">';
                    echo '<span class="tmreviews-category-name">' . esc_html($term->name) . '</span>';
                    echo '<span class="tmreviews-category-count">';
                    printf(
                        /* translators: %d: number of places */
                        esc_html(_n('%d Place', '%d Places', $term->count, 'tm-reviews')),
                        $term->count
                    );
                    echo '</span>';
                    echo '</a>';
                    echo '</li>';
                }
                
                echo '</ul>';
            }
        }

        echo $args['after_widget'];
    }

    /**
     * Admin form settings
     * (Форма настроек в админке)
     */
    public function form($instance) {
        $title = !empty($instance['title']) ? $instance['title'] : esc_html__('Popular Categories', 'tm-reviews');
        $selected_categories = !empty($instance['categories']) ? $instance['categories'] : array();

        // Get all place categories
        $terms = get_terms(array(
            'taxonomy' => tmreviews_get_post_type() . '-category',
            'hide_empty' => false
        ));

        if (empty($terms) || is_wp_error($terms)) {
            echo '<p>' . esc_html__('No categories found. Please add some categories first.', 'tm-reviews') . '</p>';
            return;
        }
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title:', 'tm-reviews'); ?></label>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
                   type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label><?php esc_html_e('Select categories to display:', 'tm-reviews'); ?></label>
            <div style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; padding: 5px; margin-top: 5px;">
                <?php foreach ($terms as $term) : ?>
                    <label style="display: block; margin-bottom: 5px;">
                        <input type="checkbox" 
                               name="<?php echo esc_attr($this->get_field_name('categories')); ?>[]" 
                               value="<?php echo esc_attr($term->term_id); ?>"
                               <?php checked(in_array($term->term_id, $selected_categories)); ?>>
                        <?php echo esc_html($term->name); ?> 
                        <?php printf(
                            /* translators: %d: number of places */
                            esc_html(_n('%d Place', '%d Places', $term->count, 'tm-reviews')),
                            $term->count
                        ); ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </p>
        <?php
    }

    /**
     * Save widget settings
     * (Сохранение настроек виджета)
     */
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['categories'] = (!empty($new_instance['categories'])) ? array_map('absint', $new_instance['categories']) : array();
        return $instance;
    }
}

// Register widget
// (Регистрируем виджет)
function tmreviews_register_popular_categories_widget() {
    register_widget('TMReviews_Popular_Categories_Widget');
}
add_action('widgets_init', 'tmreviews_register_popular_categories_widget');
