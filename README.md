# WordPress Notice Management System

A portfolio project composed of two standalone WordPress plugins. A central WordPress installation creates dashboard notices and assigns them to registered client sites. Each client site retrieves its assigned notices through an authenticated REST API.

## What it demonstrates

- WordPress plugin lifecycle and custom database tables
- REST API route registration and API-key authentication
- Secure admin forms with capabilities, nonces, sanitization, and escaping
- Server-to-server communication with the WordPress HTTP API
- Per-user notice dismissal
- A small, server-side search for notices and clients
- Native `WP_List_Table` pagination
- PHPUnit integration tests and automated WPCS checks
- Accessible, native-looking WordPress admin UI

## Architecture

```text
Central WordPress site                     Client WordPress site
┌─────────────────────────┐               ┌─────────────────────────┐
│ Admin Notice Manager    │   REST API    │ Client Notice Receiver  │
│                         │◄──────────────│                         │
│ notices, clients, keys  │  API key      │ dashboard notices       │
│ assignments, dismissals │──────────────►│ per-user dismissal      │
└─────────────────────────┘               └─────────────────────────┘
```

The central plugin stores notices, clients, assignments, and dismissals in four custom tables. Every client receives a unique 64-character API key. The client plugin keeps that key on the server and does not expose it to browser-side JavaScript.

## Features

### Admin Notice Manager

- Create, edit, search, and delete notices
- Rich HTML or plain-text content
- Info, success, warning, and error styles
- Active/inactive state and optional expiration
- Assignment to one or more clients
- Client connection status and key management
- Individual API-key rotation
- Paginated WordPress-native notice and client tables
- REST endpoints protected by a client-specific API key

### Client Notice Receiver

- Configuration under **Settings → Notice Receiver**
- Server-side connection test
- Dashboard-only notice rendering
- Safe rendering with WordPress escaping helpers
- Asynchronous, per-user dismissal
- API credentials kept out of frontend JavaScript

## Requirements

- WordPress 6.2 or later
- PHP 7.4 or later
- HTTPS between the central and client sites for any real deployment

## Installation

1. Copy `admin-notice-manager` to the central site's `wp-content/plugins` directory and activate it.
2. Open **Notices → Clients**, add a client, and copy its generated API key.
3. Copy `client-notice-receiver` to the client site's `wp-content/plugins` directory and activate it.
4. Open **Settings → Notice Receiver** on the client site.
5. Enter the central API URL, for example `https://central.example/wp-json/anm/v1`, and the client's API key.
6. Save the settings and run the connection test.
7. Create a notice on the central site and assign it to that client.

## REST API

All routes require the following header:

```http
X-ANM-API-Key: CLIENT_API_KEY
```

### Fetch notices

```http
GET /wp-json/anm/v1/notices?user_id=42
```

```json
{
  "success": true,
  "notices": [
    {
      "id": 12,
      "title": "Scheduled maintenance",
      "content": "<p>Maintenance starts at 22:00.</p>",
      "content_type": "html",
      "notice_type": "warning",
      "is_dismissible": true,
      "created_at": "2026-09-14 18:30:00"
    }
  ],
  "count": 1
}
```

### Dismiss a notice

```http
POST /wp-json/anm/v1/dismiss/12
Content-Type: application/json

{"user_id":42}
```

The API verifies that the notice is assigned to the authenticated client and is dismissible before recording the dismissal.

### Test a connection

```http
GET /wp-json/anm/v1/ping
```

## Data model

| Table | Purpose |
| --- | --- |
| `wp_anm_notices` | Notice content, type, state, and expiration |
| `wp_anm_clients` | Client identity, status, API key, and last connection |
| `wp_anm_notice_clients` | Many-to-many notice assignments |
| `wp_anm_dismissals` | Per-client, per-user dismissed notices |

The actual table prefix follows the WordPress installation's configured prefix.

## Security notes

- Admin actions require the `manage_options` capability and valid nonces.
- Form values are unslashed, sanitized, and restricted to expected values.
- Output is escaped for its HTML context.
- Remote HTML notice content is filtered with `wp_kses_post()` on both ends.
- Client API keys are generated with cryptographically secure random bytes.
- The browser talks only to the local WordPress AJAX endpoint; the API key remains server-side.
- A client cannot dismiss notices assigned to another client.

API keys are stored in plain text because the client must present the original value to the central site. For a production-oriented evolution, the central plugin could store only keyed hashes and show a new credential once when it is generated.

Each client key can be rotated independently from its edit screen. Rotation immediately invalidates only that client's previous key; other clients continue working unchanged.

## Development and quality checks

Install the development dependencies:

```bash
composer install
```

Run WordPress Coding Standards and PHP compatibility checks:

```bash
composer lint
```

Run the database and REST API integration tests when the WordPress PHPUnit test suite and a test database are available:

```bash
WP_TESTS_DIR=/path/to/wordpress-develop/tests/phpunit \
WP_TESTS_CONFIG_FILE_PATH=/path/to/wordpress-develop/wp-tests-config.php \
composer test
```

The GitHub Actions workflow performs both jobs automatically on every push and pull request. It creates an isolated MySQL service and checks out the official WordPress test suite, so no live or manually installed WordPress site is required.

The tests cover:

- notice creation, assignment, search, and pagination queries;
- per-user dismissals and cleanup of related records;
- individual client API-key rotation;
- REST authentication and client isolation;
- successful dismissal and filtering of dismissed notices.

## Project scope

This repository is intentionally compact and built as a portfolio demonstration, not as a hosted service. Search is deliberately limited to a straightforward SQL `LIKE` match over the relevant title/content or site name/URL fields. It does not include fuzzy matching, transliteration, or custom search-code systems.

The list screens load 20 records per page. WordPress renders pagination controls only when the result set exceeds one page, keeping small demo datasets visually simple.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
