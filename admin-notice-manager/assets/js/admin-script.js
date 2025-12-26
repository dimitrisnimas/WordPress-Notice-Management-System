/**
 * Admin Notice Manager JavaScript
 * File: assets/js/admin-script.js
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        
        // Copy API key to clipboard
        $('.anm-copy-api-key').on('click', function(e) {
            e.preventDefault();
            
            var button = $(this);
            var apiKey = button.data('api-key');
            
            // Create temporary input
            var tempInput = $('<input>');
            $('body').append(tempInput);
            tempInput.val(apiKey).select();
            document.execCommand('copy');
            tempInput.remove();
            
            // Show feedback
            var originalText = button.text();
            button.text('Copied!');
            
            setTimeout(function() {
                button.text(originalText);
            }, 2000);
        });
        
        // Confirm delete actions
        $('.anm-delete-confirm').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
                return false;
            }
        });
        
        // Select all clients checkbox
        $('#anm-select-all-clients').on('change', function() {
            $('input[name="client_ids[]"]').prop('checked', $(this).is(':checked'));
        });
        
        // Toggle client selection
        $('input[name="client_ids[]"]').on('change', function() {
            var allChecked = $('input[name="client_ids[]"]').length === $('input[name="client_ids[]"]:checked').length;
            $('#anm-select-all-clients').prop('checked', allChecked);
        });
        
    });
    
})(jQuery);
