<?php
/**
 * Polish translation for Oxtilo Fast Cal.
 *
 * @package Oxtilofastcal
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Built-in Polish translation
 *
 * If WP locale is pl_PL and no real MO is loaded for oxtilofastcal,
 * we translate via gettext filters.
 *
 * @since 0.5.0
 */
final class Oxtilofastcal_I18n_Fallback {

	/**
	 * Polish translations.
	 *
	 * @var array<string,string>
	 */
	private static array $pl = array(
		// Generic.
		'Access Denied'                                                     => 'Brak dostępu',
		'Invalid request.'                                                  => 'Nieprawidłowe żądanie.',

		// Defaults/services.
		'Consultation (Online)'                                             => 'Konsultacja (online)',
		'Consultation (In-person)'                                          => 'Konsultacja (stacjonarnie)',

		// Admin UI - General.
		'Oxtilo Fast Cal'                                                         => 'Oxtilo Fast Cal',
		'Oxtilo Fast Cal Settings'                                                => 'Ustawienia Oxtilo Fast Cal',
		'General'                                                           => 'Ogólne',
		'Settings'                                                          => 'Ustawienia',
		'Bookings'                                                          => 'Rezerwacje',
		'Email Templates'                                                   => 'Szablony e-mail',
		'Diagnostics'                                                       => 'Diagnostyka',
		'Admin notification email'                                          => 'E-mail powiadomień administratora',
		'Where new booking notifications should be sent.'                   => 'Na ten adres będą wysyłane powiadomienia o nowych rezerwacjach.',
		'Administrator Name'                                                => 'Imię administratora',
		'Used in ICS titles and email notifications.'                       => 'Używane w tytułach ICS i powiadomieniach e-mail.',
		'Google Meet link (optional)'                                       => 'Link Google Meet (opcjonalnie)',
		'Used for online meetings in email notifications.'                  => 'Używany dla spotkań online w powiadomieniach e-mail.',
		'Minimum lead time (minutes)'                                       => 'Minimalny czas wyprzedzenia (minuty)',
		'How many minutes in advance must a booking be made.'               => 'Ile minut wcześniej musi zostać dokonana rezerwacja.',
		'Max days in future'                                                => 'Maksymalna liczba dni w przód',
		'How many days in advance can a booking be made.'                   => 'Ile dni wcześniej można dokonać rezerwacji.',
		'Time format (Frontend)'                                            => 'Format czasu (Frontend)',
		'Controls how times are displayed on the booking form.'             => 'Określa sposób wyświetlania godzin w formularzu rezerwacji.',
		'Private calendar feed token'                                       => 'Token prywatnego kanału kalendarza',
		'Generate new token'                                                => 'Wygeneruj nowy token',
		'Feed URL:'                                                         => 'Adres URL kanału:',
		'Channel URL (ICS Feed):'                                           => 'Adres URL kanału (ICS Feed):',
		'You can add this URL to your calendar app as a read-only feed.'    => 'Możesz dodać ten adres URL do aplikacji kalendarza jako kanał tylko do odczytu.',
		'Save changes'                                                      => 'Zapisz zmiany',
		'Shortcode'                                                         => 'Shortcode',
		'Use this shortcode to display the booking form:'                   => 'Użyj tego shortcode, aby wyświetlić formularz rezerwacji:',
		'Services'                                                          => 'Usługi',
		'Edit services as JSON. Fields: name (string), duration (minutes), type (online|in_person).' => 'Edytuj usługi w formacie JSON. Pola: name (tekst), duration (minuty), type (online|in_person).',
		'Save services'                                                     => 'Zapisz usługi',

		// Working hours.
		'Working hours'                                                     => 'Godziny pracy',
		'Day'                                                               => 'Dzień',
		'Start (HH:MM)'                                                     => 'Start (GG:MM)',
		'End (HH:MM)'                                                       => 'Koniec (GG:MM)',
		'Day off'                                                           => 'Dzień wolny',
		'Closed'                                                            => 'Zamknięte',
		'Save working hours'                                                => 'Zapisz godziny pracy',

		// ICS feeds.
		'External ICS feeds (optional)'                                     => 'Zewnętrzne kanały ICS (opcjonalnie)',
		'Events from these calendars will block availability (read-only).'  => 'Wydarzenia z tych kalendarzy będą blokować dostępność (tylko do odczytu).',
		'Update frequency (minutes)'                                        => 'Częstotliwość aktualizacji (minuty)',
		'How often to fetch external calendars (min. 5 minutes).'           => 'Jak często pobierać zewnętrzne kalendarze (min. 5 minut).',
		'ICS Calendar 1 URL'                                                => 'Adres URL kalendarza ICS 1',
		'ICS Calendar 2 URL'                                                => 'Adres URL kalendarza ICS 2',
		'iCloud ICS URL'                                                    => 'Adres ICS iCloud',
		'Proton Calendar ICS URL'                                           => 'Adres ICS Proton Calendar',
		'Holidays ICS URL'                                                  => 'Adres ICS świąt',
		'Save ICS feeds'                                                    => 'Zapisz kanały ICS',
		'Test & Check'                                                      => 'Testuj i sprawdź',

		// Days of week.
		'Monday'                                                            => 'Poniedziałek',
		'Tuesday'                                                           => 'Wtorek',
		'Wednesday'                                                         => 'Środa',
		'Thursday'                                                          => 'Czwartek',
		'Friday'                                                            => 'Piątek',
		'Saturday'                                                          => 'Sobota',
		'Sunday'                                                            => 'Niedziela',

		// Admin JS localized.
		'Generating…'                                                       => 'Generowanie…',
		'New token generated.'                                              => 'Wygenerowano nowy token.',
		'Could not generate token.'                                         => 'Nie udało się wygenerować tokena.',

		// Frontend form.
		'No services available.'                                            => 'Brak dostępnych usług.',
		'Your booking has been confirmed.'                                  => 'Twoja rezerwacja została potwierdzona.',
		'That time slot is no longer available. Please choose another.'     => 'Ten termin nie jest już dostępny. Wybierz inny.',
		'Please enter a valid name and email address.'                      => 'Wpisz poprawne imię oraz adres e-mail.',
		'We could not complete your booking. Please try again.'             => 'Nie udało się zrealizować rezerwacji. Spróbuj ponownie.',
		'Select a service'                                                  => 'Wybierz usługę',
		'Choose…'                                                           => 'Wybierz…',
		'%d min'                                                            => '%d min',
		'Choose a date'                                                     => 'Wybierz datę',
		'YYYY-MM-DD'                                                        => 'RRRR-MM-DD',
		'Pick a date to see available times.'                               => 'Wybierz datę, aby zobaczyć dostępne godziny.',
		'Select a time slot'                                                => 'Wybierz termin',
		'Please choose a service and date.'                                 => 'Wybierz usługę oraz datę.',
		'Your name'                                                         => 'Twoje imię',
		'Your email'                                                        => 'Twój e-mail',
		'Confirm Booking'                                                   => 'Potwierdź rezerwację',
		'Today'                                                             => 'Dziś',
		'Tomorrow'                                                          => 'Jutro',
		'Day after'                                                         => 'Pojutrze',
		'Add message (optional)'                                            => 'Dodaj wiadomość (opcjonalnie)',

		// Frontend JS localized.
		'Please select a service.'                                          => 'Wybierz usługę.',
		'Please choose a date.'                                             => 'Wybierz datę.',
		'Loading available times…'                                          => 'Ładowanie dostępnych godzin…',
		'No available time slots for this date.'                            => 'Brak dostępnych terminów w tym dniu.',
		'Choose a time slot'                                                => 'Wybierz godzinę',
		'Date is too far in the future.'                                    => 'Wybrana data jest za daleko w przyszłości.',

		// Manage booking page.
		'Manage Booking'                                                    => 'Zarządzanie rezerwacją',
		'Your booking has been cancelled.'                                  => 'Twoja rezerwacja została anulowana.',
		'Your booking has been successfully rescheduled.'                   => 'Twoja rezerwacja została pomyślnie przełożona.',
		'Please select a date and time.'                                    => 'Wybierz datę i godzinę.',
		'The selected date is invalid or the time slot is no longer available.' => 'Wybrana data jest nieprawidłowa lub termin nie jest już dostępny.',
		'Invalid service selected.'                                         => 'Wybrano nieprawidłową usługę.',
		'An error occurred during booking update. Please try again.'        => 'Wystąpił błąd podczas aktualizacji rezerwacji. Spróbuj ponownie.',
		'Service:'                                                          => 'Usługa:',
		'Date:'                                                             => 'Data:',
		'Status:'                                                           => 'Status:',
		'Actions'                                                           => 'Akcje',
		'Reschedule Booking'                                                => 'Zmień termin',
		'Cancel Booking'                                                    => 'Anuluj rezerwację',
		'Are you sure you want to cancel this booking?'                     => 'Czy na pewno chcesz anulować tę rezerwację?',
		'Choose a new date and time'                                        => 'Wybierz nową datę i godzinę',
		'New Date'                                                          => 'Nowa data',
		'Confirm Reschedule'                                                => 'Potwierdź zmianę terminu',
		'Cancel'                                                            => 'Anuluj',
		'confirmed'                                                         => 'potwierdzona',
		'cancelled'                                                         => 'anulowana',

		// Admin bookings page.
		'Client'                                                            => 'Klient',
		'Date / Time'                                                       => 'Data / godzina',
		'Status'                                                            => 'Status',
		'No bookings found.'                                                => 'Nie znaleziono rezerwacji.',
		'Unknown Service'                                                   => 'Nieznana usługa',
		'Confirmed'                                                         => 'Potwierdzona',
		'Cancelled'                                                         => 'Anulowana',
		'Edit'                                                              => 'Edytuj',
		'Delete'                                                            => 'Usuń',
		'Are you sure?'                                                     => 'Czy na pewno?',
		'Edit Booking'                                                      => 'Edytuj rezerwację',
		'Client Name'                                                       => 'Imię klienta',
		'Client Email'                                                      => 'E-mail klienta',
		'Start Time'                                                        => 'Czas rozpoczęcia',
		'End Time'                                                          => 'Czas zakończenia',
		'Update Booking'                                                    => 'Zaktualizuj rezerwację',
		'Booking not found.'                                                => 'Nie znaleziono rezerwacji.',
		'Booking updated successfully. Email notification sent.'            => 'Rezerwacja została zaktualizowana. Powiadomienie e-mail wysłane.',
		'Booking deleted successfully. Email notification sent.'            => 'Rezerwacja została usunięta. Powiadomienie e-mail wysłane.',

		// Email templates page.
		'Customize email notifications sent to admin and clients. Use variables below to insert dynamic content.' => 'Dostosuj powiadomienia e-mail wysyłane do administratora i klientów. Użyj poniższych zmiennych, aby wstawić dynamiczną treść.',
		'Available variables:'                                              => 'Dostępne zmienne:',
		'Booking ID number'                                                 => 'Numer ID rezerwacji',
		'Name of the booked service'                                        => 'Nazwa zarezerwowanej usługi',
		'Client full name'                                                  => 'Pełne imię klienta',
		'Client email address'                                              => 'Adres e-mail klienta',
		'Date of the booking'                                               => 'Data rezerwacji',
		'Start time'                                                        => 'Czas rozpoczęcia',
		'End time'                                                          => 'Czas zakończenia',
		'Google Meet link (if set)'                                         => 'Link Google Meet (jeśli ustawiony)',
		'Website name'                                                      => 'Nazwa strony',
		'Click on a variable to copy it, then paste into the editor.'       => 'Kliknij zmienną, aby ją skopiować, a następnie wklej do edytora.',
		'Admin Notification (New Booking)'                                  => 'Powiadomienie administratora (Nowa rezerwacja)',
		'Client Notification (New Booking)'                                 => 'Powiadomienie klienta (Nowa rezerwacja)',
		'Client Notification (Booking Update)'                              => 'Powiadomienie klienta (Aktualizacja rezerwacji)',
		'Client Notification (Booking Cancellation)'                        => 'Powiadomienie klienta (Anulowanie rezerwacji)',
		'Subject'                                                           => 'Temat',
		'Body (HTML)'                                                       => 'Treść (HTML)',
		'Leave empty to use default template. HTML is supported.'           => 'Zostaw puste, aby użyć domyślnego szablonu. Obsługiwany jest HTML.',
		'Leave empty to use default template.'                              => 'Zostaw puste, aby użyć domyślnego szablonu.',
		'Save email templates'                                              => 'Zapisz szablony e-mail',
		'New booking confirmed (#{booking_id})'                             => 'Nowa rezerwacja potwierdzona (#{booking_id})',
		'Booking confirmed: {service_name}'                                 => 'Rezerwacja potwierdzona: {service_name}',
		'A new booking has been confirmed...'                               => 'Nowa rezerwacja została potwierdzona...',
		'Hello {client_name}, your booking is confirmed...'                 => 'Cześć {client_name}, Twoja rezerwacja została potwierdzona...',
		'Booking updated: {service_name}'                                   => 'Rezerwacja zaktualizowana: {service_name}',
		'Hello {client_name}, your booking details have been updated...'    => 'Cześć {client_name}, szczegóły Twojej rezerwacji zostały zaktualizowane...',
		'Booking cancelled: {service_name}'                                 => 'Rezerwacja anulowana: {service_name}',
		'Hello {client_name}, your booking has been cancelled...'           => 'Cześć {client_name}, Twoja rezerwacja została anulowana...',

		// Diagnostics page.
		'Check which events are being loaded from ICS sources and how they affect availability.' => 'Sprawdź, które wydarzenia są wczytywane ze źródeł ICS i jak wpływają na dostępność.',
		'Warning:'                                                          => 'Ostrzeżenie:',
		'WordPress timezone is set to UTC. This may cause issues with ICS calendars from different timezones.' => 'Strefa czasowa WordPress jest ustawiona na UTC. Może to powodować problemy z kalendarzami ICS z różnych stref czasowych.',
		'Change timezone in Settings → General'                             => 'Zmień strefę czasową w Ustawienia → Ogólne',
		'Date to check'                                                     => 'Data do sprawdzenia',
		'Run Diagnostics'                                                   => 'Uruchom diagnostykę',
		'Results'                                                           => 'Wyniki',

		// Emails - default templates.
		'New booking confirmed (#%d)'                                       => 'Nowa rezerwacja potwierdzona (#%d)',
		'Booking confirmed: %s'                                             => 'Rezerwacja potwierdzona: %s',
		'When: %1$s (%2$s - %3$s)'                                          => 'Kiedy: %1$s (%2$s - %3$s)',
		'Service: %s'                                                       => 'Usługa: %s',
		'Hello %s,'                                                         => 'Cześć %s,',
		'there'                                                             => '',
		'Your booking is confirmed.'                                        => 'Twoja rezerwacja została potwierdzona.',
		'Online meeting details:'                                           => 'Szczegóły spotkania online:',
		'Join link:'                                                        => 'Link do spotkania:',
		'Join link: (to be provided)'                                       => 'Link do spotkania: (zostanie podany)',
		'In-person meeting details:'                                        => 'Szczegóły spotkania stacjonarnego:',
		'Address: (to be provided)'                                         => 'Adres: (zostanie podany)',
		'You can manage your booking here:'                                 => 'Możesz zarządzać swoją rezerwacją tutaj:',
		'An ICS calendar invite is attached to this email.'                 => 'Zaproszenie do kalendarza w formacie ICS jest dołączone do tej wiadomości.',
		'A new booking has been confirmed (ID: %1$d).'                      => 'Nowa rezerwacja została potwierdzona (ID: %1$d).',
		'A new booking has been confirmed (ID: %1$s).'                      => 'Nowa rezerwacja została potwierdzona (ID: %1$s).',
		'Client: %s'                                                        => 'Klient: %s',
		'Email: %s'                                                         => 'E-mail: %s',
		'Message: %s'                                                       => 'Wiadomość: %s',
		'Manage Booking: %s'                                                => 'Zarządzaj rezerwacją: %s',
		'ICS file attached.'                                                => 'Załączono plik ICS.',
		'Join Meeting:'                                                     => 'Dołącz do spotkania:',
		'Booking updated: %s'                                               => 'Rezerwacja zaktualizowana: %s',
		'Hello %s, your booking details have been updated.'                 => 'Cześć %s, szczegóły Twojej rezerwacji zostały zaktualizowane.',
		'Booking cancelled: %s'                                             => 'Rezerwacja anulowana: %s',
		'Hello %s, your booking has been cancelled.'                        => 'Cześć %s, Twoja rezerwacja została anulowana.',
		'Service'                                                           => 'Usługa',
		'%1$s (Booking #%2$d)'                                              => '%1$s (Rezerwacja #%2$d)',

		// ICS feed summary.
		'Booking #%d'                                                       => 'Rezerwacja #%d',

		// AJAX messages.
		'Loading…'                                                          => 'Ładowanie…',
		'Empty URL'                                                         => 'Pusty adres URL',
		'No events found or error fetching feed.'                           => 'Nie znaleziono wydarzeń lub błąd pobierania kanału.',
		'Success! Found %d events.'                                         => 'Sukces! Znaleziono %d wydarzeń.',
		'Invalid date.'                                                     => 'Nieprawidłowa data.',
		'Invalid ID'                                                        => 'Nieprawidłowy identyfikator',

		// Manager page.
		'Invalid booking link.'                                             => 'Nieprawidłowe link rezerwacji.',

		// Admin Settings - Intervals.
		'Time slot interval'                                                => 'Interwał czasowy',
		'Meeting start times will be aligned to this interval (e.g., 15 min: 16:00, 16:15, 16:30, 16:45).' => 'Godziny rozpoczęcia spotkań będą wyrównane do tego interwału (np. 15 min: 16:00, 16:15, 16:30, 16:45).',

		// Admin Bookings - General.
		'Add New'                                                           => 'Dodaj nową',
		'Booking created successfully. Email notification sent.'            => 'Rezerwacja utworzona pomyślnie. Powiadomienie e-mail wysłane.',
		'Please fill in all required fields.'                               => 'Wypełnij wszystkie wymagane pola.',
		'Invalid date/time values. End time must be after start time.'      => 'Nieprawidłowe wartości daty/godziny. Data zakończenia musi być późniejsza niż rozpoczęcia.',
		'Database error occurred. Please try again.'                        => 'Wystąpił błąd bazy danych. Spróbuj ponownie.',

		// Admin Bookings - Create Form.
		'Add New Booking'                                                   => 'Dodaj nową rezerwację',
		'As an administrator, you can create bookings at any time, including outside of regular working hours.' => 'Jako administrator możesz tworzyć rezerwacje w dowolnym momencie, również poza godzinami pracy.',
		'Service Name'                                                      => 'Nazwa usługi',
		'Enter custom service name'                                         => 'Wpisz własną nazwę usługi',
		'Enter any service name. This will be the event title.'             => 'Wpisz dowolną nazwę usługi. Będzie to tytuł wydarzenia.',
		'Service Template'                                                  => 'Szablon usługi',
		'— Select a template (optional) —'                                  => '— Wybierz szablon (opcjonalne) —',
		'Optionally select a predefined service to auto-fill name and duration.' => 'Opcjonalnie wybierz zdefiniowaną usługę, aby automatycznie uzupełnić nazwę i czas trwania.',
		'Check Available Slots'                                             => 'Sprawdź dostępne terminy',
		'Available Slots'                                                   => 'Dostępne terminy',
		'Click "Check Available Slots" to see available times.'             => 'Kliknij "Sprawdź dostępne terminy", aby zobaczyć wolne godziny.',
		'These are suggestions based on availability. You can still enter any custom time below.' => 'To sugestie oparte na dostępności. Poniżej nadal możesz wpisać dowolną godzinę.',
		'You can enter any time, e.g., 17:15.'                              => 'Możesz wpisać dowolną godzinę, np. 17:15.',
		'Auto-calculate from service duration'                              => 'Oblicz automatycznie z czasu trwania usługi',
		'Send Email Notification'                                           => 'Wyślij powiadomienie e-mail',
		'Send confirmation emails to admin and client'                      => 'Wyślij e-maile potwierdzające do administratora i klienta',
		'Create Booking'                                                    => 'Utwórz rezerwację',
		'Message from the client (read-only).'                              => 'Wiadomość od klienta (tylko do odczytu).',

		// Admin Bookings - JS.
		'Please select a date first.'                                       => 'Najpierw wybierz datę.',
		'No available slots for this date. You can still enter custom times.' => 'Brak wolnych terminów w tym dniu. Nadal możesz wpisać własne godziny.',
		'Error loading slots. You can still enter custom times.'            => 'Błąd ładowania terminów. Nadal możesz wpisać własne godziny.',
		'Please enter a start time first.'                                  => 'Najpierw wpisz godzinę rozpoczęcia.',
		'string'                                                            => 'ciąg znaków',
		'integer'                                                           => 'liczba całkowita',
		'An error occurred. Please try again.'                              => 'Wystąpił błąd. Spróbuj ponownie.',
		'Please enter a valid email address.'                               => 'Wpisz poprawny adres e-mail.',

		// Security & Rate Limiting.
		'Security Settings'                                                 => 'Ustawienia zabezpieczeń',
		'Anti-Bot Protection'                                               => 'Ochrona Anty-Bot',
		'Enable Anti-Bot'                                                   => 'Włącz Anty-Bot',
		'Enable advanced anti-bot protection'                               => 'Włącz zaawansowaną ochronę przed botami',
		'Adds Honeypot, Nonce, and Time Trap fields to the booking form. Protects against spam bots without Captcha.' => 'Dodaje pola Honeypot, Nonce i Time Trap do formularza. Chroni przed botami bez użycia Captcha.',
		'Bot detected'                                                      => 'Wykryto bota',
		'Security check failed'                                             => 'Błąd weryfikacji bezpieczeństwa',
		'Bot detected (No JS)'                                              => 'Wykryto bota (brak JS)',
		'Bot detected (Invalid Token)'                                      => 'Wykryto bota (błędny token)',
		'Bot detected (Too Fast)'                                           => 'Wykryto bota (zbyt szybko)',
		'Configure rate limiting to protect against booking spam, brute force attacks, and abuse.' => 'Skonfiguruj limitowanie żądań, aby chronić przed spamem rezerwacyjnym, atakami brute force i nadużyciami.',
		'WAF/CDN Detected:'                                                 => 'Wykryto WAF/CDN:',
		'Rate Limiting'                                                     => 'Limitowanie żądań (Rate Limiting)',
		'Enable Rate Limiting'                                              => 'Włącz limitowanie żądań',
		'Enable rate limiting for public endpoints'                         => 'Włącz limitowanie dla publicznych punktów końcowych',
		'Protects the booking form, time slots AJAX, REST API, and booking management page from abuse.' => 'Chroni formularz rezerwacji, pobieranie terminów AJAX, REST API i stronę zarządzania rezerwacją przed nadużyciami.',
		'Max Requests'                                                      => 'Maksymalna liczba żądań',
		'requests per'                                                      => 'żądań na',
		'seconds'                                                           => 'sekund',
		'minutes'                                                           => 'minut',
		'Default: %1$d requests per %2$d seconds (1 minute). Applies per IP address per endpoint type.' => 'Domyślnie: %1$d żądań na %2$d sekund (1 minuta). Dotyczy adresu IP dla każdego typu punktu końcowego.',
		'IP Address Detection'                                              => 'Wykrywanie adresu IP',
		'Important:'                                                        => 'Ważne:',
		'If your site uses a CDN, WAF, or reverse proxy (e.g., Cloudflare, Sucuri, AWS CloudFront), the visitor\'s real IP may be in a different header. Incorrect configuration may cause rate limiting to apply to all users as if they shared the same IP.' => 'Jeśli Twoja witryna korzysta z CDN, WAF lub odwrotnego proxy (np. Cloudflare, Sucuri, AWS CloudFront), prawdziwy adres IP odwiedzającego może znajdować się w innym nagłówku. Nieprawidłowa konfiguracja może spowodować nałożenie limitów na wszystkich użytkowników, jakby korzystali z tego samego IP.',
		'IP Source'                                                         => 'Źródło IP',
		'Auto-detect (recommended)'                                         => 'Wykryj automatycznie (zalecane)',
		'Direct connection'                                                 => 'Połączenie bezpośrednie',
		'Generic proxy'                                                     => 'Ogólne proxy',
		'Choose how to determine the visitor\'s real IP address. "Auto-detect" will detect common WAF/CDN providers automatically.' => 'Wybierz, jak określać prawdziwy adres IP odwiedzającego. "Wykryj automatycznie" rozpozna popularnych dostawców WAF/CDN.',
		'Current Detection'                                                 => 'Obecnie wykryty',
		'(unknown)'                                                         => '(nieznany)',
		'This is your IP address as detected with the current settings. If this shows the CDN/proxy IP instead of your real IP, adjust the IP Source setting above.' => 'To Twój adres IP wykryty przy obecnych ustawieniach. Jeśli widzisz tu IP CDN/proxy zamiast swojego, dostosuj ustawienie "Źródło IP" powyżej.',
		'Protected Endpoints'                                               => 'Chronione punkty końcowe',
		'When rate limiting is enabled, the following endpoints are protected:' => 'Gdy limitowanie jest włączone, chronione są następujące punkty:',
		'Endpoint'                                                          => 'Punkt końcowy',
		'Description'                                                       => 'Opis',
		'Risk Without Protection'                                           => 'Ryzyko bez ochrony',
		'Booking form submission'                                           => 'Przesłanie formularza rezerwacji',
		'Booking spam, calendar flooding'                                   => 'Spam rezerwacyjny, zalewanie kalendarza',
		'Available time slots retrieval'                                    => 'Pobieranie dostępnych terminów',
		'DoS, excessive server load'                                        => 'DoS, nadmierne obciążenie serwera',
		'REST API - Get slots'                                              => 'REST API - Pobieranie terminów',
		'REST API - Create booking'                                         => 'REST API - Tworzenie rezerwacji',
		'Booking spam via API'                                              => 'Spam rezerwacyjny przez API',
		'Booking management page'                                           => 'Strona zarządzania rezerwacją',
		'Hash enumeration, booking hijacking'                               => 'Enumeracja hashów, przejmowanie rezerwacji',
		'Save Security Settings'                                            => 'Zapisz ustawienia zabezpieczeń',
		'Too many requests. Please try again later.'                        => 'Zbyt wiele żądań. Spróbuj ponownie później.',
		'Cloudflare detected. For accurate IP detection, consider installing the <a href="%s" target="_blank">Cloudflare plugin</a>.' => 'Wykryto Cloudflare. Dla dokładnego wykrywania IP rozważ instalację <a href="%s" target="_blank">wtyczki Cloudflare</a>.',
		'Sucuri detected. For accurate IP detection, consider installing the <a href="%s" target="_blank">Sucuri Security plugin</a>.' => 'Wykryto Sucuri. Dla dokładnego wykrywania IP rozważ instalację <a href="%s" target="_blank">wtyczki Sucuri Security</a>.',
		'AWS CloudFront detected. IP detection should work automatically with HTTP_CLOUDFRONT_VIEWER_ADDRESS header.' => 'Wykryto AWS CloudFront. Wykrywanie IP powinno działać automatycznie z nagłówkiem HTTP_CLOUDFRONT_VIEWER_ADDRESS.',
		'Fastly detected. IP detection should work automatically with HTTP_FASTLY_CLIENT_IP header.' => 'Wykryto Fastly. Wykrywanie IP powinno działać automatycznie z nagłówkiem HTTP_FASTLY_CLIENT_IP.',
		'A proxy or CDN is detected (X-Forwarded-For header present). If rate limiting is not working correctly, please configure the IP source manually below.' => 'Wykryto proxy lub CDN (obecny nagłówek X-Forwarded-For). Jeśli limitowanie nie działa poprawnie, skonfiguruj ręcznie źródło IP poniżej.',
		'Rate Limit Exceeded'                                               => 'Przekroczono limit żądań',

		// REST API & Tokens.
		'REST API'                                                          => 'REST API',
		'External applications (e.g., Apple Shortcuts, Zapier) can integrate with Oxtilo Fast Cal via REST API.' => 'Zewnętrzne aplikacje (np. Skróty Apple, Zapier) mogą integrować się z Oxtilo Fast Cal poprzez REST API.',
		'Authentication'                                                    => 'Uwierzytelnianie',
		'Security Note:'                                                    => 'Uwaga dotycząca bezpieczeństwa:',
		'The API token is separate from the Calendar Feed token. The Calendar Feed token (above) is read-only for ICS feeds. The API token below grants write access and should be kept secret.' => 'Token API jest oddzielny od tokena kanału kalendarza. Token kalendarza (powyżej) służy tylko do odczytu (ICS). Token API poniżej daje prawa zapisu i musi być chroniony.',
		'API Token (write access)'                                          => 'Token API (dostęp do zapisu)',
		'Generate new API token'                                            => 'Wygeneruj nowy token API',
		'Used for REST API authentication. Do not share this token publicly.' => 'Używany do uwierzytelniania w REST API. Nie udostępniaj go publicznie.',
		'All API requests require the API token in the HTTP header:'        => 'Wszystkie zapytania API wymagają tokena w nagłówku HTTP:',
		'GET /slots - Available Time Slots'                                 => 'GET /slots - Dostępne terminy',
		'Returns available booking slots for a given date.'                 => 'Zwraca dostępne terminy rezerwacji dla podanej daty.',
		'Parameter'                                                         => 'Parametr',
		'Type'                                                              => 'Typ',
		'Required'                                                          => 'Wymagane',
		'Yes'                                                               => 'Tak',
		'No'                                                                => 'Nie',
		'Date in YYYY-MM-DD format'                                         => 'Data w formacie RRRR-MM-DD',
		'Service index (default: 0)'                                        => 'Indeks usługi (domyślnie: 0)',
		'Custom duration in minutes (overrides service duration)'           => 'Własny czas trwania w minutach (nadpisuje czas usługi)',
		'Example request:'                                                  => 'Przykładowe żądanie:',
		'Example response:'                                                 => 'Przykładowa odpowiedź:',
		'POST /create - Create Booking'                                     => 'POST /create - Utwórz rezerwację',
		'Creates a new booking. Returns error 409 if the time slot conflicts with an existing booking.' => 'Tworzy nową rezerwację. Zwraca błąd 409, jeśli termin koliduje z inną rezerwacją.',
		'Client full name'                                                  => 'Pełne imię i nazwisko klienta',
		'Client email address'                                              => 'Adres e-mail klienta',
		'Time in HH:MM format (24h)'                                        => 'Czas w formacie GG:MM (24h)',
		'Duration in minutes (default: 60)'                                 => 'Czas trwania w minutach (domyślnie: 60)',
		'Service name (default: "Rezerwacja API")'                          => 'Nazwa usługi (domyślnie: "Rezerwacja API")',
		'Optional message from client'                                      => 'Opcjonalna wiadomość od klienta',
		'Example response (success):'                                       => 'Przykładowa odpowiedź (sukces):',
		'Example response (conflict):'                                      => 'Przykładowa odpowiedź (konflikt):',
		'Invalid or missing API token.'                                     => 'Nieprawidłowy lub brakujący token API.',
		'Invalid date or time format.'                                      => 'Nieprawidłowy format daty lub godziny.',
		'Failed to create booking.'                                         => 'Nie udało się utworzyć rezerwacji.',
		'Include "Manage Booking" link'                                     => 'Dołącz link "Zarządzaj rezerwacją"',
		'Add a link to edit/manage the booking in the calendar event description.' => 'Dodaj link do edycji/zarządzania rezerwacją w opisie wydarzenia w kalendarzu.',
		'Security Warning:'                                                 => 'Ostrzeżenie bezpieczeństwa:',
		'Enabling this option adds a direct link to manage the booking (with a secret token) to the calendar event. If your calendar feed URL is leaked, anyone with access to it can edit or cancel your bookings. Protect your feed URL carefully.' => 'Włączenie tej opcji dodaje bezpośredni link do zarządzania rezerwacją (z tajnym tokenem) do wydarzenia w kalendarzu. Jeśli adres URL Twojego kanału kalendarza wycieknie, każdy kto ma do niego dostęp, będzie mógł edytować lub anulować Twoje rezerwacje. Chroń starannie adres URL swojego kanału.',
	);

