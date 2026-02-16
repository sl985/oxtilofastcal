=== Oxtilo Fast Cal ===
Contributors: slawomirklimek
Donate link: https://oxtilo.pl/
Tags: booking, calendar, appointment, schedule, reservation
Requires at least: 5.8
Tested up to: 6.9
Stable tag: 0.9.6
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A secure and flexible booking management system for WordPress with availability handling, ICS sync, and REST API.

== Description ==

Oxtilo Fast Cal is a secure and flexible booking management system for WordPress. Features robust availability handling, ICS calendar synchronization, email notifications, and a full REST API. Includes built-in Polish translations.

= Features =

*   **Service Management** - Define multiple services with duration and type (online/in-person)
*   **Booking Intervals** - Configurable slot intervals (15, 30, or 60 minutes)
*   **Manual Bookings** - Administrator can create bookings for any time, including outside working hours
*   **Frontend Management** - Clients can reschedule or cancel bookings via secure links
*   **Working Hours** - Configure working hours for each day of the week
*   **Availability Calculation** - Automatic slot availability based on working hours and existing bookings
*   **External Calendar Sync** - Import busy times from iCloud, Proton Calendar, or holiday calendars via ICS
*   **ICS Feed Export** - Private calendar feed for syncing bookings to external apps
*   **Email Notifications** - Automatic notifications to admin and clients with ICS attachments and customizable templates
*   **Mobile Friendly** - Responsive booking form with quick date selection (Today/Tomorrow)
*   **REST API** - Token-authenticated endpoints for external integrations (Apple Shortcuts, Zapier)
*   **Built-in Polish Translations** - No `.mo` file needed for Polish locale

== Installation ==

1. Upload the `oxtilofastcal` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to **Oxtilo Fast Cal** in the admin menu to configure settings

== Frequently Asked Questions ==

= How do I add the booking form? =

Add the booking form to any page using the shortcode: `[oxtilofastcal_form]`

= Does it support translations? =

Yes, it has built-in Polish translations.

== Screenshots ==

1. (No screenshots available yet)

== Changelog ==

= 0.9.6 =
* **Security**: Improved nonce verification and permission checks.
* **Refactor**: Replaced inline scripts and styles with `wp_enqueue_script` and `wp_enqueue_style`.
* **Fix**: Corrected text domain to `oxtilo-fast-cal` matches plugin slug.
* **Compatibility**: Updated Block API version to 3 for WordPress 7.0 readiness.
* **Compatibility**: Tested up to WordPress 6.9.

= 0.9.5 =
* **Refactor**: Codebase improvements for WordPress.org plugin review standards.
* **Fix**: Replaced discouraged functions (`unlink` -> `wp_delete_file`) for better hosting compatibility.
* **Fix**: Removed redundant `load_plugin_textdomain` as translations are handled by WordPress.org.
* **Security**: Enhanced output escaping and sanitization in admin views.
* **I18n**: Fixed text domain inconsistencies and missing translation strings.

= 0.9.4 =
* **Feature**: Added setting to include/hide "Manage Booking" link in the private calendar feed events.
* **Security**: Added warning when "Manage Booking" link is enabled in calendar feed to prevent unauthorized access.
* **I18n**: Added Polish translations for new settings.

= 0.9.3 =
* **Security**: Added server-side validation for `max_days_future` in REST API `GET /slots` endpoint.
* **Security**: Hardened output escaping for `paginate_links` to prevent potential XSS vulnerabilities.
* **Security**: Improved `$_GET` parameter handling and escaping in admin booking pages.
* **Compatibility**: Replaced `file_put_contents` with WP Filesystem API for better hosting compatibility.

= 0.9.2 =
* **Fix**: Prevented double booking when rescheduling by excluding the current booking from availability checks.
* **Fix**: Updated frontend availability display to correctly show slots occupied by the current booking as available for rescheduling.

= 0.9.1 =
* **Security**: Added Anti-Bot Protection (Honeypot + JS Time Trap + Nonce) to booking form.
* **Security**: Added ability to enable/disable anti-bot protection in Security settings.
* **I18n**: Added Polish translations for new anti-bot settings.

= 0.9.0 =
* **Security**: Implemented comprehensive Rate Limiting system to prevent abuse (DoS, brute force, spam).
*   Configurable request limits for public endpoints (requests/minute).
*   Smart IP detection with support for Cloudflare, Sucuri, AWS CloudFront, Fastly, and proxies.
*   Rate limiting applied to booking form submissions, AJAX slot checks, and REST API.
* **Security**: Fixed potential race condition (TOCTOU) in booking creation using atomic database transactions.
* **Security**: Added strict date/time validation to prevent invalid booking durations.
* **Security**: Hardened singleton pattern for admin class to prevent multiple instances.
* **I18n**: Completed Polish translations for all new security features and API documentation.
* **Fix**: Fixed issue with WordPress data sanitization (unslashing) for Apostrophes.
* **Fix**: Added validation to ensure end time is always after start time.

= 0.8.0 =
* **Security**: Separated API token from calendar feed token for better security
*   Calendar feed token (32 chars): Read-only access for ICS feeds shared with calendar apps
*   API token (48 chars): Write access for REST API, kept secret
*   **Breaking**: If using REST API, update your applications to use the new API token from Settings
* Added: Dedicated API token display and regeneration button in REST API settings section
* Added: Security warning explaining token separation in admin panel

= 0.7.0 =
* Added: REST API for external integrations (e.g., Apple Shortcuts, Zapier)
* Added: GET `/wp-json/oxtilofastcal/v1/slots` endpoint for available time slots
* Added: POST `/wp-json/oxtilofastcal/v1/create` endpoint for booking creation
* Added: Token-based API authentication via `X-Oxtilofastcal-Token` header
* Added: Custom duration parameter for slot availability queries
* Added: API documentation in admin settings page with real URLs and tokens
* Improved: `get_available_slots()` now supports custom duration override

= 0.6.0 =
* Added: Administrator ability to manually create bookings from the dashboard.
* Added: Configurable booking interval setting (15, 30, or 60 minutes).
* Added: "Client Message" field to booking form and notifications.
* Added: Quick date selectors (Today, Tomorrow) to frontend form.
* Added: Option to toggle 12h/24h time format on frontend.
* Added: Email notifications for booking updates and cancellations.
* Fixed: Issue with external ICS calendar synchronization.
* Fixed: Gutenberg block rendering issues.
* Fixed: ICS attachment filename in emails.
* Improved: Frontend form styling and responsiveness.
* Improved: Admin interface organization.

= 0.5.1 =
* Refactored codebase into separate files with proper class structure
* Added uninstall.php for clean plugin removal
* Added PHP 7.4 compatibility (polyfill for `str_ends_with`)
* Improved security with better input validation
* Changed date input to native HTML5 date picker
* Added keyboard accessibility for slot selection
* Improved XSS protection in JavaScript
* Added multisite support for uninstall

= 0.5.0 =
* Initial release

== Upgrade Notice ==

= 0.9.6 =
Security and compatibility updates. Recommended update.

= 0.9.5 =
Codebase refactoring and security improvements. Update recommended.
