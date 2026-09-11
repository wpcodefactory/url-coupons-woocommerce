# Pro / Free Code Separation Issues

- Plugin: URL Coupons for WooCommerce Pro
- Version: 1.8.3-dev
- Date: 2026-09-11 (second review)

## How the separation works

- The Pro code lives exclusively in `includes/pro/`. The free distribution is generated without that folder.
- Pro features are blocked in the free UI with:

```php
'custom_attributes' => apply_filters( 'alg_wc_url_coupons_settings', array( 'readonly' => 'readonly' ) ),
```

(or `array( 'disabled' => 'disabled' )`). The Pro class (`Alg_WC_URL_Coupons_Pro::settings()`) empties these attributes, and must also contain all of the feature implementation code.

## Already fixed (first review)

- **Redirect** (`alg_wc_url_coupons_redirect`, `..._redirect_custom_url`, `..._redirect_per_coupon`): `redirect()` moved to `includes/pro/`.
- **Remove "add to cart" key** (`alg_wc_url_coupons_remove_add_to_cart_key`): now a blocked pro field; implementation lives inside `redirect()` in Pro.
- **Javascript reload** (`alg_wc_url_coupons_javascript_reload`): now a blocked pro field; `reload_page_via_js()`, `do_not_apply_url_coupon_until_js_reload()`, `remove_reloaded_param_via_js_on_redirect()`, `get_reloaded_param_via_js()` moved to `includes/pro/`.
- Verified clean: "Add products to cart" and all 7 "Custom notices" fields — implementations entirely in `includes/pro/`.

## Open issues (second review)

### Issue 4 — "Force coupon redirect" — RESOLVED (kept as free feature)

- Verified: all of its code is in the free version — field in `includes/settings/class-alg-wc-url-coupons-settings-advanced.php` (line 73), hook and method `add_to_cart_action_force_coupon_redirect()` in `includes/class-alg-wc-url-coupons-core.php` (lines 68-69, 319-331). No references in `includes/pro/`.
- Decision: keep it as a free feature. No changes needed.

### Issue 5 — `[alg_wc_url_coupons_print_notices]` shortcode — FIXED (moved to free)

- The free "Notes" section (`includes/settings/class-alg-wc-url-coupons-settings-general.php`, line 306) advertises the `[alg_wc_url_coupons_print_notices]` shortcode.
- Resolution: `print_notices()` and its `add_shortcode()` registration were moved from `includes/pro/class-alg-wc-url-coupons-pro.php` to the free core `includes/class-alg-wc-url-coupons-core.php` (registered in the "Shortcodes" section, next to `alg_wc_url_coupons_translate`). The "why this is in Pro?" todo was removed.

### Issue 6 — readme.txt advertises "Javascript reload" as a free feature

- `readme.txt` line 58, under the free "Advanced Options" feature list: "Javascript reload: Reload the page via javascript when the coupon is detected from the URL".
- The option is now a blocked pro field in the free UI. Move this bullet to the "Do More: PRO Version" section (or remove it). Optionally add "Remove add to cart key" to the Pro section as well.

### Issue 7 — readme.txt FAQ recommends the pro "Javascript reload" option

- `readme.txt` line 138: "Enable **Advanced > Javascript reload** option."
- In the free version this field is now disabled. Update the FAQ (remove the bullet or note it is available in Pro only).

### Suggestion 8 — changelog entry for the pro/free split — DONE

- The pro/free moves are covered by the existing `* Fix - Code refactoring.` entry in `= 1.8.3 =` (no extra entry added).
- `readme.txt` changelog `= 1.8.4 =` includes: `* Dev - `[alg_wc_url_coupons_print_notices]` shortcode moved from the Pro version to the free version.`

## Scan coverage (verified clean)

- Blocked pro fields now: General (2 add-products + 3 redirect), Notices (7), Advanced (2: remove add-to-cart key, Javascript reload). All implementations are in `includes/pro/` (see "Already fixed").
- No `wp_safe_redirect()`/`wp_redirect()` calls in free files (`redirect()` is in Pro; only Pro's own `vendor/` has them).
- No `alg_wc_url_coupons_redirect*` / `alg_wc_url_coupons_notice*` option reads in free files (settings UI definitions only).
- Hook points kept in the free core (`alg_wc_url_coupons_before_coupon_applied`, `alg_wc_url_coupons_coupon_applied`, `alg_wc_url_coupons_after_coupon_applied`, `alg_wc_url_coupons_apply_url_coupon_validation`) are Pro extension points and fine to keep.
