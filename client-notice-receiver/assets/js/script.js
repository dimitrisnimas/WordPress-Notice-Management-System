/**
 * Client Notice Receiver JavaScript
 * File: assets/js/script.js
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Handle notice dismissal
        $(document).on('click', '.cnr-dismiss-notice', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var noticeDiv = button.closest('.cnr-notice');
            var noticeId = noticeDiv.data('notice-id');
            
            if (!noticeId) {
                noticeDiv.fadeOut(200, function() {
                    $(this).remove();
                });
                return;
            }
            
            // Fade out immediately for better UX
            noticeDiv.fadeOut(200, function() {
                $(this).remove();
            });
            
            // Send dismissal to server
            $.ajax({
                url: cnrData.ajaxUrl,
                method: 'POST',
                data: {
                    action: 'cnr_dismiss_notice',
                    nonce: cnrData.nonce,
                    notice_id: noticeId
                },
                success: function(response) {
                    if (!response.success) {
                        console.error('Failed to dismiss notice:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error dismissing notice:', error);
                }
            });
        });
        
        // Add smooth fade-in animation to notices
        $('.cnr-notice').each(function(index) {
            var notice = $(this);
            notice.css('opacity', '0');
            
            setTimeout(function() {
                notice.animate({opacity: 1}, 300);
            }, index * 100);
        });
        
    });
    
})(jQuery);