	/**
	 * Initialize fallback if needed.
	 */
	public static function init_if_needed(): void {
		$locale = function_exists( 'determine_locale' ) ? determine_locale() : get_locale();
		if ( 'pl_PL' !== $locale ) {
			return;
		}

		// If real MO is loaded, do not override.
		if ( self::is_domain_translated( 'oxtilo-fast-cal' ) ) {
			return;
		}

		add_filter( 'gettext', array( __CLASS__, 'filter_gettext' ), 10, 3 );
	}

	/**
	 * Check if a text domain has real translations loaded.
	 *
	 * @param string $domain Text domain.
	 * @return bool
	 */
	private static function is_domain_translated( string $domain ): bool {
		if ( ! function_exists( 'get_translations_for_domain' ) ) {
			return false;
		}

		$translations = get_translations_for_domain( $domain );
		if ( ! is_object( $translations ) ) {
			return false;
		}

		if ( property_exists( $translations, 'entries' ) && is_array( $translations->entries ) && ! empty( $translations->entries ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Filter gettext for Polish fallback.
	 *
	 * @param string $translated Translated text.
	 * @param string $text       Original text.
	 * @param string $domain     Text domain.
	 * @return string
	 */
	public static function filter_gettext( string $translated, string $text, string $domain ): string {
		if ( 'oxtilo-fast-cal' !== $domain ) {
			return $translated;
		}

		if ( isset( self::$pl[ $text ] ) ) {
			return self::$pl[ $text ];
		}

		return $translated;
	}
}
