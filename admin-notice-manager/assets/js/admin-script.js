/**
 * Admin Notice Manager JavaScript
 * File: assets/js/admin-script.js
 */

(function($) {
    'use strict';

    $(document).ready(function() {

        // Copy API keys and URLs to the clipboard.
        $('.anm-copy-api-key').on('click', async function(e) {
            e.preventDefault();

            var button = $(this);
            var apiKey = button.data('api-key');

            try {
                await navigator.clipboard.writeText(apiKey);
            } catch (error) {
                var tempInput = $('<input>');
                $('body').append(tempInput);
                tempInput.val(apiKey).trigger('select');
                document.execCommand('copy');
                tempInput.remove();
            }

            // Show feedback
            var originalText = button.text();
            button.text(anmData.copiedText);

            setTimeout(function() {
                button.text(originalText);
            }, 2000);
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

        $('.anm-confirm-delete').on('click', function(e) {
            if (!window.confirm(anmData.confirmDeleteText)) {
                e.preventDefault();
            }
        });

        // Client modal.
        $('#anm-add-client-btn').on('click', function(e) {
            e.preventDefault();
            $('#anm-add-client-modal, #anm-modal-overlay').prop('hidden', false);
        });

        $('#anm-cancel-add-client, #anm-modal-overlay').on('click', function() {
            $('#anm-add-client-modal, #anm-modal-overlay').prop('hidden', true);
        });

    });

})(jQuery);
