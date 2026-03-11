<?php
/**
 * TM Reviews Share Functions
 *
 * Функции для шаринга в социальных сетях
 *
 * @package TM_Reviews
 */

// Проверяем, что функция еще не определена, чтобы избежать дублирования
if (!function_exists('tmreviews_get_share_show')) {
    /**
     * Отображает кнопки шаринга AddToAny
     * 
     * @return string HTML-код кнопок шаринга
     */
    function tmreviews_get_share_show() {
        return tmreviews_addtoany_share_buttons();
    }
}

// Проверяем, что функция еще не определена, чтобы избежать дублирования
if (!function_exists('tmreviews_addtoany_share_buttons')) {
    /**
     * Создает кнопки шаринга с использованием плагина AddToAny
     * 
     * @return string HTML-код кнопок шаринга AddToAny
     */
    function tmreviews_addtoany_share_buttons() {
        // Начинаем буферизацию вывода
        ob_start();
        
        // Получаем текущий URL и заголовок
        global $post;
        
        if (!isset($post) || !is_object($post)) {
            // Если нет объекта поста, используем текущий URL
            $permalink = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $title = get_bloginfo('name');
        } else {
            $permalink = get_permalink($post->ID);
            $title = get_the_title($post->ID);
        }
        
        // Используем шорткод AddToAny с параметрами
        echo '<div class="tmreviews-addtoany-share-buttons">';
        echo do_shortcode('[addtoany url="' . esc_url($permalink) . '" title="' . esc_attr($title) . '"]');
        echo '</div>';
        
        // Добавляем стили для кнопок
        echo '<style>
            .tmreviews-addtoany-share-buttons {
                margin: 10px 0;
                display: inline-block;
            }
            .tmreviews-addtoany-share-buttons .a2a_kit {
                display: flex;
                align-items: center;
            }
            .tmreviews-addtoany-share-buttons .a2a_button {
                margin-right: 5px;
            }
        </style>';
        
        // Возвращаем буферизированный вывод
        return ob_get_clean();
    }
}
