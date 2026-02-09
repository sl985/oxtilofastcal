/* global jQuery, oxtilofastcalAdmin */
(function ($) {
    'use strict';

    $(document).ready(function () {
        if (typeof oxtilofastcalAdmin === 'undefined') {
            return;
        }

        var $btn = $('#oxtilofastcal_generate_token_btn');
        var $token = $('#oxtilofastcal_calendar_feed_token');
        var $url = $('#oxtilofastcal_calendar_feed_url');
        var $status = $('#oxtilofastcal_token_status');

        /**
         * Set status message.
         *
         * @param {string} text Status text.
         */
        function setStatus(text) {
            $status.text(text || '');
        }

        /**
         * Handle token generation button click.
         */
        $btn.on('click', function () {
            $btn.prop('disabled', true);
            setStatus(oxtilofastcalAdmin.i18n.generating);

            $.ajax({
                url: oxtilofastcalAdmin.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'oxtilofastcal_generate_calendar_token',
                    nonce: oxtilofastcalAdmin.nonce
                }
            })
                .done(function (resp) {
                    if (resp && resp.success && resp.data && resp.data.token) {
                        $token.val(resp.data.token);
                        $url.val(resp.data.feedUrl || '');
                        setStatus(oxtilofastcalAdmin.i18n.generated);
                    } else {
                        setStatus(oxtilofastcalAdmin.i18n.error);
                    }
                })
                .fail(function () {
                    setStatus(oxtilofastcalAdmin.i18n.error);
                })
                .always(function () {
                    $btn.prop('disabled', false);
                });
        });

        /**
         * Handle API token generation button click.
         */
        var $apiBtn = $('#oxtilofastcal_generate_api_token_btn');
        var $apiToken = $('#oxtilofastcal_api_token');
        var $apiStatus = $('#oxtilofastcal_api_token_status');

        $apiBtn.on('click', function () {
            $apiBtn.prop('disabled', true);
            $apiStatus.text(oxtilofastcalAdmin.i18n.generating);

            $.ajax({
                url: oxtilofastcalAdmin.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'oxtilofastcal_generate_api_token',
                    nonce: oxtilofastcalAdmin.nonce
                }
            })
                .done(function (resp) {
                    if (resp && resp.success && resp.data && resp.data.token) {
                        $apiToken.val(resp.data.token);
                        $apiStatus.text(oxtilofastcalAdmin.i18n.generated);
                    } else {
                        $apiStatus.text(oxtilofastcalAdmin.i18n.error);
                    }
                })
                .fail(function () {
                    $apiStatus.text(oxtilofastcalAdmin.i18n.error);
                })
                .always(function () {
                    $apiBtn.prop('disabled', false);
                });
        });

        /**
         * Handle ICS feed test.
         */
        $('.oxtilofastcal-test-feed').on('click', function () {
            var $this = $(this);
            var inputSel = $this.data('input');
            var $input = $(inputSel);
            var $row = $this.closest('td');
            var $msg = $row.find('.oxtilofastcal-feed-status');
            var url = $input.val();

            if (!url) {
                $msg.css('color', 'red').text('Please enter a URL first.');
                return;
            }

            $this.prop('disabled', true);
            $msg.css('color', '#666').text('Checking...');

            $.ajax({
                url: oxtilofastcalAdmin.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'oxtilofastcal_test_ics_feed',
                    nonce: oxtilofastcalAdmin.testNonce,
                    url: url
                }
            })
                .done(function (resp) {
                    if (resp && resp.success) {
                        $msg.css('color', 'green').text(resp.data.message || 'OK');
                    } else {
                        var err = (resp && resp.data && resp.data.message) ? resp.data.message : 'Error - check console';
                        $msg.css('color', 'red').text(err);
                        console.log('Oxtilofastcal ICS Test Error:', resp);
                    }
                })
                .fail(function (jqXHR, textStatus, errorThrown) {
                    var errMsg = 'Network error: ' + textStatus;
                    if (jqXHR.responseText) {
                        console.log('Oxtilofastcal ICS Test Response:', jqXHR.responseText);
                        errMsg += ' (see console)';
                    }
                    $msg.css('color', 'red').text(errMsg);
                })
                .always(function () {
                    $this.prop('disabled', false);
                });
        });

        /**
         * Handle diagnostics button click.
         */
        $('#oxtilofastcal_run_diagnostics').on('click', function () {
            var $btn = $(this);
            var $date = $('#oxtilofastcal_diag_date');
            var $results = $('#oxtilofastcal_diag_results');
            var $content = $('#oxtilofastcal_diag_content');

            $btn.prop('disabled', true);
            $content.text(oxtilofastcalAdmin.i18n.loading || 'Loading...');
            $results.show();

            $.ajax({
                url: oxtilofastcalAdmin.ajaxUrl,
                method: 'POST',
                dataType: 'json',
                data: {
                    action: 'oxtilofastcal_diagnostics',
                    nonce: oxtilofastcalAdmin.diagNonce,
                    date: $date.val()
                }
            })
                .done(function (resp) {
                    if (resp && resp.success && resp.data) {
                        var d = resp.data;
                        var out = [];

                        out.push('=== DIAGNOSTICS ===');
                        out.push('Date: ' + d.date);
                        out.push('WordPress Timezone: ' + d.wp_timezone);
                        out.push('Current Time: ' + d.current_time);
                        out.push('');

                        out.push('=== BUSY FROM DATABASE ===');
                        if (d.busy_db && d.busy_db.length > 0) {
                            d.busy_db.forEach(function (b) {
                                out.push('  • ' + b.start + ' → ' + b.end);
                            });
                        } else {
                            out.push('  (none)');
                        }
                        out.push('');

                        out.push('=== ICS SOURCES ===');
                        if (d.sources && d.sources.length > 0) {
                            d.sources.forEach(function (src) {
                                out.push(src.name + ':');
                                out.push('  URL: ' + src.url);
                                if (src.events && src.events.length > 0) {
                                    out.push('  Events for this day:');
                                    src.events.forEach(function (ev) {
                                        out.push('    • ' + (ev.summary || '(no title)'));
                                        out.push('      Start (converted): ' + ev.start);
                                        out.push('      End (converted): ' + ev.end);
                                        out.push('      Start (original): ' + ev.start_orig);
                                        out.push('      End (original): ' + ev.end_orig);
                                    });
                                } else {
                                    out.push('  Events for this day: (none)');
                                }
                                out.push('');
                            });
                        }

                        out.push('=== AVAILABLE SLOTS (Service 0) ===');
                        if (d.available && d.available.length > 0) {
                            d.available.forEach(function (s) {
                                out.push('  • ' + s.start + ' → ' + s.end);
                            });
                        } else {
                            out.push('  (none)');
                        }

                        $content.text(out.join('\n'));
                    } else {
                        var err = (resp && resp.data && resp.data.message) ? resp.data.message : 'Error';
                        $content.text('Error: ' + err);
                    }
                })
                .fail(function () {
                    $content.text('Network error');
                })
                .always(function () {
                    $btn.prop('disabled', false);
                });
        });


        /**
         * Handle variable button click.
         */
        $('.oxtilofastcal-var-btn').on('click', function () {
            var $btn = $(this);
            var variable = $btn.data('var');

            // Copy to clipboard.
            var $temp = $('<input>');
            $('body').append($temp);
            $temp.val(variable).select();
            document.execCommand('copy');
            $temp.remove();

            // Visual feedback.
            var originalText = $btn.text();
            $btn.text('Copied!');
            setTimeout(function () {
                $btn.text(originalText);
            }, 1000);

            // Try to insert into last focused textarea.
            if (activeTextarea) {
                var $txt = $(activeTextarea);
                var cursorPos = $txt.prop('selectionStart');
                var v = $txt.val();
                var textBefore = v.substring(0, cursorPos);
                var textAfter = v.substring(cursorPos, v.length);

                $txt.val(textBefore + variable + textAfter);

                // Restore focus and cursor position.
                $txt.focus();
                var newPos = cursorPos + variable.length;
                $txt.prop('selectionStart', newPos);
                $txt.prop('selectionEnd', newPos);
            }
        });

        // Track last focused textarea for email templates.
        var activeTextarea = null;
        $('.oxtilofastcal-email-editor').on('focus', function () {
            activeTextarea = this;
        });

    });

})(jQuery);
