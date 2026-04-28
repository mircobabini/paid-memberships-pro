# Using PMPro Abilities API (WordPress 6.9+)

PMPro now registers foundational Abilities API integrations for WordPress 6.9 and above.

## What is included

PMPro registers:

- Ability categories:
  - `pmpro-memberships`
  - `pmpro-reports`
- Read-only abilities:
  - `pmpro/list-levels`
  - `pmpro/list-reports`

Both abilities are exposed to the Abilities REST API (`wp-abilities/v1`) when available.

## Requirements

- WordPress **6.9+** (Abilities API is core in 6.9+).
- PMPro active.
- Authenticated user with proper capabilities:
  - `pmpro_membershiplevels` or `manage_options` for `pmpro/list-levels`
  - `pmpro_reports` or `manage_options` for `pmpro/list-reports`

## Endpoints

Assuming your site is `https://example.com`:

- List abilities:
  - `GET https://example.com/wp-json/wp-abilities/v1/abilities`
- Execute `pmpro/list-levels`:
  - `POST https://example.com/wp-json/wp-abilities/v1/abilities/pmpro%2Flist-levels/run`
- Execute `pmpro/list-reports`:
  - `POST https://example.com/wp-json/wp-abilities/v1/abilities/pmpro%2Flist-reports/run`

## cURL examples (Application Passwords)

Replace placeholders:

- `WP_USER` = your WordPress username
- `APP_PASSWORD` = your Application Password (with spaces removed or URL-escaped)

### 1) Discover abilities

```bash
curl -u 'WP_USER:APP_PASSWORD' \
  'https://example.com/wp-json/wp-abilities/v1/abilities'
```

### 2) Run PMPro levels ability

```bash
curl -u 'WP_USER:APP_PASSWORD' \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{}' \
  'https://example.com/wp-json/wp-abilities/v1/abilities/pmpro%2Flist-levels/run'
```

### 3) Run PMPro reports ability

```bash
curl -u 'WP_USER:APP_PASSWORD' \
  -X POST \
  -H 'Content-Type: application/json' \
  -d '{}' \
  'https://example.com/wp-json/wp-abilities/v1/abilities/pmpro%2Flist-reports/run'
```

## WP-CLI examples

If WP-CLI includes ability commands in your environment:

```bash
wp ability list
wp ability get pmpro/list-levels
wp ability run pmpro/list-levels
wp ability run pmpro/list-reports
```

## Turning PMPro ability registration on/off

Ability registration can be disabled with the `pmpro_enable_abilities_api` filter:

```php
add_filter( 'pmpro_enable_abilities_api', '__return_false' );
```

Use this in a must-use plugin or custom site plugin.
