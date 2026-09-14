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

            button.prop('disabled', true);
            noticeDiv.css('opacity', '0.5');

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
                    if (response.success) {
                        noticeDiv.fadeOut(200, function() {
                            $(this).remove();
                        });
                        return;
                    }

                    noticeDiv.css('opacity', '1');
                    button.prop('disabled', false);
                },
                error: function() {
                    noticeDiv.css('opacity', '1');
                    button.prop('disabled', false);
                }
            });
        });

        // Test the remote connection through WordPress so the API key stays server-side.
        $('#cnr-test-connection').on('click', function() {
            var button = $(this);
            var resultDiv = $('#cnr-test-result');

            button.prop('disabled', true).text(cnrData.testingText);
            resultDiv.empty();

            $.post(cnrData.ajaxUrl, {
                action: 'cnr_test_connection',
                nonce: cnrData.nonce
            }).done(function(response) {
                var notice = $('<div>', {
                    class: 'notice inline ' + (response.success ? 'notice-success' : 'notice-error')
                });
                $('<p>').text(response.data.message).appendTo(notice);
                resultDiv.append(notice);
            }).fail(function(xhr) {
                var message = xhr.responseJSON && xhr.responseJSON.data
                    ? xhr.responseJSON.data.message
                    : cnrData.connectionErrorText;
                var notice = $('<div>', {class: 'notice notice-error inline'});
                $('<p>').text(message).appendTo(notice);
                resultDiv.append(notice);
            }).always(function() {
                button.prop('disabled', false).text(cnrData.testText);
            });
        });

    });

})(jQuery);
