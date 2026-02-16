/**
 * Admin bookings page JavaScript.
 *
 * @package Oxtilofastcal
 */

/* global jQuery, oxtilofastcalBookings */
jQuery(document).ready(function ($) {
    var nonce = oxtilofastcalBookings.nonce;
    var ajaxUrl = oxtilofastcalBookings.ajaxUrl;
    var i18n = oxtilofastcalBookings.i18n;

    // Check available slots
    $('#oxtilofastcal-check-availability').on('click', function () {
        var date = $('#booking_date').val();
        var serviceId = $('#service_id').val();
        var $container = $('#oxtilofastcal-available-slots-container');
        var $row = $('#oxtilofastcal-available-slots-row');

        if (!date) {
            alert(i18n.selectDateFirst);
            return;
        }

        $container.html('<p><span class="spinner is-active" style="float: none;"></span> ' + i18n.loading + '</p>');
        $row.show();

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'oxtilofastcal_get_slots',
                nonce: nonce,
                date: date,
                service_id: serviceId
            },
            success: function (response) {
                if (response.success && response.data.slots && response.data.slots.length > 0) {
                    var html = '<div class="oxtilofastcal-admin-slots" style="display: flex; flex-wrap: wrap; gap: 5px;">';
                    response.data.slots.forEach(function (slot) {
                        var startTime = slot.start.substring(11, 16); // Extract HH:MM
                        var endTime = slot.end.substring(11, 16);
                        html += '<button type="button" class="button oxtilofastcal-slot-btn" data-start="' + startTime + '" data-end="' + endTime + '" style="margin: 2px;">' + slot.label + '</button>';
                    });
                    html += '</div>';
                    $container.html(html);

                    // Slot click handler
                    $('.oxtilofastcal-slot-btn').on('click', function () {
                        $('#start_time').val($(this).data('start'));
                        $('#end_time').val($(this).data('end'));
                        $('.oxtilofastcal-slot-btn').removeClass('button-primary');
                        $(this).addClass('button-primary');
                    });
                } else {
                    $container.html('<p class="description" style="color: #d63638;">' + i18n.noSlots + '</p>');
                }
            },
            error: function () {
                $container.html('<p class="description" style="color: #d63638;">' + i18n.errorSlots + '</p>');
            }
        });
    });

    // Auto-calculate end time based on service duration
    $('#oxtilofastcal-auto-end-time').on('click', function () {
        var startTime = $('#start_time').val();
        if (!startTime) {
            alert(i18n.enterStartFirst);
            return;
        }

        var duration = parseInt($('#service_template option:selected').data('duration')) || 30;
        var parts = startTime.split(':');
        var hours = parseInt(parts[0]);
        var minutes = parseInt(parts[1]) + duration;

        hours += Math.floor(minutes / 60);
        minutes = minutes % 60;

        if (hours >= 24) {
            hours = hours - 24;
        }

        var endTime = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0');
        $('#end_time').val(endTime);
    });

    // When service template is selected, auto-fill the service name and recalculate end time
    $('#service_template').on('change', function () {
        var $selected = $(this).find('option:selected');
        var name = $selected.data('name');
        if (name) {
            $('#service_name').val(name);
        }
        if ($('#start_time').val()) {
            $('#oxtilofastcal-auto-end-time').trigger('click');
        }
    });
});
