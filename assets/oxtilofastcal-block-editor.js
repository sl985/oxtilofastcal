/**
 * Oxtilofastcal Gutenberg Block
 *
 * @package Oxtilofastcal
 */

(function (wp) {
    'use strict';

    var el = wp.element.createElement;
    var __ = wp.i18n.__;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var Placeholder = wp.components.Placeholder;
    var RawHTML = wp.element.RawHTML;

    // Calendar icon SVG
    var calendarIcon = el('svg', {
        width: 24,
        height: 24,
        viewBox: '0 0 24 24',
        xmlns: 'http://www.w3.org/2000/svg'
    },
        el('path', {
            d: 'M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM9 10H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm-8 4H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2z',
            fill: 'currentColor'
        })
    );

    // Register block type
    wp.blocks.registerBlockType('oxtilofastcal/booking-form', {
        title: __('Oxtilofastcal Form', 'oxtilofastcal'),
        description: __('Display the booking form.', 'oxtilofastcal'),
        icon: calendarIcon,
        category: 'widgets',
        keywords: [
            __('booking', 'oxtilofastcal'),
            __('calendar', 'oxtilofastcal'),
            __('reservation', 'oxtilofastcal'),
            __('appointment', 'oxtilofastcal')
        ],
        supports: {
            html: false,
            align: ['wide', 'full'],
            multiple: false
        },
        attributes: {},
        edit: function (props) {
            var blockProps = useBlockProps({
                className: 'oxtilofastcal-block-preview'
            });

            return el(
                'div',
                blockProps,
                el(
                    Placeholder,
                    {
                        icon: calendarIcon,
                        label: __('Oxtilofastcal Booking Form', 'oxtilofastcal'),
                        instructions: __('The booking form will be displayed here on the frontend.', 'oxtilofastcal')
                    },
                    el(
                        'div',
                        { className: 'oxtilofastcal-block-info' },
                        el(
                            'p',
                            {},
                            __('Configure services and availability in Oxtilofastcal settings.', 'oxtilofastcal')
                        )
                    )
                )
            );
        },
        save: function () {
            // Return null to render dynamically via PHP.
            return null;
        }
    });

})(window.wp);
