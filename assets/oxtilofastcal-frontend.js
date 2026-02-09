/**
 * Oxtilofastcal Frontend JavaScript
 *
 * @package Oxtilofastcal
 */

/* global jQuery, oxtilofastcalFrontend */
(function ($) {
	'use strict';

	if (typeof oxtilofastcalFrontend === 'undefined') {
		return;
	}

	var $service = $('#oxtilofastcal_service');
	var $date = $('#oxtilofastcal_date');
	var $slots = $('#oxtilofastcal_slots');
	var $slotStart = $('#oxtilofastcal_slot_start');

	/**
	 * Set slots container HTML.
	 *
	 * @param {string} html HTML content.
	 */
	function setSlotsHtml(html) {
		$slots.html(html);
	}

	/**
	 * Escape HTML for safe output.
	 *
	 * @param {string} text Text to escape.
	 * @return {string} Escaped text.
	 */
	function escapeHtml(text) {
		var div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	/**
	 * Render available slots.
	 *
	 * @param {Array} list List of slots.
	 */
	function renderSlots(list) {
		if (!list || !list.length) {
			setSlotsHtml('<div class="oxtilofastcal-slots__empty">' + escapeHtml(oxtilofastcalFrontend.i18n.noSlots) + '</div>');
			$slotStart.val('');
			return;
		}

		var html = '<div class="oxtilofastcal-slots__title">' + escapeHtml(oxtilofastcalFrontend.i18n.chooseTime) + '</div>';
		html += '<div class="oxtilofastcal-slots__grid">';

		for (var i = 0; i < list.length; i++) {
			var slot = list[i];
			var start = slot.start || '';
			var label = slot.label || start;

			html += '<button type="button" class="oxtilofastcal-slot" data-start="' + escapeHtml(start) + '">';
			html += escapeHtml(label);
			html += '</button>';
		}

		html += '</div>';
		setSlotsHtml(html);
	}

	/**
	 * Fetch available slots from server.
	 */
	function fetchSlots() {
		var serviceId = $service.val();
		var dateVal = $date.val();

		if (!serviceId) {
			setSlotsHtml('<div class="oxtilofastcal-slots__placeholder">' + escapeHtml(oxtilofastcalFrontend.i18n.selectService) + '</div>');
			$slotStart.val('');
			return;
		}

		if (!dateVal) {
			setSlotsHtml('<div class="oxtilofastcal-slots__placeholder">' + escapeHtml(oxtilofastcalFrontend.i18n.selectDate) + '</div>');
			$slotStart.val('');
			return;
		}

		setSlotsHtml('<div class="oxtilofastcal-slots__loading">' + escapeHtml(oxtilofastcalFrontend.i18n.loading) + '</div>');
		$slotStart.val('');

		var data = {
			action: 'oxtilofastcal_get_slots',
			nonce: oxtilofastcalFrontend.nonce,
			service_id: serviceId,
			date: dateVal
		};

		if (oxtilofastcalFrontend.booking_id) {
			data.booking_id = oxtilofastcalFrontend.booking_id;
		}

		$.ajax({
			url: oxtilofastcalFrontend.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: data
		}).done(function (resp) {
			if (resp && resp.success && resp.data && resp.data.slots) {
				renderSlots(resp.data.slots);
			} else {
				renderSlots([]);
			}
		}).fail(function () {
			renderSlots([]);
		});
	}

	// Event handlers
	$service.on('change', fetchSlots);
	$date.on('change', function () {
		fetchSlots();
		// Update active state of buttons
		var val = $(this).val();
		$('.oxtilofastcal-date-btn').removeClass('active');
		if (val) {
			$('.oxtilofastcal-date-btn[data-date="' + val + '"]').addClass('active');
		}
	});

	// Date buttons
	$('.oxtilofastcal-date-btn').on('click', function () {
		var date = $(this).data('date');
		if (date) {
			$date.val(date).trigger('change');
		}
	});

	// Slot selection
	$slots.on('click', '.oxtilofastcal-slot', function () {
		$('.oxtilofastcal-slot').removeClass('is-selected');
		$(this).addClass('is-selected');

		var start = $(this).data('start') || '';
		$slotStart.val(start);
	});

	// Keyboard accessibility for slots
	$slots.on('keydown', '.oxtilofastcal-slot', function (e) {
		if (e.key === 'Enter' || e.key === ' ') {
			e.preventDefault();
			$(this).trigger('click');
		}
	});

	// Initialize: Click "Today" button if date is empty
	var $todayBtn = $('.oxtilofastcal-date-btn').first();
	if (!$date.val() && $todayBtn.length) {
		$todayBtn.trigger('click');
	}

})(jQuery);
