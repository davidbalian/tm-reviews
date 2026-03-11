/**
 * TM Reviews admin notifications script
 *
 * Handles the test notification functionality
 *
 * @package TM_Reviews
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Test Telegram notification
        $('#tmreviews-test-telegram').on('click', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var $result = $('#tmreviews-telegram-test-result');
            
            $button.prop('disabled', true).text(tmreviews_notifications.testing);
            $result.html('').removeClass('notice-success notice-error').hide();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'tmreviews_test_telegram',
                    nonce: tmreviews_notifications.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $result.addClass('notice-success').html('<p>' + response.data + '</p>').show();
                    } else {
                        $result.addClass('notice-error').html('<p>' + response.data + '</p>').show();
                    }
                },
                error: function() {
                    $result.addClass('notice-error').html('<p>' + tmreviews_notifications.error + '</p>').show();
                },
                complete: function() {
                    $button.prop('disabled', false).text(tmreviews_notifications.test);
                }
            });
        });
    });
    
})(jQuery);
