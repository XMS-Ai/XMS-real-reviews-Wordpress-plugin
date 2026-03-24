# Real Reviews Suite — Plugin Context for Claude

## Plugin Overview
WordPress plugin v4.0 by Xperience Marketing Solutions.
Displays reviews fetched from an external API in multiple widget formats.

## File Architecture
```
real-reviews-suite.php          → Entry point: asset registration + module loader
includes/
  api-handler.php               → API fetch, 12h transient cache, JSON-LD SEO output
  admin-ui.php                  → WP Admin menu, Settings page, Shortcodes page, Dashboard iframe
  shortcodes.php                → 3 shortcodes: Grid, Carousel, Form
assets/
  css/rr-style.css              → All frontend styles (grid, carousel, form, shared)
  js/rr-scripts.js              → initRRGrid(), initRRCarousel(), rrToggle()
  js/rr-form.js                 → Star rating, 2-step reveal, rrSubmit()
```

## External API Contracts
| Purpose          | Endpoint                                              | Method |
|------------------|-------------------------------------------------------|--------|
| Fetch reviews    | `https://api.realreviewsbyrp.com/6wlgncta5/{company_id}` | GET |
| Submit review    | `https://api.realreviewsbyrp.com/24867dekf/`          | POST   |
| Platform images  | `https://img.realreviewsbyrp.com/files/app-img/social-media/{platform}.jpg` | GET |
| Dashboard iframe | `https://client-area.realreviewsbyrp.com/`            | iframe |

## API Response Shape
```json
[
  {
    "guest_name": "John Smith",
    "comment": "Great service!",
    "comment_date": "2024-01-15",
    "product_evaluation": "5",
    "reviewSite": "Google",
    "comment_reply": ""
  }
]
```
Response may be raw JSON **or** `{"key": "<base64-encoded-json>"}` — handled by `real_reviews_fetch_and_decode()`.

## WordPress Options (wp_options)
| Option key                    | Default           | Purpose                    |
|-------------------------------|-------------------|----------------------------|
| `real_reviews_company_id`     | `Com0000DEMO`     | API identifier              |
| `real_reviews_accent_color`   | `#f39c12`         | Star/highlight color        |
| `real_reviews_section_title`  | `Customer Reviews`| Grid section heading        |

## Transient Cache
- Key: `rr_cache_{md5($api_url)}`
- TTL: 12 hours (`12 * HOUR_IN_SECONDS`)
- Cleared via: Settings page → "Clear Review Cache" button

## Shortcodes
| Shortcode                 | Output                                        |
|---------------------------|-----------------------------------------------|
| `[real_reviews]`          | Masonry grid with platform tabs + stats bar   |
| `[real_reviews_carousel]` | 3-up responsive carousel, shows latest 9      |
| `[real_reviews_form]`     | 2-step review submission form                 |

## CSS Design System (CSS custom properties)
```css
--rr-accent   → set per-widget via inline style (from accent_color option)
--rg: #8BC53F  → brand green
--rb: #00AEEF  → brand blue
--rd: #131720  → dark background
--rds: #1d2333 → dark secondary
```

## JavaScript Global Functions (window scope)
- `initRRGrid(containerId, reviews[])` → renders masonry grid
- `initRRCarousel(containerId, reviews[])` → renders carousel
- `rrToggle(btn, cardId)` → expand/collapse long comments
- `rrSubmit(companyId)` → submits form to API

## Admin UI Conventions
- All admin pages use `rrsuite-active` body class for full-width override
- CSS vars defined in `rrsuite_admin_inline_css()`
- Component: `rrsuite_panel_header($active_page)` renders shared header + tabs
- Flash notices use transient `rrsuite_flash` (30s TTL)
- Pages registered: `rrsuite-main`, `real-reviews-settings`, `real-reviews-shortcode`

## Security Patterns Already in Use
- `sanitize_key()` on `$_GET['page']`
- `sanitize_hex_color()` and `sanitize_text_field()` on options
- `current_user_can('manage_options')` before destructive actions
- `esc_attr()`, `esc_html()`, `esc_url()`, `esc_js()` in all HTML output
- `wp_json_encode()` for JS data injection
- ABSPATH guard on every file

## Naming Conventions
- PHP functions: `rrsuite_*` or `rr_*` or `real_reviews_*`
- CSS classes (admin): `.rr-*`
- CSS classes (frontend grid): `.rr-*`
- CSS classes (frontend carousel): `.rrc-*`
- CSS classes (frontend form): `.rr-form-*`
- JS functions: `rr*` (camelCase)
- WP option keys: `real_reviews_*`
- Transient keys: `rr_cache_*`, `rrsuite_*`

## Known Patterns & Gotchas
1. Assets are **registered** on `wp_enqueue_scripts` but only **enqueued** inside shortcode callbacks (`wp_enqueue_style/script` inside ob_start block).
2. The carousel only shows the **latest 9** reviews (`array_slice($all_reviews, 0, 9)`).
3. Grid `container_id` uses `uniqid()` — supports multiple instances on same page.
4. Form uses `btoa(unescape(encodeURIComponent(payload)))` encoding for POST body.
5. No nonce on the review submission form (handled server-side by external API).
6. `oninput` inline handler on color picker syncs hex display — not a security issue (admin-only).

## Growth Roadmap Signals
- Dashboard is an iframe to external client area (decoupled from plugin code)
- API base URLs suggest a SaaS backend owned by the same team
- Plugin prefix strategy (`rrsuite_`, `rr_`, `real_reviews_`) needs consistency as features grow
